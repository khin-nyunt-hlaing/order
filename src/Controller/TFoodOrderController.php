<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log; 
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Http\Exception\ForbiddenException;
use \Exception;

/**
 * 食材発注コントローラー   TFoodOrder Controller
 *
 * @property \App\Model\Table\TFoodOrderTable $TFoodOrder
 */
class TFoodOrderController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // 権限チェック（最初に置く）
            $perm = $this->decidePermission((string)$this->request->getParam('controller'));
                if ($perm < 0) {
                    throw new ForbiddenException('このアカウントでは利用できません。');
                }
                $this->set('usePermission', $perm);
                $this->set('permissionCode', $perm);

                // 旧 $level を使っているビューがまだある場合 → 互換用に設定
                $level = match ($perm) {
                    1 => 1,          // サービス1 = 管理
                    3, 4 => 2,    // サービス3、4 = 更新
                    5 => 0,          // サービス5 = 閲覧
                    default => -1,
                };
            $this->set('level', $level);

            //閲覧サービス要処理
                $userId = $this->request->getAttribute('identity')->get('user_id') ?? 'system';
                $MDispUserTable = $this->fetchTable('MDispUser');
                $specifiedNumbers = $MDispUserTable->find()
                                        ->select(['disp_user_id'])
                                        ->where(['user_id' => $userId])
                                        ->distinct(['disp_user_id'])
                                        ->enableHydration(false)
                                        ->all()
                                        ->extract('disp_user_id')
                                        ->toList();

            // ▼ SQL処理前調整
                $uid = $this->Authentication->getIdentity()->get('user_id');
                $q = (array)$this->request->getQueryParams();

                $scopeWhere = '';
                $scopeParams = [];
                //権限スコープ
                    if ($perm === 1) {
                        // ALL: 制限なし
                        $scopeWhere = '';
                    } elseif ($perm === 5) {
                        // VIEWER: 閲覧者（紐付け先のみ）
                        $ids = array_map('intval', $specifiedNumbers ?? []);
                        Log::debug('Converted IDs: ' . print_r($ids, true));
                        if (!$ids) {
                            $scopeWhere = ' AND 1=0'; // 閲覧不可
                        } else {
                            $phs = [];
                            foreach ($ids as $i => $val) {
                                $ph = ":id{$i}";
                                $phs[] = $ph;
                                $scopeParams["id{$i}"] = $val;
                            }
                            $scopeWhere = ' AND B.user_id IN (' . implode(',', $phs) . ')';
                        }
                    } else {
                        // SELF: 自分のみ
                        $scopeWhere = ' AND B.user_id = :uid';
                        $scopeParams['uid'] = $uid;
                    }

                // user_id（ALLのみ任意指定、SELF/VIEWERは無視してログ）
                    $userIdRaw = $q['user_id'] ?? null;
                    $userIdParam = null; 
                    if ($perm === 1) {
                        $userIdParam = ($userIdRaw !== null && $userIdRaw !== '') ? (string)$userIdRaw : null;
                    } elseif ($perm === 5) {
                            // 閲覧者
                            $allowed = array_values(array_unique(array_filter(array_map(
                                fn($x) => preg_match('/^[0-9A-Za-z_-]+$/', trim($x)) ? trim($x) : null,
                                $specifiedNumbers ?? []
                            ))));

                            if (!$allowed) {
                                $scopeWhere = ' AND 1=0';
                            } else {
                                // user_id がフォームから入力されており、かつ許可対象に含まれている場合だけ適用
                                if ($userIdRaw !== null && in_array($userIdRaw, $allowed, true)) {
                                    $userIdParam = (string)$userIdRaw; // ←★ ここで代入
                                }

                                // 通常の IN句制限
                                $phs = [];
                                foreach ($allowed as $i => $val) {
                                    $name = "id{$i}";
                                    $phs[] = ':' . $name;
                                    $scopeParams[$name] = $val;
                                    $scopeTypes[$name]  = 'string';
                                }
                                $scopeWhere = ' AND B.user_id IN (' . implode(',', $phs) . ')';
                            }
                        } else {
                        // SELF: 自分のみ
                        $scopeWhere = ' AND B.user_id = :uid';
                        $scopeParams['uid'] = $uid;
                        $scopeTypes['uid']  = 'integer'; // uid が数値なら明示
                    }

                // ステータス（空文字は未指定扱い）
                    $orderStatusParam = (isset($q['order_status']) && $q['order_status'] !== '')
                        ? (string)$q['order_status']
                        : null;

                // 日付（空は未指定扱いのNULLで渡す）
                    $odFrom = $q['order_date_from']    ?? null;
                    $odTo   = $q['order_date_to']      ?? null;
                    $drFrom = $q['deli_req_date_from'] ?? null;
                    $drTo   = $q['deli_req_date_to']   ?? null;
                    $ecFrom = $q['export_confirm_date_from'] ?? null;
                    $ecTo   = $q['export_confirm_date_to']   ?? null;

                    // ★ 上限日は当日終端に補正（datetime の想定。datetime2 なら .9999999）
                    $odToEnd = $odTo ? $odTo . ' 23:59:59.997' : null;
                    $drToEnd = $drTo ? $drTo . ' 23:59:59.997' : null;

                // 一覧SQL用のバインド配列
                    $sqlParams = [
                        'perm'         => $perm,
                        'uid'          => $uid,
                        'user_id'      => $userIdParam,
                        'order_status' => $orderStatusParam,
                        'od_from'      => $odFrom,
                        'od_to'        => $odTo,
                        'dr_from'      => $drFrom,
                        'dr_to'        => $drTo,
                        'ec_from'      => $ecFrom,
                        'ec_to'        => $ecTo,
                    ];

                // ▼ 一覧SQL（WHERE に $scopeWhere を差し込み、ORDER BY の前まで同じに）
                    $sql = "
                        SELECT 
                            B.food_order_id,
                            B.user_id,
                            CONVERT(VARCHAR(10), COALESCE(A.order_date,    B.order_date),    111) AS order_date,
                            CONVERT(VARCHAR(10), COALESCE(A.deli_req_date, B.deli_req_date), 111) AS deli_req_date,
                            CONVERT(
                                VARCHAR(10),
                                COALESCE(A.deli_shedule_date, B.deli_shedule_date),
                                111
                            ) AS deli_shedule_date,
                            
                            CONVERT(VARCHAR(10), A.deli_confirm_date, 111) AS deli_confirm_date,
                            CONVERT(
                                VARCHAR(10),
                                COALESCE(A.export_confirm_date, B.export_confirm_date),
                                111
                            ) AS export_confirm_date,
                            COALESCE(A.order_quantity,B.order_quantity)AS order_quantity,
                            COALESCE(A.order_status,  B.order_status)  AS order_status,
                            F.food_name,
                            F.food_specification,
                            U.user_name
                        FROM t_food_order AS B
                        LEFT JOIN t_food_order_fix AS A
                        ON A.food_order_id = B.food_order_id
                        AND A.del_flg = 0
                        INNER JOIN m_food  AS F ON F.food_id = COALESCE(A.food_id, B.food_id)
                        INNER JOIN m_user  AS U ON U.user_id = B.user_id
                        WHERE 1=1
                        AND B.del_flg = 0
                        " . $scopeWhere . "
                        AND COALESCE(A.user_id, B.user_id)
                            = COALESCE(:user_id, COALESCE(A.user_id, B.user_id))
                        AND COALESCE(A.order_status, B.order_status)
                            = COALESCE(:order_status, COALESCE(A.order_status, B.order_status))
                        AND COALESCE(A.order_date,    B.order_date)
                            >= COALESCE(:od_from, COALESCE(A.order_date,    B.order_date))
                        AND COALESCE(A.order_date,    B.order_date)
                            <= COALESCE(:od_to,   COALESCE(A.order_date,    B.order_date))
                        AND COALESCE(A.deli_req_date, B.deli_req_date)
                            >= COALESCE(:dr_from, COALESCE(A.deli_req_date, B.deli_req_date))
                        AND COALESCE(A.deli_req_date, B.deli_req_date)
                            <= COALESCE(:dr_to,   COALESCE(A.deli_req_date, B.deli_req_date))

                        
                    ";
                    // ▼ ★ 管理者だけ export_confirm_date 条件を追加（SQLの外で追加）
                    if ((int)$level === 1) {

                        if (!empty($ecFrom)) {
                            $sql .= " AND COALESCE(A.export_confirm_date, B.export_confirm_date) >= :ec_from ";
                            $sqlParams['ec_from'] = $ecFrom;
                        }

                        if (!empty($ecTo)) {
                            $sql .= " AND COALESCE(A.export_confirm_date, B.export_confirm_date) <= :ec_to ";
                            $sqlParams['ec_to'] = $ecTo;
                        }

                    } else {
                        // 管理者以外はパラメータを削除
                        unset($sqlParams['ec_from'], $sqlParams['ec_to']);
                    }
                    $sql .= " ORDER BY COALESCE(A.order_date, B.order_date) DESC";
                    
                    // ▼ 件数SQL（TOP/ORDER BYなしで同じWHEREをコピペ）
                    $countSql = "
                        SELECT COUNT(1) AS cnt
                        FROM t_food_order AS B
                        LEFT JOIN t_food_order_fix AS A
                        ON A.food_order_id = B.food_order_id
                        AND A.del_flg = 0
                        INNER JOIN m_food  AS F ON F.food_id = COALESCE(A.food_id, B.food_id)
                        INNER JOIN m_user  AS U ON U.user_id = B.user_id
                        WHERE 1=1
                        AND B.del_flg = 0
                        " . $scopeWhere . "
                        AND COALESCE(A.user_id, B.user_id)
                            = COALESCE(:user_id, COALESCE(A.user_id, B.user_id))
                        AND COALESCE(A.order_status, B.order_status)
                            = COALESCE(:order_status, COALESCE(A.order_status, B.order_status))
                        AND COALESCE(A.order_date,    B.order_date)
                            >= COALESCE(:od_from, COALESCE(A.order_date,    B.order_date))
                        AND COALESCE(A.order_date,    B.order_date)
                            <= COALESCE(:od_to,   COALESCE(A.order_date,    B.order_date))
                        AND COALESCE(A.deli_req_date, B.deli_req_date)
                            >= COALESCE(:dr_from, COALESCE(A.deli_req_date, B.deli_req_date))
                        AND COALESCE(A.deli_req_date, B.deli_req_date)
                            <= COALESCE(:dr_to,   COALESCE(A.deli_req_date, B.deli_req_date))
                    
                    ";
                    // ▼ countSql の export_confirm_date 条件（管理者だけ）
                    if ((int)$level === 1) {

                        if (!empty($ecFrom)) {
                            $countSql .= " AND COALESCE(A.export_confirm_date, B.export_confirm_date) >= :ec_from ";
                            $sqlParams['ec_from'] = $ecFrom;
                        }

                        if (!empty($ecTo)) {
                            $countSql .= " AND COALESCE(A.export_confirm_date, B.export_confirm_date) <= :ec_to ";
                            $sqlParams['ec_to'] = $ecTo;
                        }

                    } else {
                        unset($sqlParams['ec_from'], $sqlParams['ec_to']);
                    }

                    $connection = $this->fetchTable('TFoodOrder')->getConnection();
                    $userIdRaw = $q['user_id'] ?? null;
                    $userIdParam = ($perm === 1 && $userIdRaw !== '' && $userIdRaw !== null) ? (string)$userIdRaw : null;

                    // ▼ 実際にSQL内で使うプレースホルダだけを渡す（初期化）
                    $bind = array_merge($scopeParams, [
                        'user_id'      => $userIdParam, 
                        'order_status' => $orderStatusParam,
                        'od_from'      => $odFrom,
                        'od_to'        => $odTo,
                        'dr_from'      => $drFrom,
                        'dr_to'        => $drTo,
                        'ec_from'      => $ecFrom,
                        'ec_to'        => $ecTo,
                    ]);

                    // 1) SQL中のプレースホルダだけを拾って bind を作る関数
                    $makeBind = function (string $sql, array $scopeParams, array $sqlParams): array {
                        preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $m);
                        $need = array_unique($m[1]);  // SQLに現れる :名前 の一覧

                        // 値の供給元は scopeParams（権限）と sqlParams（検索値）
                        $merged = $scopeParams + $sqlParams;

                        // 必要なキーだけ詰める（余分は渡さない）
                        $bind = [];
                        foreach ($need as $k) {
                            if (array_key_exists($k, $merged)) {
                                $bind[$k] = $merged[$k];
                            } else {
                                Log::error("[BIND_MISSING] :$k が未設定");
                            }
                        }

                        // デバッグ（必要なら）
                        // Log::debug('[PH-LIST] ' . implode(', ', $need));
                        // Log::debug('[BIND-KEYS] ' . implode(', ', array_keys($bind)));
                        // Log::debug('[BIND-VALS] ' . json_encode($bind, JSON_UNESCAPED_UNICODE));
                        return $bind;
                    };

                    // Log::debug('[SQL_RAW] ' . $sql);
                    // Log::debug('[SQL_BIND_KEYS-LIST] ' . implode(', ', array_keys($bind)));

                    // // ★ execute() の直前に1回だけ
                    // Log::debug('[SCOPE_WHERE] ' . $scopeWhere);

                    // SQL内の :param を抽出して比較
                    preg_match_all('/:\w+/', $sql, $m);
                    $need = array_unique(array_map(fn($s) => ltrim($s, ':'), $m[0]));
                    $have = array_keys($bind);

                    $missing = array_values(array_diff($need, $have));
                    $extra   = array_values(array_diff($have, $need));

                    // Log::debug('[PARAM_CHECK] need=' . implode(',', $need) .
                    //         ' / have=' . implode(',', $have) .
                    //         ' / missing=' . implode(',', $missing) .
                    //         ' / extra=' . implode(',', $extra));
            // ★★★★★★★★★★★★★★★★
                // ★ 一覧用：SQLに出る :param だけ残す
                preg_match_all('/:\w+/', $sql, $m1);
                $need1 = array_unique(array_map(fn($s) => substr($s, 1), $m1[0]));
                $bindList  = $makeBind($sql,       $scopeParams, $sqlParams);

                // （任意）不足チェックを残すと安心
                $missing1 = array_diff($need1, array_keys($bindList));
                if ($missing1) {
                    Log::error('[PARAM_MISSING_LIST] ' . implode(',', $missing1));
                    // 必要なら例外・早期return
                }

                // ★ 件数用：同様にフィルタ
                preg_match_all('/:\w+/', $countSql, $m2);
                $need2 = array_unique(array_map(fn($s) => substr($s, 1), $m2[0]));
                $bindCount = $makeBind($countSql,  $scopeParams, $sqlParams);

                $missing2 = array_diff($need2, array_keys($bindCount));
                if ($missing2) {
                    Log::error('[PARAM_MISSING_COUNT] ' . implode(',', $missing2));
                    // 必要なら例外・早期return
                }
                // （任意）ログの取り違いを修正
                // Log::debug('[LIST_BIND_KEYS] '  . implode(', ', array_keys($bindList)));
                // Log::debug('[COUNT_BIND_KEYS] ' . implode(', ', array_keys($bindCount)));

                // ★ 実行（フィルタ済みパラメータで実行）
                $listStmt  = $connection->execute($sql,$bindList);
                $rows      = $listStmt->fetchAll('assoc');

                $countStmt = $connection->execute($countSql, $bindCount);
                $countRow  = $countStmt->fetch('assoc');
                $count     = (int)($countRow['cnt'] ?? 0);

                // ▼ ビューへ
                $tFoodOrder = $rows;

            //一覧処理終わり
                $mUserTable = $this->fetchTable('MUser');
                    $users = $mUserTable->find('list', keyField: 'user_id', valueField: 'user_name')
                    ->where([
                        'del_flg' => 0,
                        'use_service_id IN' => [3, 4]
                    ])->toArray();

            // 2) Bから、その番号に一致する「番号 => 名前」を取得（重複なし）
                $codeToName = [];
                if (!empty($specifiedNumbers)) {
                    $codeToName = $mUserTable->find('list', 
                            keyField   : 'user_id',
                            valueField : 'user_name',
                        )
                        ->where([
                                    'del_flg' => 0,
                                    'use_service_id IN' => [3, 4],
                                    'user_id IN' => $specifiedNumbers])
                        ->toArray();
                }
                // 7) 初期値（状態=すべて）
                if (!isset($q['order_status'])) $q['order_status'] = '';

                // 8) ビューへ
                $this->set('filters', $q);
                $this->set(compact('tFoodOrder','count','level'));
                // ビューで使っている $isConfirmed も必ずセット（暫定は false）
                $this->set('isConfirmed', false);
                            

        if ($this->request->is('post')) {
                $action = $this->request->getData('action'); 
                $selected = $this->request->getData('select') ?? [];
                $selectedIds = array_keys(array_filter($selected));
                $selectcount = count($selectedIds);
                // Log::debug('🔁 POST開始');
                // Log::debug('🧪 POST全体: ' . print_r($this->request->getData(), true));

                // ★ 権限制御（POSTアクション × レベル）
                $deny = match ($action) {
                    // 管理者専用：削除・確定・確定解除・書出し
                    'confirm', 'unconfirm', 'export' => ($level !== 1),

                    // 更新者まで許可：add / edit（閲覧は不可）
                    'add', 'edit' ,'delete'=> ($level === 0),

                    // その他は既定で許可
                    default => false,
                };

                if ($deny) {
                    $this->Flash->error('権限がありません。');
                    return $this->redirect(['action' => 'index']);
                }

                // ✅ ここを修正：チェックが必要な操作のみ制限
                if (in_array($action, ['edit', 'confirm', 'unconfirm', 'delete']) && empty($selectedIds)) {
                    $this->Flash->error('食材発注が選択されていません。');
                    return $this->redirect(['action' => 'index']);
                }
                // 追加処理
                if ($action === 'add') {
                    return $this->redirect(['action' => 'add']);
                }

                // 更新処理
                if ($action === 'edit') {
                    if ($selectcount === 1) {
                        $id = (int)$selectedIds[0];

                        // ★ もう order_status 判定も、editmaster 遷移も不要
                        // 1件だけ選択されていれば通常の edit に飛ばす
                        return $this->redirect(['action' => 'edit', $id]);

                    } elseif ($selectcount === 0) {
                        $this->Flash->error('更新する項目を1つ選択してください。');

                    } else {
                        $this->Flash->error('更新は1件のみ選択可能です。');
                    }
                }

                // 確定処理
                // 確定・確定解除処理（混在チェック）
                if ($action === 'confirm' || $action === 'unconfirm') {

                    if (empty($selectedIds)) {
                        $this->Flash->error('食材発注が選択されていません。');
                        return $this->redirect(['action' => 'index']);
                    }

                    $targetStatus = ($action === 'confirm') ? 0 : 1;

                    $orders = $this->TFoodOrder->find()
                        ->where(['food_order_id IN' => $selectedIds])
                        ->all();

                    // 混在チェック
                    $invalid = [];
                    foreach ($orders as $order) {
                        if ((int)$order->order_status !== $targetStatus) {
                            $invalid[] = $order->food_order_id;
                        }
                    }

                    if (!empty($invalid)) {
                        $this->set('confirmError', $action);
                        $this->set('selectedIds', $selectedIds);
                        $this->set(compact('tFoodOrder', 'count', 'users'));
                        return;
                    }

                    // 正常処理
                    $loginUserId = $this->request->getAttribute('identity')->get('user_id') ?? 'system';
                    $now = FrozenTime::now();  // ←★ ここで FrozenTime を統一
                    $status = ($action === 'confirm') ? 1 : 0;
                    $TFoodOrderFixTable = $this->fetchTable('TFoodOrderFix');

                    $conn = $this->TFoodOrder->getConnection();

                    $conn->transactional(function () use ($orders, $action, $loginUserId, $now, $status, $TFoodOrderFixTable) {

                        foreach ($orders as $order) {

                            // --- T_FOOD_ORDER 更新 ---
                            $order->order_status = $status;
                            $order->update_user  = $loginUserId;
                            $order->update_date  = $now;

                            // 確定日
                            $order->deli_confirm_date = ($action === 'confirm') ? $now : null;

                            if (!$this->TFoodOrder->save($order)) {
                                throw new \RuntimeException('TFoodOrder 更新失敗: ' . $order->food_order_id);
                            }

                            // --- T_FOOD_ORDER_FIX 更新 or 作成 ---
                            $linked = $TFoodOrderFixTable->find()
                                ->where(['food_order_id' => $order->food_order_id])
                                ->first();

                            if ($linked) {
                                // 修正(Update)
                                $patch = [
                                    'order_status'      => $status,
                                    'deli_confirm_date' => ($action === 'confirm') ? $now : null,
                                    'update_user'       => $loginUserId,
                                    'update_date'       => $now,
                                ];

                                $TFoodOrderFixTable->patchEntity($linked, $patch);
                                $TFoodOrderFixTable->saveOrFail($linked);

                            } else {
                                // 新規 Insert
                                $insertData = [
                                    'food_order_id'      => $order->food_order_id,
                                    'user_id'            => $order->user_id,
                                    'order_date'         => $order->order_date,
                                    'deli_req_date'      => $order->deli_req_date,
                                    'deli_shedule_date'  => $order->deli_shedule_date,
                                    'deli_confirm_date'  => ($action === 'confirm') ? $now : null,
                                    'export_confirm_date'=> $order->export_confirm_date,
                                    'food_id'            => $order->food_id,
                                    'order_quantity'     => $order->order_quantity,
                                    'order_status'       => $status,
                                    'del_flg'            => 0,
                                    'create_user'        => $loginUserId,
                                    'create_date'        => $now,
                                    'update_user'        => $loginUserId,
                                    'update_date'        => $now,
                                ];

                                $linked = $TFoodOrderFixTable->newEmptyEntity();
                                $linked = $TFoodOrderFixTable->patchEntity($linked, $insertData);
                                $TFoodOrderFixTable->saveOrFail($linked);
                            }
                        }
                    });

                    // ★★★★★ ここに書く！！（トランザクションの外）★★★★★
                if ($action === 'confirm') {
                    // $csvPath = $this->exportConfirmedOrders($selectedIds);
                    $this->Flash->success("確定しました。");
                } else {
                    $this->Flash->success("確定を解除しました。");
                }
              

                return $this->redirect(['action' => 'index']);
                }
                if ($action === 'search') {
                    $this->request->allowMethod(['post']);

                    $d = (array)$this->request->getData();
                    unset($d['select']); 
                    // 入力正規化（空を落とす / user_name→user_id）
                        $carry = [
                            'user_id'            => $d['user_name']          ?? null,
                            'order_status'       => $d['order_status']       ?? null,
                            'order_date_from'    => $d['order_date_from']    ?? null,
                            'order_date_to'      => $d['order_date_to']      ?? null,
                            'deli_req_date_from' => $d['deli_req_date_from'] ?? null,
                            'deli_req_date_to'   => $d['deli_req_date_to']   ?? null,
                            'export_confirm_date_from' => $d['export_confirm_date_from'] ?? null,
                            'export_confirm_date_to'   => $d['export_confirm_date_to']   ?? null,
                        ];
                        $carry = array_filter($carry, fn($v) => $v !== '' && $v !== null);

                        Log::debug('[TFood search ▶ carry] ' . json_encode($carry, JSON_UNESCAPED_UNICODE));

                        return $this->redirect(['action' => 'index', '?' => $carry], 303);
                    }
                // 書出し処理
                if ($action === 'export') {
                $this->request->allowMethod(['post']);

                $d = (array)$this->request->getData();
                unset($d['select']); 
                // 入力正規化（空を落とす / user_name→user_id）
                    $carry = [
                        'user_id'            => $d['user_name']          ?? null,
                        'order_status'       => $d['order_status']       ?? null,
                        'order_date_from'    => $d['order_date_from']    ?? null,
                        'order_date_to'      => $d['order_date_to']      ?? null,
                        'deli_req_date_from' => $d['deli_req_date_from'] ?? null,
                        'deli_req_date_to'   => $d['deli_req_date_to']   ?? null,
                    ];
                    $carry = array_filter($carry, fn($v) => $v !== '' && $v !== null);

                    Log::debug('[TFood search ▶ carry] ' . json_encode($carry, JSON_UNESCAPED_UNICODE));

                    return $this->redirect(['action' => 'export', '?' => $carry], 303);
                
                }
                // 削除処理（論理削除）
                if ($action === 'delete') {
                    Log::debug('[🔁 delete処理] POSTアクション開始');

                    // ① チェックされたIDの抽出（値=1のみを採用）
                    $rawSelect = (array)$this->request->getData('select', []);
                    $selectedIds = array_keys(array_filter($rawSelect, function ($v) {
                        return $v === 1 || $v === '1' || $v === true || $v === 'on';
                    }));

                    if (empty($selectedIds)) {
                        Log::warning('[⚠️ 削除処理] チェックされたIDがありません');
                        $this->Flash->error('削除するデータを選択してください。');
                        return $this->redirect(['action' => 'index']);
                    }

                    // 確定状態のIDを事前にチェック
                    $confirmedIds = $this->TFoodOrder->find()
                        ->select(['food_order_id'])
                        ->where([
                            'food_order_id IN' => $selectedIds,
                            'order_status' => '1'
                        ])
                        ->all()
                        ->extract('food_order_id')
                        ->toList();

                    if (!empty($confirmedIds)) {
                        Log::warning('[確定データあり] 削除処理を中止します: ' . json_encode($confirmedIds));
                        $this->Flash->error('確定している為、削除できません。');
                        return $this->redirect(['action' => 'index']);
                    }

                    // ② 更新者と現在時刻
                    $userId = $this->request->getAttribute('identity')->get('user_id') ?? 'system';
                    $now = FrozenTime::now();

                    // 別テーブルに同じIDがあるか確認
                    $TFOFTable = $this->fetchTable('TFoodOrderFix');
                    // 対象が存在する場合だけ del_flg=1 に更新
                    $FixIds = (array)$selectedIds;
                    $affectedFix = $TFOFTable->updateAll(
                        ['del_flg' => 1],
                        ['food_order_id IN' => $FixIds, 'del_flg' => 0]
                    );

                    if ($affectedFix > 0) {
                        Log::debug("TFoodOrderFix の {$affectedFix} 件を del_flg=1 に更新しました。");
                    }

                    // ③ 一括更新（未削除のものだけ対象）
                    $affected = $this->TFoodOrder->updateAll(
                        [
                            'del_flg'     => 1,
                            'update_user' => $userId,
                            'update_date' => $now,
                        ],
                        [
                            'food_order_id IN' => $selectedIds,
                            'del_flg'          => 0,
                        ]
                    );

                    Log::debug("[✅ delete処理] updateAll affected={$affected}, ids=" . json_encode($selectedIds));

                    if ($affected > 0) {
                        $this->Flash->success("選択されたデータ（{$affected}件）を削除しました。");
                    } else {
                        // すでに削除済み等で0件の可能性もある
                        $this->Flash->warning('対象データは既に削除されているか、見つかりませんでした。');
                    }

                    return $this->redirect(['action' => 'index']);
                }

            }
        $this->set(compact('tFoodOrder', 'count', 'users','codeToName'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $userId = null;
        $userName = null;

        $identity = $this->Authentication->getIdentity();

        if ($identity) {
            $userId = $identity->get('user_id');
            $userName = $identity->get('user_name');

            Log::debug("[セッション] user_id => {$userId}");
            Log::debug("[セッション] user_name => {$userName}");
        }

        //データセット☆
        $TFoodOrder = $this->TFoodOrder->newEmptyEntity(); // ← 必須
        // データセット☆（POST前）
        //$TFoodOrder->order_status = 0; // ← ここで明示的に型付きで初期化（POSTの前に！）

        // Bテーブル（例：MFoods）から全件取得し、名前 / 規格で連結
        // 🔄 category_id 付きで取得して分類
            $mFoods = $this->fetchTable('MFoods')->find()
                ->select(['food_id', 'food_name', 'food_specification', 'category_id'])
                ->where(['del_flg' => '0'])
                ->order(['disp_no' => 'ASC'])
                ->all();

            $categoryOptions = $this->fetchTable('MFoodCategories')->find('list', keyField: 'category_id', valueField: 'category_name')
                    ->where(['del_flg' => '0'])
                    ->order(['disp_no' => 'ASC'])
                    ->toArray();

            $groupedFoods = [];
            foreach ($mFoods as $food) {
                $groupedFoods[$food->category_id][] = [
                    'id' => $food->food_id,
                    'label' => $food->food_name . ' / ' . $food->food_specification
                ];
            }

            $base = FrozenTime::now();
            // $days = 7;
            $hour = (int)$base->format('H');
            $isAfter17 = ($hour >= 17);
            // ログ出力（状況を明示）
            $status = $isAfter17 ? '🕖 17時を過ぎています' : '🕔 まだ17時前です';
            Log::debug($status);
            $extraDay = ($isAfter17 >= 17) ? 1 : 0;

            // M_USER.read_time の取得（ログインユーザー）
            $readTime = (int)($this->fetchTable('MUser')
                ->find()
                ->select(['read_time'])
                ->where(['user_id' => $userId])
                ->first()?->read_time ?? 0);
            $target = $base->addDays($readTime);
            Log::debug("⏰ {$readTime}日後: " . $target->format('Y-m-d H:i:s'));

            //リードタイム⁺17時過ぎの場合
            $addDays = $readTime + $extraDay;

            // minDate を日付文字列で生成
            $minDate = (clone $base)->modify("+{$addDays} days")->format('Y-m-d');
            // 今日の日付を初期値に設定（例：order_date カラム）
            $TFoodOrder->order_date = FrozenDate::today();

            $categoryId = null;
            $this->set(compact('TFoodOrder', 'userName', 'groupedFoods','categoryOptions','addDays','minDate','categoryId'));
            $this->set('mode', 'add');
            $identity = $this->Authentication->getIdentity();
            $useSvc = (int)$identity->get('use_service_id');
            $this->set('useSvc', $useSvc);
            $this->render('add_edit');
        try{
            //データセット★
            if ($this->request->is('post')) {
                Log::debug('[REQ] method=' . $this->request->getMethod() .
            ' isPost=' . ($this->request->is('post') ? 'yes' : 'no'));
                $data = $this->request->getData();
                Log::debug('📦 POSTデータ: ' . print_r($data, true));
                $deliReqDate = $this->request->getData('deli_req_date') ?: null; 
                $categoryId = $data['category_id'] ?? null;
                $rawFoodId  = $this->request->getData('food_id');
                $selectedFoodId = (is_string($rawFoodId) && $rawFoodId !== '') ? $rawFoodId : null;
                    
                    // ▼ 入力日（申込日＝今日）とリードタイム
                    // ここは “today” を使う。希望日ではない点が重要。
                    $orderDate = $TFoodOrder->order_date; // FrozenDate::today() が入っている前提
                    $identity  = $this->Authentication->getIdentity();
                    $userId    = $identity->get('user_id');
                    $readTime  = (int)($this->fetchTable('MUser')->find()
                                    ->select(['read_time'])
                                    ->where(['user_id' => $userId])
                                    ->first()?->read_time ?? 0);

                    // ▼ ① 入力された希望日（文字列→日付）
                    $userDateRaw = $data['deli_req_date'] ?? null;
                    if ($userDateRaw === null || $userDateRaw === '') {
                        $this->Flash->error('納品希望日が未入力です。');
                        return $this->render('add_edit');
                    }
                    $userDate = new FrozenDate(str_replace('/', '-', $userDateRaw));

                    // ▼ 最短“基準”日（= 申込日 + リードタイム） ← ★ここを「orderDate」基準に！
                    $minBaseDate = $orderDate->addDays($readTime);

                    // ▼ 希望日が最短日と同日なら、17:00以降は受け付けない
                    $cutoff  = FrozenTime::now()->setTime(17, 0, 0);
                    $now     = FrozenTime::now();
                    $sameDay = ($userDate->format('Y-m-d') === $minBaseDate->format('Y-m-d'));
                    $after17 = ($now >= $cutoff);

                    Log::debug(sprintf(
                        '[17時超過ブロック] 最短=%s / 希望=%s / 現在=%s / 判定:同日=%s, after17=%s',
                        $minBaseDate->format('Y-m-d'),
                        $userDate->format('Y-m-d'),
                        $now->format('Y-m-d H:i:s'),
                        $sameDay ? 'YES' : 'NO',
                        $after17 ? 'YES' : 'NO'
                    ));

                    // ▼ ⑤ ブロック（17:00 ちょうど含む）
                    if ($sameDay && $after17) {
                        $displayDate = $minBaseDate->addDays(1); // 翌日を案内
                        $this->Flash->error('納品希望日は ' . $displayDate->format('m') . '月' . $displayDate->format('d') . '日 以降を設定してください。');
                        $this->set(compact('TFoodOrder','userName','groupedFoods','categoryOptions','addDays','minDate','deliReqDate','categoryId','selectedFoodId'));
                        $this->set('mode', 'add');
                        return $this->render('add_edit');
                    }

                Log::debug('[PASS] 17:00ブロックは未発火、保存処理へ進む');

                // Log::debug('バリデーションエラー: ' . print_r($TFoodOrder->getErrors(), true));
                // Log::debug('📦 POSTデータ: ' . print_r($data, true));
                // Log::debug('📌 パッチ後: ' . print_r($TFoodOrder, true));

                if (!isset($data['disp_no'])) {
                        $data['disp_no'] = 0; // または適切な初期値
                    }

                    $data['order_status'] = '0'; // ← 初期値代入

                    $TFoodOrder = $this->TFoodOrder->patchEntity($TFoodOrder, $data);

                    $TFoodOrder->order_status = '0';
                    $TFoodOrder->del_flg = 0;
                    $TFoodOrder->user_id = $userId;
                    $TFoodOrder->create_user = $userId;
                    $TFoodOrder->update_user = $userId;

                // 配列として中身を確認
                Log::debug('💾 保存前エンティティ配列: ' . print_r($TFoodOrder->toArray(), true));

                // もしくはJSON形式で見たい場合
                Log::debug('💾 保存前エンティティJSON: ' . json_encode($TFoodOrder->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                
                if ($this->TFoodOrder->save($TFoodOrder)) {
                    Log::debug('登録処理');
                    $this->Flash->success('登録しました。');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $errors = $TFoodOrder->getErrors(); 
                    Log::debug(print_r('登録失敗 : '.$errors, true));
                    $this->Flash->error('登録に失敗しました。');
                }
            }

        } catch (\Exception $e) {
            $this->Flash->error('システムエラーです。登録に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
    /**
     * Edit method
     *
     * @param string|null $id T Food Order id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        
        $TFoodOrder = $this->TFoodOrder->get($id);
        Log::debug("更新処理開始 - ID: {$id}");
        // Log::debug('食材発注のゲット:'.print_r($TFoodOrder,true));

        $identity = $this->Authentication->getIdentity();
        $userId = $identity?->get('user_id');

        // 認証ユーザー名取得（必要なら）
        $userName = null;
        if ($TFoodOrder->user_id) {
            $MUser = $this->fetchTable('MUser')->get($TFoodOrder->user_id);
            $userName = $MUser->user_name ?? null;
        }

        // 食材一覧（MFoods）
        $mFoods = $this->fetchTable('MFoods')->find()
            ->select(['food_id', 'food_name', 'food_specification', 'category_id'])
            ->where(['del_flg' => '0'])
            ->order(['disp_no' => 'ASC'])
            ->all();

        // 分類：カテゴリ一覧
        $categoryOptions = $this->fetchTable('MFoodCategories')->find('list', 
                keyField : 'category_id',
                valueField : 'category_name'
            )
            ->where(['del_flg' => '0'])
            ->order(['disp_no' => 'ASC'])
            ->toArray();

        // food_idからcategory_id取得
        $categoryId = null;
        if ($TFoodOrder->food_id) {
            $food = $this->fetchTable('MFoods')->get($TFoodOrder->food_id);
            $categoryId = $food->category_id ?? null;
        }

        // 商品規格セレクト用（distinct food_specification）
        $specOptions = [];
        foreach ($mFoods as $food) {
            $val = $food->food_specification;
            if ($val !== null && $val !== '') {
                $specOptions[$val] = $val;
            }
        }

        // 食材を分類ごとにグループ化
        $groupedFoods = [];
        foreach ($mFoods as $food) {
            $groupedFoods[$food->category_id][] = [
                'id' => $food->food_id,
                'label' => $food->food_name . ' / ' . $food->food_specification
            ];
        }

        $minDate = $TFoodOrder->deli_req_date?->format('Y-m-d') ?? null;

        try {
            if ($this->request->is(['patch', 'post', 'put'])) {
                $data = $this->request->getData();
                

                // 納品希望日の空欄チェック（必須にするなら）
                if (empty($data['deli_req_date'])) {
                    
                    $this->Flash->error('納品希望日は必須です。');
                    // フォーム再表示
                    $this->set(compact('TFoodOrder', 'userName', 'groupedFoods', 'categoryOptions', 'specOptions', 'minDate', 'categoryId'));
                    $this->set('mode', 'edit');
                    $identity = $this->Authentication->getIdentity();
                    $useSvc = (int)$identity->get('use_service_id');
                    $this->set('useSvc', $useSvc);
                    $this->render('add_edit');
                    return;
                }

                // 改ざんチェック（納品希望日の変更禁止）
                try {
                    $inputDate = new \DateTime($data['deli_req_date']);
                    $originalDate = $TFoodOrder->deli_req_date;
                    if ($inputDate->format('Y-m-d') !== $originalDate->format('Y-m-d')) {
                        $this->Flash->error('納品希望日は変更できません。');
                        Log::warning("改ざん検出: {$originalDate->format('Y-m-d')} → {$inputDate->format('Y-m-d')}");
                        return $this->redirect(['action' => 'edit', $id]);
                    }
                } catch (\Exception $e) {
                    $this->Flash->error('納品希望日の形式が不正です。');
                    Log::error('納品希望日の変換エラー: ' . $e->getMessage());
                    return $this->redirect(['action' => 'edit', $id]);
                }

                // disp_noがなければ0をセット
                if (!isset($data['disp_no'])) {
                    $data['disp_no'] = 0;
                }

                // 更新ユーザーをセット
                $data['update_user'] = $userId;

                // patchEntity は1回だけ
                $TFoodOrder = $this->TFoodOrder->patchEntity($TFoodOrder, $data);

                // バリデーションエラーがあれば表示してフォーム再表示
                if ($TFoodOrder->getErrors()) {
                    // エラーメッセージ設定
                    $this->Flash->error('入力に誤りがあります。');
                    // フォーム再表示
                    $this->set(/* 変数セット */);
                    $this->set('mode', 'edit');
                    $identity = $this->Authentication->getIdentity();
                    $useSvc = (int)$identity->get('use_service_id');
                    $this->set('useSvc', $useSvc);
                    return $this->render('add_edit');
                }

                if ($this->TFoodOrder->save($TFoodOrder)) {
                    $this->Flash->success('更新しました。');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('更新に失敗しました。');
                    Log::error('更新失敗', ['data' => $data, 'errors' => $TFoodOrder->getErrors()]);
                }
            }
        } catch (\Exception $e) {
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        
        Log::debug('食材発注set前:'.print_r($TFoodOrder,true));

        // ここでのrenderは初期表示や失敗時のみ
        $this->set(compact('TFoodOrder', 'userName', 'groupedFoods', 'categoryOptions', 'specOptions', 'minDate', 'categoryId'));
        $this->set('mode', 'edit');
        $identity = $this->Authentication->getIdentity();
        $useSvc = (int)$identity->get('use_service_id');
        $this->set('useSvc', $useSvc);
        return $this->render('add_edit');
    }

    // チェックした行の単品食材発注情報を確定し、同時にデータを書き出してダウンロードフォルダに保存。
    private function exportConfirmedOrders(array $ids)
    {
        // POSTデータ取得
        $query = $this->request->getData();

        // ★ここで条件を作る（必須）
        $conditions = ['TFoodOrder.del_flg' => 0];

        if (!empty($query['order_date_from'])) {
            $conditions['order_date >='] = $query['order_date_from'];
        }
        if (!empty($query['order_date_to'])) {
            $conditions['order_date <='] = $query['order_date_to'];
        }
        if (!empty($query['deli_req_date_from'])) {
            $conditions['deli_req_date >='] = $query['deli_req_date_from'];
        }
        if (!empty($query['deli_req_date_to'])) {
            $conditions['deli_req_date <='] = $query['deli_req_date_to'];
        }
        if (isset($query['order_status']) && $query['order_status'] !== '') {
            $conditions['order_status'] = $query['order_status'];
        }
        if (!empty($query['user_id'])) {
            $conditions['TFoodOrder.user_id'] = $query['user_id'];
        }
       $orders = $this->TFoodOrder->find()
                ->contain([
                    'MUsers.MUserGroups',
                    'MFoods.MFoodCategories'
                    ]) // ここ追加
                ->where($conditions)
                ->order(['order_date' => 'ASC'])
                ->all();

        $csv = "単品食材発注ID,施設グループ番号,施設グループ名称,ユーザID(施設番号),施設名,発注日,納品希望日,コード番号,商品名,分類ID,分類名称,規格,発注数,発注状態\n";

        foreach ($orders as $order) {
            $userGroup = $order->m_user->m_user_group ?? null;
            
            $csv .= implode(',', [
                    '="' . $order->food_order_id . '"',
                    '="' . ($userGroup?->user_group_id   ?? '') . '"',
                    '="' . ($userGroup?->user_group_name ?? '') . '"',
                    '="' . $order->user_id . '"',
                    '="' . ($order->m_user->user_name ?? '') . '"',
                    '="' . ($order->order_date ? $order->order_date->format('Y/m/d') : '') . '"',
                    '="' . ($order->deli_req_date ? $order->deli_req_date->format('Y/m/d') : '') . '"',
                    '="' . ($order->m_food?->m_food_category?->category_id ?? '') . '"',
                    '="' . ($order->m_food?->m_food_category?->category_name ?? '') . '"',
                    '="' . $order->food_id . '"',
                    '="' . ($order->m_food?->food_name ?? '') . '"',
                    '="' . ($order->m_food?->food_specification ?? '') . '"',
                    '="' . $order->order_quantity . '"',
                    '="' . ($order->order_status === "1" ? "確定" : "未確定") . '"',
            ]) . "\n";
        }

        // 🔵 ← ここを固定（あなたの Windows のユーザー名を使用）
        $downloads = "C:/Users/sonic/Downloads/";

        if (!is_dir($downloads)) {
            throw new \RuntimeException("Downloads フォルダが見つかりません: $downloads");
        }

        $fileName = date('Ymd') . '.csv';
        $path = $downloads . $fileName;

        file_put_contents($path, mb_convert_encoding($csv, 'SJIS-win', 'UTF-8'));
        $now = FrozenTime::now();
        $loginUserId = $this->request->getAttribute('identity')->get('user_id') ?? 'system';

        // Aテーブル（t_food_order_fix）のみ更新
        $TFix = $this->fetchTable('TFoodOrderFix');
        $TFix->updateAll([
            'export_confirm_date' => $now,
            'update_user'         => $loginUserId,
            'update_date'         => $now,
        ], ['food_order_id IN' => $ids]);

        return $path;
    }

    public function export()
    {
        $query = $this->request->getQueryParams();
        $conditions = ['TFoodOrder.del_flg' => 0];

        $mUserTable = $this->fetchTable('MUser');
        $users = $mUserTable->find('list', 
                keyField    : 'user_id',
                valueField  : 'user_name'
            )->where([
                'del_flg' => 0,
                'use_service_id IN' => [3, 4]  // ← IN を明示
            ])->toArray();

        $filterUserId = $query['user_id'] ?? null;

        if (!empty($query['order_date_from'])) {
            $conditions['order_date >='] = $query['order_date_from'];
        }
        if (!empty($query['order_date_to'])) {
            $conditions['order_date <='] = $query['order_date_to'];
        }
        if (!empty($query['deli_req_date_from'])) {
            $conditions['deli_req_date >='] = $query['deli_req_date_from'];
        }
        if (!empty($query['deli_req_date_to'])) {
            $conditions['deli_req_date <='] = $query['deli_req_date_to'];
        }
        // export_confirm_date_from
        if (!empty($query['export_confirm_date_from'])) {
            $conditions[] = [
                'COALESCE(TFoodOrderFix.export_confirm_date, TFoodOrder.export_confirm_date) >=' 
                    => $query['export_confirm_date_from']
            ];
        }

        // export_confirm_date_to
        if (!empty($query['export_confirm_date_to'])) {
            $conditions[] = [
                'COALESCE(TFoodOrderFix.export_confirm_date, TFoodOrder.export_confirm_date) <=' 
                    => $query['export_confirm_date_to']
            ];
        }

        if (isset($query['order_status']) && $query['order_status'] !== '') {
            $conditions['order_status'] = $query['order_status'];
        }
        if (!empty($query['user_id'])) {
            $conditions['TFoodOrder.user_id'] = $filterUserId;
        }

            $orders = $this->TFoodOrder->find()
                ->contain(['TFoodOrderFix'])
                ->where($conditions)
                ->order(['TFoodOrder.order_date' => 'ASC'])
                ->all();

            $dataCount = $orders->count();
            Log::debug("B件数: {$dataCount}");
            $queryParams = $this->request->getQuery();

            Log::debug('668 queryParams内容: ' . print_r($queryParams, true));

            // 2) トップレベルの型・配列かどうか・要素数
            Log::debug(sprintf('[A=query]  type=%s is_array=%s count=%s',
                function_exists('get_debug_type') ? get_debug_type($query) : gettype($query),
                is_array($query) ? 'true' : 'false',
                is_array($query) ? count($query) : 'n/a'
            ));

        $this->set(compact('orders', 'dataCount', 'users'));
        
        // POSTで書出し要求が来たときだけCSV出力
        if ($this->request->is('post')) {
            $fileName = $this->request->getData('export_file_name');

            // ファイル名が入力されていない場合、ファイル名に'YYYYMMDD.csv'を設定
            if (empty($fileName)) {
                $fileName = date('Ymd') . '.csv';
            } else {
                // 入力があって、末尾が .csv で終わっていなければ追加
                if (!preg_match('/\.csv$/i', $fileName)) {
                    $fileName .= '.csv';
                }
            }

            // ここで POST データから再取得
            // POSTデータ取得
            $query = $this->request->getData(); // ← これOK

            $filterUserId = $query['user_id'] ?? null;
            
            // ✅ ここで conditions を組み立て直す（これが今抜けてる）
            $conditions = ['TFoodOrder.del_flg' => 0];
            if (!empty($query['order_date_from'])) {
                $conditions['order_date >='] = $query['order_date_from'];
            }
            if (!empty($query['order_date_to'])) {
                $conditions['order_date <='] = $query['order_date_to'];
            }
            if (!empty($query['deli_req_date_from'])) {
                $conditions['deli_req_date >='] = $query['deli_req_date_from'];
            }
            if (!empty($query['deli_req_date_to'])) {
                $conditions['deli_req_date <='] = $query['deli_req_date_to'];
            }
            if (!empty($query['export_confirm_date_from'])) {
                $conditions['export_confirm_date >='] = $query['export_confirm_date_from'];
            }

            if (!empty($query['export_confirm_date_to'])) {
                $conditions['export_confirm_date <='] = $query['export_confirm_date_to'];
            }
            if (isset($query['order_status']) && $query['order_status'] !== '') {
                $conditions['order_status'] = $query['order_status'];
            }

            
            if (!empty($query['user_id'])) {
                $conditions['TFoodOrder.user_id'] = $filterUserId;
            }

            $orders = $this->TFoodOrder->find()
                ->contain([
                    'MUsers.MUserGroups',
                    'MFoods.MFoodCategories'
                    ]) // ここ追加
                ->where($conditions)
                ->order(['order_date' => 'ASC'])
                ->all();
            

            try {
            // BOM付きCSVを作成
            $csv = "単品食材発注ID,施設グループ番号,施設グループ名称,ユーザID(施設番号),施設名,発注日,納品希望日,コード番号,商品名,分類ID,分類名称,規格,発注数,発注状態\n";
            foreach ($orders as $order) {
                $userGroup = $order->m_user->m_user_group ?? null;
                $csv .= implode(',', [
                    '="' . $order->food_order_id . '"',
                     '="' . ($userGroup?->user_group_id   ?? '') . '"',
                    '="' . ($userGroup?->user_group_name ?? '') . '"',
                    '="' . $order->user_id . '"',
                    '="' . ($order->m_user->user_name ?? '') . '"',
                    '="' . ($order->order_date ? $order->order_date->format('Y/m/d') : '') . '"',
                    '="' . ($order->deli_req_date ? $order->deli_req_date->format('Y/m/d') : '') . '"',
                    '="' . ($order->m_food?->m_food_category?->category_id ?? '') . '"',
                    '="' . ($order->m_food?->m_food_category?->category_name ?? '') . '"',
                    '="' . $order->food_id . '"',
                    '="' . ($order->m_food?->food_name ?? '') . '"',
                    '="' . ($order->m_food?->food_specification ?? '') . '"',
                    '="' . $order->order_quantity . '"',
                    '="' . ($order->order_status === "1" ? "確定" : "未確定") . '"'
                    ]) . "\n";
            }

            // ここでBOMを付加（Excel用）
            $bom = "\xEF\xBB\xBF";
            $csvWithBom = $bom . $csv;

            // レスポンスで返す（=ダウンロード）
        $this->response = $this->response->withType('csv');
        $this->response = $this->response->withDownload($fileName);
        $this->response = $this->response->withStringBody($csvWithBom);

        //書出し
        $now = FrozenTime::now();
        $loginUserId = $this->request->getAttribute('identity')->get('user_id') ?? 'system';

        foreach ($orders as $order) {

            if ($order->order_status == 1) {
                // 確定済 → Fix
                $this->fetchTable('TFoodOrderFix')->updateAll([
                    'export_confirm_date' => $now,
                    'update_user'         => $loginUserId,
                    'update_date'         => $now,
                ], ['food_order_id' => $order->food_order_id]);

            } else {
                // 未確定 → B
                $this->TFoodOrder->updateAll([
                    'export_confirm_date' => $now,
                    'update_user'         => $loginUserId,
                    'update_date'         => $now,
                ], ['food_order_id' => $order->food_order_id]);
            }
        }
        

            // 書き出し成功時、indexに遷移（※Flash後でもDLは成功する）
            return $this->response;

        } catch (\Throwable $e) {
            $this->Flash->error('書出しに失敗しました：' . $e->getMessage());
            return $this->redirect(['action' => 'export'] + $this->request->getQuery());
        }
        }
    }

    // 管理者用更新画面
    public function editmaster($id = null)
    {
        //初期表示
            $info   = (array)$this->getLoginUserInfo();
            $useSvc = (int)($info['use_service_id'] ?? 0);
            if ($useSvc !== 1) {
                $this->Flash->error('確定している為、更新できません。');
                return $this->redirect(['action' => 'index']);
            }
            Log::debug('食材発注の番号:'.$id);

            // 2) テーブル取得
            $Fix = $this->fetchTable('TFoodOrderFix');
                $TFoodOrder = $Fix->find()
                ->where(['food_order_id' => $id])
                ->first();
            if($TFoodOrder){// B が見つかった：そのまま編集へ
                Log::debug('B found: ' . $TFoodOrder->id);
            }else{
                $TFoodOrder = $this->TFoodOrder->get($id);
                // Log::debug('食材発注のゲット:'.print_r($TFoodOrder,true));
            }

                    Log::debug("更新処理開始 - ID: {$id}");
                    // Log::debug('食材発注のゲット:'.print_r($TFoodOrder,true));

                    $identity = $this->Authentication->getIdentity();
                    $userId = $identity?->get('user_id');

                    // 認証ユーザー名取得（必要なら）
                    $userName = null;
                    if ($TFoodOrder->user_id) {
                        $MUser = $this->fetchTable('MUser')->get($TFoodOrder->user_id);
                        $userName = $MUser->user_name ?? null;
                    }
            Log::debug('社名:'.$userName);

            // 食材一覧（MFoods）
            $mFoods = $this->fetchTable('MFoods')->find()
                ->select(['food_id', 'food_name', 'food_specification', 'category_id'])
                ->where(['del_flg' => '0'])
                ->order(['disp_no' => 'ASC'])
                ->all()
                ->toList(); 
                // name カラムだけを配列にする
            $names = array_column($mFoods, 'food_name');
            // Log::debug('食材一覧（MFoods）:'.print_r($names,true));

            // 分類：カテゴリ一覧
            $categoryOptions = $this->fetchTable('MFoodCategories')->find('list', 
                    keyField : 'category_id',
                    valueField : 'category_name'
                )
                ->where(['del_flg' => '0'])
                ->order(['disp_no' => 'ASC'])
                ->toArray();
            // Log::debug('分類：カテゴリ一覧:'.print_r($categoryOptions,true));

            // food_idからcategory_id取得
            $categoryId = null;
            if ($TFoodOrder->food_id) {
                $food = $this->fetchTable('MFoods')->get($TFoodOrder->food_id);
                $categoryId = $food->category_id ?? null;
            }
            // Log::debug('food_idからcategory_id取得:'.print_r($categoryId,true));

            // 商品規格セレクト用（distinct food_specification）
            $specOptions = [];
            foreach ($mFoods as $food) {
                $val = $food->food_specification;
                if ($val !== null && $val !== '') {
                    $specOptions[$val] = $val;
                }
            }
            // Log::debug('商品規格セレクト用（distinct food_specification）:'.print_r($specOptions,true));

            // 食材を分類ごとにグループ化
            $groupedFoods = [];
            foreach ($mFoods as $food) {
                $groupedFoods[$food->category_id][] = [
                    'id' => $food->food_id,
                    'label' => $food->food_name . ' / ' . $food->food_specification
                ];
            }
            // Log::debug('食材を分類ごとにグループ化:'.print_r($groupedFoods,true));
            
            $minDate = $TFoodOrder->deli_req_date?->format('Y-m-d') ?? null;
            $identity = $this->Authentication->getIdentity();
        
        if ($this->request->is(['post', 'put', 'patch'])) {
            $postdata = $this->request->getData();      // 配列
            Log::debug('ゲットデータ: ' . print_r($postdata, true));
            $inputTable = $this->TFoodOrder->get($id);
            // 3) A から補充したい値を用意（必要なカラムだけ！）

            if (empty($postdata['deli_shedule_date'])) {
                $this->Flash->error('納品予定日は必須です。');
                return $this->redirect($this->referer());
            }

            $fromA = [
                'user_id'   => $inputTable->user_id,          // 外部キー
                'order_status'   => $inputTable->order_status,
                'del_flg'   => $inputTable->del_flg,
            ];
            Log::debug('fromA: ' . print_r($fromA, true));
            $extra = [
                        'food_order_id'  => $id,
                        'create_user' => $userId,
                        'update_user' => $userId,
                    ];
            $data = $postdata + $fromA;
            Log::debug('+fromA その後: ' . print_r($data, true));
            $data['deli_shedule_date'] = $postdata['deli_shedule_date'];
            $data = array_merge($data, $extra); // 同じキーがあれば $extra 側で上書き
            
            
            Log::debug('merge後: ' . print_r($data, true));
            $TFOFixTable = $this->fetchTable('TFoodOrderFix');
            $TFOFix = $TFOFixTable->newEmptyEntity();
            $TFOFix = $TFOFixTable->patchEntity($TFOFix, $data);
            Log::debug('登録するデータ: ' . print_r($data, true));

            if ($TFOFixTable->save($TFOFix)) {
                $this->Flash->success('登録しました');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('登録に失敗しました。');
        }

        // 10) 希望日を min に（editmaster は希望日固定）
                $minDate = $TFoodOrder->deli_req_date?->format('Y-m-d') ?? null;
                // $this->set('foodScheduleField', $this->foodScheduleField());
                // $this->set('foodScheduleField', 'deli_schedule_date');


                $selectedCategoryId = $categoryId ?? null;

                $statusLabels = [
                    '0' => '未確定',
                    '1' => '確定',
                    '2' => 'キャンセル'
                ];
                $displayText = $statusLabels[$TFoodOrder->order_status ?? ''] ?? '';

                // ログ出力（任意）
                Log::debug('categoryId = ' . var_export($categoryId, true));
                Log::debug('food_id = ' . var_export($TFoodOrder->food_id, true));
                // Log::debug('foodOptions = ' . json_encode($foodOptions));

                $initialFoodId = $TFoodOrder->food_id ?? null;

                
        // Log::debug('食材発注set前:'.print_r($TFoodOrder,true));

            // 11) ビューへセット
            $this->set(compact('TFoodOrder', 'userName', 'groupedFoods', 'categoryOptions', 'specOptions', 'minDate', 'categoryId'));
            $identity = $this->Authentication->getIdentity();
            $useSvc = (int)$identity->get('use_service_id');
            $this->set('useSvc', $useSvc);

    }
}
