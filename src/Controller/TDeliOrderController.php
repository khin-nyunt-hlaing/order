<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Log\Log; 
use Cake\Collection\Collection;
use Cake\ORM\Query;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\Date;
use \Exception;

/**
 * TDeliOrder Controller
 *
 * @property \App\Model\Table\TDeliOrderTable $TDeliOrder
 */
class TDeliOrderController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
public function index()
{
    // --- ① 権限・共通値 ---
            $perm = $this->decidePermission((string)$this->request->getParam('controller'));
            Log::debug('権限番号'.$perm);
            if ($perm < 0) {
                throw new ForbiddenException('このアカウントでは利用できません。');
            }
            $this->set('usePermission', $perm);
            $this->set('permissionCode', $perm);

            $level = match ($perm) { 1 => 1, 2,4 => 2, 5 => 0, default => -1 };
            $this->set('level', $level);
        // コントローラ単位でログインユーザーの権限レベルを判定する

            $identity  = $this->getRequest()->getAttribute('identity');
            $userId    = $identity ? $identity->get('user_id') : null;
            $serviceId = (int)($identity?->get('use_service_id') ?? 0);

            // ★ 追加：ログインユーザーID（文字列）
            $loginUserId = (string)($identity?->get('user_id') ?? '');

        // --- ② 抽出条件 ---
            $queryParams = $this->request->is('post') ? $this->request->getData() : $this->request->getQueryParams();

        // --- ③ 画面表示用データを構築（一覧 + ラベル + 行/ページ活性）---
            $dispUserIds = (array)($this->getRequest()->getAttribute('disp_user_ids') ?? []);
            [$tDeliOrder, $users, $pageFlags] = $this->composeIndexViewData(
                $queryParams,
                (string)($userId ?? ''),
                (int)$level,
                $dispUserIds
            );

        // 画面表示用の共通セット
            $count        = count($tDeliOrder);
            $userName     = $identity?->get('user_name') ?? '';
            $confirmError = null;
            $loginUserId  = (string)$identity?->get('user_id');

            // Log::debug(json_encode($tDeliOrder, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->set(compact('tDeliOrder','count','users','userName','confirmError','pageFlags'));
            $this->set('loginUserId', $loginUserId);

        // 互換のため
            $this->set('hasSelectable',    $pageFlags['hasSelectable']    ?? false);
            $this->set('hasConfirmable',   $pageFlags['hasConfirmable']   ?? false);
            $this->set('hasUnconfirmable', $pageFlags['hasUnconfirmable'] ?? false);

    // --- ④ POST アクション ---
    if ($this->request->is('post')) {
            // action は POST優先で取得
            $action = $this->request->getData('action') ?? $this->request->getQuery('action') ?? '';

            Log::debug(sprintf("[POST] method=%s action='%s' keys=%s",
                $this->request->getMethod(),
                $action,
                implode(',', array_keys((array)$this->request->getData()))
            ));

            // 1) 画面から来た select を受ける（階層配列）
            $selected = (array)$this->request->getData('select');

            // 2) 1回のループで selectedIds / selectedPairs / selectedOwnerId を抽出
            $selectedIds = [];
            $selectedPairs = [];
            $selectedOwnerId = null;
                
            //★1件のみ通過
                $select = $this->request->getData('select');
                        $count = 0;

                        if (!empty($select)) {
                            foreach ($select as $key1 => $arr) {
                                foreach ($arr as $key2 => $val) {
                                    Log::debug("select[{$key1}][{$key2}] = {$val}");
                                    $count++; // チェック数をインクリメント
                                }
                            }
                        }
                        Log::debug("チェックされた数: {$count}");

            foreach ($selected as $termId => $owners) {
                // 想定：$owners は [ownerUid => '1' or '0' or (無し)] の配列
                if (!is_array($owners)) {
                    // hiddenField=false で term 単位だけを直接持たせる設計ならここで拾う
                    if (!empty($owners)) {
                        $selectedIds[] = (int)$termId;
                    }
                    continue;
                }

                $termPicked = false;
                foreach ($owners as $ownerUid => $v) {
                    if ((string)$v === '1') {
                        $termPicked = true;
                        $selectedPairs[] = [(int)$termId, (string)$ownerUid];
                        // 単一選択時の owner を控える（複数時は後で無視）
                        if ($selectedOwnerId === null) {
                            $selectedOwnerId = (string)$ownerUid;
                        }
                    }
                }
                if ($termPicked) {
                    $selectedIds[] = (int)$termId;
                }
            }

            // 重複除去＆整形
            $selectedIds   = array_values(array_unique($selectedIds));
            $selectedPairs = array_values($selectedPairs);

            // 単一 term のみかどうか
            $isSingle = (count($selectedIds) === 1);

            // payload へ
            $payload = $this->request->getData();
            $payload['selected_ids'] = $selectedIds;
            if ($isSingle && $selectedOwnerId !== null) {
                $payload['user_id'] = $selectedOwnerId;
            }

            // ログ（必要なものだけ）
            Log::debug('🔎 payload.selected_ids=' . json_encode($payload['selected_ids']));
            Log::debug('🔎 payload.user_id=' . json_encode($payload['user_id'] ?? null));

            // 早期分岐：export / add / edit / search
            if ($action === 'export') {
                $this->request->allowMethod(['post']);
                $data   = $this->request->getData();
                unset($data['select']); 
                Log::debug('POST data: ' . print_r($data, true));
                // URLに載せて良い値だけを持たせる（例：カラム1 from/to）
                $carry = [
                    'entry_start_date_from' => $data['entry_start_date_from'] ?? null,
                    'entry_start_date_to'   => $data['entry_start_date_to']   ?? null,
                    'add_deadline_date_from' => $data['add_deadline_date_from'] ?? null,
                    'add_deadline_date_to'   => $data['add_deadline_date_to']   ?? null,
                    'create_date_from'      => $data['create_date_from'] ?? null,
                    'create_date_to'        => $data['create_date_to']   ?? null,
                    'update_date_from'      => $data['update_date_from'] ?? null,
                    'update_date_to'        => $data['update_date_to']   ?? null,

                    'user_id'             => $data['user_id'] ?? null,
                    'confirm_status'        => $data['confirm_status']   ?? null,
                ];
                $carry = array_filter($carry, fn($v) => $v !== '' && $v !== null);
                Log::debug('[TFood search ▶ carry] ' . json_encode($carry, JSON_UNESCAPED_UNICODE));

                return $this->redirect(['action' => 'export', '?' => $carry], 303);
            }
            // add
            if ($action === 'add') {
                    Log::debug("💠 add 分岐に入りました");
                    //add➡editに入る
                    Log::debug('[add] ▶ enter '
                        . 'query=' . json_encode($this->request->getQuery(), JSON_UNESCAPED_UNICODE)
                        . ' method=' . $this->request->getMethod());

                    // ★ 1件以外はエラー
                        if ($count !== 1) {
                            $this->Flash->error('登録または更新は1件のみ選択可能です。');
                            return $this->redirect(['action' => 'index']);
                        }

                    // ★１ loginUserId は使わない。serviceId のみ渡す。
                    $res = $this->setSelectedTermOwnerAndDecideAction($payload, (int)$serviceId, (string)$loginUserId);

                    if (!empty($res['error'])) {
                        // Lv5 でも「閲覧として編集画面へ」は許可する
                        if (($res['error'] === 'blocked_service5') && (($res['action'] ?? null) === 'edit')) {
                            $sess = $this->request->getSession();
                            $sess->write('ReadOnly',            true); // ★閲覧フラグ
                            $sess->write('SelectedTermId',      (int)$res['term_id']);
                            $sess->write('SelectedOwnerId',     (string)($res['user_id'] ?? ''));
                            if (!empty($res['deli_order_id'])) {
                                $sess->write('SelectedDeliOrderId', (int)$res['deli_order_id']);
                            }
                            Log::debug('【サービス５なら】配列内容: ' . print_r($sess, true));
                            return $this->redirect(['action' => 'edit']); // ← クエリ無しでOK
                        }

                        $sess = $this->request->getSession();
                        $sess->write('ReadOnly',            false); // 通常編集
                        $sess->write('SelectedTermId',      (int)'term_id');
                        $sess->write('SelectedOwnerId',     (string)'user_id');
                        $sess->write('SelectedDeliOrderId', (int)'deli_order_id');
                        Log::debug('【ＥＲＲＯＲなら】配列内容: ' . print_r($sess, true));

                        return $this->redirect(['action' => 'edit']);
                    }

                    // ★ edit決定 ＆ deli_order_id 付きなら PK で edit へ（セッション経由・クエリ無し）
                    if (($res['action'] ?? null) === 'edit' && !empty($res['deli_order_id'])) {
                        $sess = $this->request->getSession();
                        $sess->write('ReadOnly',            false); // 通常編集
                        $sess->write('SelectedTermId',      (int)$res['term_id']);
                        $sess->write('SelectedOwnerId',     (string)($res['user_id'] ?? ''));
                        $sess->write('SelectedDeliOrderId', (int)$res['deli_order_id']);
                        Log::debug('【正常操作なら】配列内容: ' . print_r($sess, true));
                        return $this->redirect(['action' => 'edit']);
                    }

                    //日付の活性非活性処理
                            $TermCol = $this->fetchTable('MTerm')->get((int)$res['term_id']);
                            $dateCol =$TermCol->add_deadline_date;

                            // null でなければ文字列化してログ
                            Log::debug('date_column={val}', [
                                'val' => $dateCol?->toDateString()
                            ]);
                            // 今日の日付
                            $today = FrozenDate::today();

                            if ($dateCol instanceof FrozenDate) {
                                if ($today < $dateCol) {
                                    Log::debug("今日({today})はカラム({col})より前です", [
                                        'today' => $today->toDateString(),
                                        'col'   => $dateCol->toDateString(),
                                    ]);
                                } elseif ($today > $dateCol) {
                                    Log::debug("今日({today})はカラム({col})より後です", [
                                        'today' => $today->toDateString(),
                                        'col'   => $dateCol->toDateString(),
                                    ]);
                                } else {
                                    Log::debug("今日({today})とカラム({col})は同じ日です");
                                    // 同じ日
                                    $now = FrozenTime::now();
                                    $noon = new FrozenTime('today 12:00:00');

                                    if ($now < $noon) {
                                        Log::debug("今日({today})とカラム({col})は同じ日、かつ現在は正午・前です", [
                                            'today' => $today->toDateString(),
                                            'col'   => $dateCol->toDateString(),
                                        ]);
                                    } else {
                                        Log::debug("今日({today})とカラム({col})は同じ日、かつ現在は正午後です", [
                                            'today' => $today->toDateString(),
                                            'col'   => $dateCol->toDateString(),
                                        ]);
                                    }
                                }
                            }

                    // 新規 or deli_order_id が無いケース（セッション経由・クエリ無し）
                    $sess = $this->request->getSession();
                    $sess->write('SelectedTermId',  (int)$res['term_id']);
                    $sess->write('SelectedOwnerId', (string)($res['user_id'] ?? ''));
                    Log::debug('【INDEX最後】配列内容: ' . print_r($sess, true));

                    return $this->redirect(['action' => $res['action']]); 
            }
            
            if (in_array($action, ['confirm', 'unconfirm'], true)) {
                $selected = $this->request->getData('select') ?? [];
                //★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★
                // 1) 形チェック
                foreach ($selected as $termId => $owners) {
                    if (!is_array($owners)) {
                        $this->Flash->error('不正なデータ形式です', ['key' => 'modal']);
                        //（多次元配列である必要があります）
                        return $this->redirect($this->referer());
                    }
                }

                // 2) ペア化
                $pairs = [];
                foreach ($selected as $termId => $owners) {
                    foreach ($owners as $userId => $val) {
                        if ($val) {
                            $pairs[] = ['term_id' => $termId, 'user_id' => $userId];
                        }
                    }
                }
                if (empty($pairs)) {
                    Log::debug('配食発注が選択されていません。');
                    $this->Flash->error('配食発注が選択されていません。', ['key' => 'modal']);
                    return $this->redirect($this->referer());
                }

                // 3) ペア → 対象行を取得（親IDで処理しない）
                $orders       = $this->TDeliOrder;
                $keyColumn    = 'deli_order_id';
                $targetColumn = 'order_status';
                $loginUserId  = (string)($identity?->get('user_id'));

                $or = [];
                foreach ($pairs as $p) {
                    $or[] = ['term_id' => $p['term_id'], 'user_id' => $p['user_id']];
                }

                $rows = $orders->find()
                    ->select([$keyColumn, 'term_id', 'user_id', $targetColumn])
                    ->where(['OR' => $or])
                    ->enableHydration(false) 
                    ->all()
                    ->toList();

                if (empty($rows)) {
                    $this->Flash->error('処理対象外が選択されています。', ['key' => 'modal']);
                    return $this->redirect($this->referer());
                }

                // ===== ここから「内包版」 =====

                // A) statusUnique 相当（ユニークな状態の抽出）
                $vals   = array_map(fn($r) => (int)$r[$targetColumn], $rows);
                $unique = array_values(array_unique($vals)); // [0] or [1] or [0,1]

                if ($action === 'confirm') {
                // 確定処理なのに、既に「確定済み(1)」が含まれていたらエラー
                    if (in_array(1, $unique, true)) {
                        $this->Flash->error('確定済のデータを含むため、確定できません。', ['key' => 'modal']);
                        return $this->redirect($this->referer());
                            }
                        } elseif ($action === 'unconfirm') {
                            // 確定解除処理なのに、既に「未確定(0)」が含まれていたらエラー
                            if (in_array(0, $unique, true)) {
                                $this->Flash->error('未確定のデータを含むため、確定できません。', ['key' => 'modal']);
                                return $this->redirect($this->referer());
                            }
                        }
                

                // B) 目標値決定と早期リターン
                $now = $unique[0];                    // 現在（0 or 1）
                $to  = ($action === 'confirm') ? 1 : 0; // 目標
                if ($now === $to) {
                    $this->Flash->info(($to === 1) ? 'すでに確定済みです。' : 'すでに未確定です。', ['key' => 'modal']);
                    return $this->redirect($this->referer());
                }

                    if (!isset($level) || (int)$level !== 1) {
                        $termIds = array_values(array_unique(array_map(fn($r) => $r['term_id'], $rows)));

                        $termTable = $this->fetchTable('MTerm');
                        $terms = $termTable->find()
                            ->select(['term_id', 'upd_deadline_date'])
                            ->where(['term_id IN' => $termIds])
                            ->disableHydration()
                            ->all()
                            ->toList();

                        // いま（JST）
                        $nowJst = $this->getSqlNowJst(); // 既存ヘルパがある前提。無ければ new \Cake\I18n\FrozenTime('now', 'Asia/Tokyo')

                        // どれか1つでも締切超過なら中断
                        foreach ($terms as $t) {
                            $cutoff = $this->at1200Jst($t['upd_deadline_date']); // 既存ヘルパ：その日の 12:00 JST を返す想定
                            if ($cutoff && $nowJst >= $cutoff) {
                                $this->Flash->error('受付は本日12:00で終了しました。締切日を超えている為、登録できません。管理者にご確認ください。', ['key' => 'modal']);
                                return $this->redirect($this->referer());
                            }
                        }
                    }

                // SQL Server 基準の“いま(JST)”を1回だけ取りに行く（あなたのキャッシュ済み実装）
                $now = $this->getSqlNowJst();            // e.g. 2025-09-08 11:23:45+09:00
                $today0  = $this->asDate0Jst($now);  // 2025-09-08 00:00:00+09:00
                $today12 = $this->at1200Jst($now);   // 2025-09-08 12:00:00+09:00

                $termsTable = $this->fetchTable('MTerm');
                foreach ($selectedIds as $termId) {
                    $term = $termsTable->get($termId);      // ← $this-> を外す
                    $col0 = $this->asDate0Jst($term->upd_deadline_date);
                    Log::debug('期間' . $col0->format('Y-m-d H:i:s'));
                    
                    $isToday = ($col0 == $today0);
                    $isFuture = ($col0 > $today0);
                    $isPast   = ($col0 < $today0);

                    if ($isToday) {
                        // 今日: 午前中のみ許可
                        $isAllowed = ($now < $today12);
                        } elseif ($isFuture) {
                            // 今日より後: 常に許可
                            $isAllowed = true;
                        } elseif ($isPast) {
                            // 今日より前: 常にNG
                            $isAllowed = false;
                        } else {
                            $isAllowed = false; // 念のため
                    }

                    if (!$isAllowed) {
                        // 期限切れが1つでもあれば即中断
                        $this->Flash->error('締切日を過ぎている為、更新できません。', ['key' => 'modal']);
                        return $this->redirect($this->referer());
                    }
                }
                

                // C) bulkToggleStatus 相当（個別IDで一括更新）
                $deliIds = array_map(fn($r) => (int)$r[$keyColumn], $rows);
                $set = [$targetColumn => $to];
                $now = FrozenTime::now()->format('Y-m-d H:i:s');
                if ($loginUserId !== '') {
                    $set['update_user'] = $loginUserId; // 必要なら Timestamp も自前で: 'modified' => FrozenTime::now()
                    $set['update_date'] = $now;
                }

                // IN [] 防止は上の empty($rows) で担保済み
                $updated = $orders->updateAll($set, [$keyColumn . ' IN' => $deliIds]);

                $msg = ($action === 'confirm') ? '確定しました。' : '確定を解除しました。';
                $this->Flash->success($msg);
                // PRG：必ずリダイレクトで終了
                return $this->redirect(['action' => 'index']);
            }
    

            if ($action === 'search') {
                    $queryParams = $this->request->getData();
                    $keyword = $this->request->getData(); // フォームから入力取得
                    // unset($keyword['select']);
                    Log::debug('🧪 check全体: ' . print_r($keyword, true));

                    // 🔹開始ログ（誰が・何を投げたか）
                    Log::debug('[TDeli search] ▶ start '
                        . ' viewer=' . (string)($userId ?? '')
                        . ' level=' .  (string)$level
                        . ' params=' . json_encode(array_filter($queryParams, fn($v) => $v !== '' && $v !== null), JSON_UNESCAPED_UNICODE));

                    // 1) 行の土台を作る
                    [$tDeliOrder, $users, $pageFlags] = $this->composeIndexViewData(
                        $queryParams,
                        (string)($userId ?? ''),
                        (int)$level,
                        (array)$dispUserIds
                    );
                    // 🔹compose 直後の件数
                    Log::debug('[TDeli search] after composeIndexViewData: rows=' . count($tDeliOrder));

                    // 🔹1件サンプル（entry_start_date が rows に載っているか確認）
                    if (!empty($tDeliOrder)) {
                        Log::debug('[rows sample] entry='.(string)($tDeliOrder[0]->entry_start_date ?? 'null'));
                    }

                    // 2) POST検索時は行レベルで絞り込み
                    $before = count($tDeliOrder);
                    $tDeliOrder = $this->filterRowsByParams($tDeliOrder, $queryParams, true);
                    $after  = count($tDeliOrder);

                    Log::debug("[TDeli search] filterRowsByParams: before={$before} after={$after}");

                    // 3) 活性フラグを再計算
                    $pageFlags = $this->computePageFlags($tDeliOrder);

                    // 4) 画面へ
                    $count    = count($tDeliOrder);
                    $userName = $identity?->get('user_name') ?? '';

                    $this->set(compact('tDeliOrder','count','users','userName','pageFlags'));
                    $this->set('hasSelectable',    $pageFlags['hasSelectable']    ?? false);
                    $this->set('hasConfirmable',   $pageFlags['hasConfirmable']   ?? false);
                    $this->set('hasUnconfirmable', $pageFlags['hasUnconfirmable'] ?? false);

                    Log::debug('[TDeli search] ◀ end count=' . $count);

                    return $this->render();
            }
    }

}
public function add()
{
    $this->request->allowMethod(['get', 'post']);
    Log::Debug('★以下add遷移後★');
        // まずログイン者IDを確定
        $identity     = $this->Authentication->getIdentity();
        $loginUserId  = (string)($identity?->get('user_id') ?? '');

        // ★セッション専用（クエリは読まない）
        $session = $this->request->getSession();
        $termId  = (int)($session->read('SelectedTermId') ?? 0);
        $ownerId = (string)($session->read('SelectedOwnerId') ?? $loginUserId);

        if ($termId <= 0 || $ownerId === '') {
            $this->Flash->error('選択情報が無効です。もう一度やり直してください。');
            return $this->redirect(['action' => 'index']);
        }

        // レベル取得（下の再代入をやめ、この位置で確定）
        $level = $this->currentLevel();
        $isL1  = ($level === 1);
        $isL2  = ($level === 2);
        $isAjaxL1 = $this->request->is('ajax') && $isL1;

        // L2 は自分のみ（偽装防止）
        if ($isL2 && $ownerId !== $loginUserId) {
            $this->Flash->error('自分のデータのみ新規作成可能です。');
            return $this->redirect(['action' => 'index']);
        }

        \Cake\Log\Log::debug("[add] resolved term_id={$termId} owner_id={$ownerId}"); // ← queryは出さない

        $deliOrderTable = $this->fetchTable('TDeliOrder');

        // 表示用の空エンティティ
        $mDelivery = $deliOrderTable->newEmptyEntity();

        // 期間取得
        $mTerm = $this->fetchTable('MTerm')->find()
            ->where(['term_id' => $termId])
            ->first();

        // 施設セレクト（サービス=2/4のみ）※テーブル・カラム名は実DBに合わせて
        $facilityOptions = [];
        if ($isL1) {
            $facilityOptions = $this->fetchTable('MUser')->find()
                ->select(['user_id','user_name'])
                ->where(['use_service_id IN' => [2,4], 'del_flg' => 0])
                ->orderAsc('user_name')
                ->all()
                ->combine('user_id','user_name')
                ->toArray();
        }
        $this->set('facilityOptions', $facilityOptions);

        if (!$mTerm) {
            $this->Flash->error('指定された献立期間が存在しません');
            return $this->redirect(['action' => 'index']);
        }

        // ②b 既存があれば edit へ
        $existing = $this->fetchTable('TDeliOrder')->find()
            ->select(['deli_order_id'])
            ->where(['term_id' => $termId, 'user_id' => $ownerId, 'del_flg' => '0'])
            ->orderAsc('deli_order_id')
            ->first();
        if ($existing) {
            $session->write('SelectedDeliOrderId', (int)$existing->deli_order_id);
            return $this->redirect(['action' => 'edit']); // ← クエリ出さない
        }

        // ③ ログイン者の施設名（表示用）
        $loginUser = $this->fetchTable('MUser')->find()
            ->select(['user_name'])
            ->where(['user_id' => $loginUserId])   // ← ここ
            ->firstOrFail()
            ->user_name;

        // 表示用整形
        $mDelivery->period_text        = $mTerm->start_date->i18nFormat('yyyy/M/d') . ' ～ ' . $mTerm->end_date->i18nFormat('yyyy/M/d');
        $mDelivery->add_deadline_date  = $mTerm->add_deadline_date->i18nFormat('yyyy/M/d');
        $mDelivery->order_status_text  = '受付中';
        $mDelivery->request_status_text= '未確定';

        //日付の活性非活性処理
            $TermCol = $this->fetchTable('MTerm')->get($termId);
            $dateCol =$TermCol->add_deadline_date;

                // null でなければ文字列化してログ
                Log::debug('date_column={val}', [
                    'val' => $dateCol?->toDateString()
                ]);
                // 今日の日付
                $today = FrozenDate::today();
                $isActive = true;
                 // 同じ日
                $now = FrozenTime::now();
                $noon = new FrozenTime('today 12:00:00');

                if ($dateCol instanceof FrozenDate) {
                    if ($today < $dateCol) {
                        Log::debug("【登録画面】今日({today})はカラム({col})より前です", [
                            'today' => $today->toDateString(),
                            'col'   => $dateCol->toDateString(),
                        ]);
                        $isActive = true;
                    } elseif ($today > $dateCol) {
                        Log::debug("【登録画面】今日({today})はカラム({col})より後です", [
                            'today' => $today->toDateString(),
                            'col'   => $dateCol->toDateString(),
                        ]);
                        $isActive = false;
                    } else {
                        Log::debug("【登録画面】今日({today})とカラム({col})は同じ日です");
                            if ($now < $noon) {
                                Log::debug("【登録画面】今日({today})とカラム({col})は同じ日、かつ現在は正午・前です", [
                                    'today' => $today->toDateString(),
                                    'col'   => $dateCol->toDateString(),
                                ]);
                                $isActive = true;
                            } else {
                                Log::debug("【登録画面】今日({today})とカラム({col})は同じ日、かつ現在は正午後です", [
                                    'today' => $today->toDateString(),
                                    'col'   => $dateCol->toDateString(),
                                ]);
                                $isActive = false;
                            }
                    }
                }
                // 初期表示セット（POST後・描画前）
    $tDeliOrder = $deliOrderTable->newEmptyEntity();
    $tDeliOrder->term_id = $termId;
    $tDeliOrder->user_id = $ownerId;
        
    // L1(add) は最初は空、他は従来どおり
    if ($isL1) {
        $deliveryItems  = [];
        $days           = [];
        $quantityValues = [];
    } else {
        [$deliveryItems, $days] = $this->buildDeliveryMatrix($termId, $ownerId);
        $quantityValues = $this->request->is('post') ? ($this->request->getData('quantity') ?? []) : [];
    }

    // View が参照している $userName を用意
    $userName = $loginUser; // ← さきほど取得したログイン者名をそのまま表示用に
        $mode = 'add';

    //post
    if ($this->request->is('post')) {
        Log::debug("✅ add() POST 処理開始");
        // ▼ 一時診断（認証前） 抽出用
        $uid  = (string)$this->request->getData('user_id');
        $raw  = (string)$this->request->getData('password');
        $u    = $this->fetchTable('MUser')->find()->select(['user_id','password'])->where(['user_id' => $uid])->first();
        $ok   = $u ? (new \Authentication\PasswordHasher\DefaultPasswordHasher())->check($raw, (string)$u->password) : false;
        \Cake\Log\Log::debug("[PVTEST] uid={$uid} ok=" . ($ok?'1':'0') . " raw_len=" . strlen($raw) . " hash_len=" . ( $u ? strlen((string)$u->password) : -1 ) . " hash=" . ( $u ? (string)$u->password : '(none)' ));

        //値再セット処理
            $quantityValues = (array)$this->request->getData('quantity');
            $mode = 'add'; // または 'edit'
            $tDeliOrder = $deliOrderTable->newEmptyEntity();
            $tDeliOrder->term_id = $termId;
            $tDeliOrder->user_id = $ownerId;
            // 表示用
            $mDelivery = $deliOrderTable->newEmptyEntity();
            $mDelivery->period_text        = $mTerm->start_date->i18nFormat('yyyy/M/d') . ' ～ ' . $mTerm->end_date->i18nFormat('yyyy/M/d');
            $mDelivery->add_deadline_date  = $mTerm->add_deadline_date->i18nFormat('yyyy/M/d');
            $mDelivery->order_status_text  = '受付中';
            $mDelivery->request_status_text= '未確定';

            // 再描画の取得
            $data = $this->request->getData();
            Log::debug('[REQ] ' . print_r($data, true));
            $termId  = (int)$this->request->getData('term_id');
            $ownerId  = (string)$this->request->getData('owner_id');
            $quantity  = $this->request->getData('quantity');
            // [$deliveryItems, $days] = $this->buildDeliveryMatrix($termId, $ownerId);

        $inputTime = ($dateCol instanceof FrozenDate)
        && ( $today < $dateCol
            || ($today == $dateCol && $now < $noon) );

        if (!$inputTime) {
            if ($this->request->is('ajax')) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(json_encode([
                        'ok' => false,
                        'errors' => [
                            'global' => '受付は本日12:00で終了しました。締切日を超えている為、登録できません。管理者にご確認ください。'
                        ]
                    ], JSON_UNESCAPED_UNICODE));
            }

            $this->Flash->error(
                '受付は本日12:00で終了しました。締切日を超えている為、登録できません。管理者にご確認ください。'
            );
            return $this->redirect(['action' => 'index']);
        }

        // POST 直後で
        $ownerIdForSave = $isL1

        ? (string)($this->request->getData('owner_id') ?? $ownerId)
        : $ownerId;

        // // ▼ デバッグログ出力
        // Log::debug(sprintf(
        //     '[TDeliOrder add ▶ POST] term_id=%d / owner_id=%s',
        //     $termId,
        //     $ownerId
        // ));

        // ▼ 既存検索（必要十分の絞り込み）
        $dup = $this->TDeliOrder
            ->find()
            ->select(['deli_order_id']) // 最小限の列
            ->where([
                'term_id'  => $termId,
                'user_id' => $ownerId,
            ])
            ->first();

        //     Log::debug(sprintf(
        // '[TDeliOrder add ▶ DUP-CHECK] term_id=%d / owner_id=%s / found=%s',
        //     $termId,
        //     $ownerId,
        //     $dup ? (string)$dup->deli_order_id : 'none'
        // ));

        if ($dup) {
            $this->Flash->error('同じ献立期間と施設の組み合わせは既に登録されています。既存ファイルを修正して下さい。');
            $this->set(compact('tDeliOrder','mDelivery','userName','ownerId','isL1',
                'facilityOptions','isActive','deliveryItems','days', 'mode','quantityValues'));
            return $this->render('add_edit'); 
        }

        // L2 再ガード（AJAX/同期両対応）
        if ($isL2 && $ownerIdForSave !== $loginUserId) {
            if ($this->request->is('ajax')) {
                $this->viewBuilder()->setClassName('Json');
                $this->set(['ok' => false, 'errors' => ['global' => '自分のデータのみ新規作成可能です。']]);
                $this->viewBuilder()->setOption('serialize', ['ok','errors']);
                return;
            }
            $this->Flash->error('自分のデータのみ新規作成可能です。');
            return $this->redirect(['action' => 'index']);
        }

        // 共通取得：1回だけ
        $deliOrderDtlTable = $this->fetchTable('TDeliOrderDtl');

        // ★ 期ごとの12:00ガード（新規は add_deadline ）
        if ($isL2) { // L2/4のみ制限
            $nowJst = $this->getSqlNowJst();

            // add_deadline_date の当日 12:00 を閾値にする
            $addCutoff = ($mTerm->add_deadline_date instanceof \DateTimeInterface)
                ? \DateTimeImmutable::createFromInterface($mTerm->add_deadline_date)
                : new \DateTimeImmutable((string)$mTerm->add_deadline_date, new \DateTimeZone('Asia/Tokyo'));
            $addCutoff = $addCutoff->setTime(12, 0, 0);

            \Cake\Log\Log::debug(sprintf('[ADD CUT] now=%s cutoff=%s',
                $nowJst->format('Y-m-d H:i:s'), $addCutoff->format('Y-m-d H:i:s')));

                \Cake\Log\Log::debug(sprintf(
                    '[ADD CUT] now=%s (%s) cutoff=%s (%s)',
                    $nowJst->format('Y-m-d H:i:s'),
                    $nowJst->getTimezone()->getName(),
                    $addCutoff->format('Y-m-d H:i:s'),
                    $addCutoff->getTimezone()->getName()
                ));
			
        }
        // ── 共通検証（TX開始前にやる）─────────────────────────
        $errors = $this->validateQuantities($quantityValues);
        Log::debug(print_r($errors, true));

        // 保存対象が1つでもあるか（空・0のみは弾く）
        $hasPositive = false;
        foreach ($quantityValues as $byDate) {
            foreach ($byDate as $v) {
                if ((string)$v !== '' && (int)$v > 0) { $hasPositive = true; break 2; }
            }
        }

        $isAjax = $this->request->is('ajax') || $this->request->accepts('application/json');
        $isAjaxL1 = $isL1 && $isAjax; // ← これが true のとき if ($isAjaxL1) に入る
        
        Log::debug('hdr X-Requested-With='.$this->request->getHeaderLine('X-Requested-With'));
        Log::debug('hdr Accept='.$this->request->getHeaderLine('Accept'));
        Log::debug('isAjax='.($this->request->is('ajax')?'1':'0')
                .' acceptsJson='.($this->request->accepts('application/json')?'1':'0')
                .' isL1='.($isL1?'1':'0')
                .' -> isAjaxL1='.($isAjaxL1?'1':'0'));

        // ── L1 & AJAX: JSON返却 ──────────────────────────────
        if ($isAjaxL1) {
            $this->disableAutoRender();

            if (!empty($errors)) {
                $deliveryItems = $this->request->getData();
                Log::debug('ERRORを返します。deliveryItemsの中身は'.print_r($deliveryItems, true));
                $payload = ['ok' => false, 'errors' => $errors, 'deliveryItems' => $deliveryItems];

                return $this->response->withType('json')
                    ->withStringBody(json_encode($payload, JSON_UNESCAPED_UNICODE));
            }

            if (!$hasPositive) { // or !$hasAnyInput（要件に合わせて）
                $payload = ['ok' => false, 'errors' => ['global' => '数量が未入力です。']];
                return $this->response->withType('json')
                    ->withStringBody(json_encode($payload, JSON_UNESCAPED_UNICODE));
            }

            $connection = $deliOrderTable->getConnection();
            $entities = [];

            try {
                $connection->begin();
        //throw new Exception();
                // 親
                $deliOrderEntity = $deliOrderTable->newEntity([
                    'user_id'      => $ownerIdForSave,  // ← 修正
                    'term_id'      => $termId,
                    'order_status' => 0,
                    'del_flg'      => 0,
                    'create_user'  => $loginUserId,
                    'update_user'  => $loginUserId,
                ]);
                Log::debug('テーブルにエンティティを保存してみる前');
                if (!$deliOrderTable->save($deliOrderEntity)) {
                    Log::debug('テーブルにエンティティを保存してみて、失敗したら if の中に入る');
                    $connection->rollback();

                        // 失敗理由も出す（バリデーション/DB制約）
                        \Cake\Log\Log::error('save failed: ' . print_r($deliOrderEntity->getErrors(), true));

                        $payload = [
                            'ok' => false,
                            'errors' => $deliOrderEntity->getErrors() ?: ['global' => '親データの保存に失敗しました'],
                        ];

                        // 不正UTF-8で落ちないように SUBSCRIBE を付与
                        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

                        if ($json === false) {
                            \Cake\Log\Log::error('json_encode error: ' . json_last_error_msg());
                            \Cake\Log\Log::error(print_r($payload, true));
                            $json = '{"ok":false,"errors":{"global":"JSON encode failed"}}';
                        }

                        // 4xx で返す（必要に応じて 400/422 等を選択）
                        return $this->response
                            ->withStatus(422)
                            ->withType('json')
                            ->withStringBody($json);
                    }
                $deliOrderId = (int)$deliOrderEntity->deli_order_id;

                // 明細
                foreach ($quantityValues as $deliveryId => $dateValues) {
                    foreach ($dateValues as $date => $qty) {
                        if ($qty === '' || $qty === null) continue;
                        $q = (int)$qty;
                        if ($q <= 0) continue; // 0は保存しない（仕様で変えるならここ）
                       $deliDate = \Cake\I18n\FrozenDate::createFromFormat('Y-m-d', $date);
                        if (!$deliDate) { \Cake\Log\Log::error("⛔ 日付パース失敗: $date"); continue; }
                       $entities[] = $deliOrderDtlTable->newEntity([
                            'deli_order_id' => $deliOrderId,
                            'delivery_id'   => $deliveryId,
                            'term_date'     => $deliDate,
                            'del_flg'       => 0,
                            'quantity'      => $q,
                            'keep_qty'      => $q,
                            'create_user'   => $loginUserId,
                            'update_user'   => $loginUserId,
                        ]);
                    }
                }

                if ($entities && !$deliOrderDtlTable->saveMany($entities)) {

                    $connection->rollback();

                    // すべてのエンティティのエラーを集める
                    $allErrors = [];
                    foreach ($entities as $i => $e) {
                        if ($e->getErrors()) {
                            $allErrors["row_{$i}"] = $e->getErrors();
                        }
                    }

                    // 何も無ければグローバルメッセージだけ
                    if (empty($allErrors)) {
                        $allErrors = ['global' => '明細保存に失敗しました'];
                    }

                    return $this->response->withType('json')
                        ->withStringBody(json_encode([
                            'ok'     => false,
                            'errors' => $allErrors
                        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
                }

               $connection->commit();

                    $this->Flash->success('登録しました');
                    return $this->redirect(['action' => 'index']);
                    

            } catch (Exception $e) {
                    $connection->rollback();

                    // ✅ 例外時も JSON だけ返して終わる（Flash/render禁止）
                    Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                    return $this->response->withType('json')
                        ->withStatus(500)
                        ->withStringBody(json_encode([
                            'ok' => false,
                            'errors' => ['global' => '保存に失敗しました。'],
                            'message' => $e->getMessage(), // デバッグ用なら含める
                        ], JSON_UNESCAPED_UNICODE));
                }
        }


        // ── 同期保存：検証NGならここで終了、OKならTX開始
        if (!empty($errors)) {
                Log::debug('ERRORを返します。');
                Log::debug('[REQ quantityValues] ' . print_r($quantityValues, true));
                Log::debug('[REQ isL1] ' . print_r($isL1, true));
                Log::debug('[REQ isActive] ' . print_r($isActive, true));

                 [$deliveryItems, $days] = $this->buildDeliveryMatrix($termId, $ownerId);
                
                $this->set(compact('tDeliOrder','mDelivery','userName','ownerId',
                'isL1',
                'facilityOptions','isActive','deliveryItems','days', 'mode','quantityValues'));
                $firstMsg = (string)reset($errors); // これだけでも可
                $this->Flash->error($firstMsg);

                return $this->render('add_edit');     
                // }elseif (!$hasPositive) {
        // Log::debug('2');
        //             [$deliveryItems, $days] = $this->buildDeliveryMatrix($termId, $ownerId);
        //             $this->Flash->error('食数が未入力です。');
        //                 $quantityValues = (array)$this->request->getData('quantity'); // ← フィールド名は quantity[...]
        //                 $this->set(compact('quantityValues', 'mode','deliveryItems'));
        //                 return $this->redirect(['action' => 'add', $tDeliOrder->deli_order_id]);
        } else {

            $connection = $deliOrderTable->getConnection();
            $entities = [];
            try {
                $connection->begin();
                // 親
                $deliOrderEntity = $deliOrderTable->newEntity([
                    'user_id'      => $ownerIdForSave,  // ← 修正
                    'term_id'      => $termId,
                    'order_status' => 0,
                    'del_flg'      => 0,
                    'create_user'  => $loginUserId,
                    'update_user'  => $loginUserId,
                ]);
                if (!$deliOrderTable->save($deliOrderEntity)) {
                    $this->Flash->error('親データの保存に失敗しました');
                    
                    $this->set(compact('quantityValues', 'mode'));
                    return $this->render('add_edit'); 
                }
                $deliOrderId = $deliOrderEntity->deli_order_id;
                // 明細
                foreach ($quantityValues as $deliveryId => $dateValues) {
                    foreach ($dateValues as $date => $qty) {
                        if ($qty === '' || $qty === null) continue;

                        $deliDate = \Cake\I18n\FrozenDate::createFromFormat('Y-m-d', $date);
                        if (!$deliDate) { \Cake\Log\Log::error("⛔ 日付パース失敗: $date"); continue; }
                        $entities[] = $deliOrderDtlTable->newEntity([
                            'deli_order_id' => $deliOrderId,
                            'delivery_id'   => $deliveryId,
                            'term_date'     => $deliDate,
                            'del_flg'       => 0,
                            'quantity'      => (int)$qty,
                            'keep_qty'      => (int)$qty,
                            'create_user'   => $loginUserId,
                            'update_user'   => $loginUserId,
                        ]);
                    }
                }
                if ($entities && !$deliOrderDtlTable->saveMany($entities)) {
                    $this->Flash->error('明細保存に失敗しました');
                    $this->set(compact('quantityValues', 'mode'));
                    return $this->render('add_edit'); 
                }
                $connection->commit();
                $this->Flash->success('登録しました');
                return $this->redirect(['action' => 'index']);

            } catch (Exception $e) {

                $connection->rollback();
                $this->Flash->error($e->getMessage());
               
                foreach ($entities as $i => $entity) {
                    if ($entity->hasErrors()) {
                        \Cake\Log\Log::error("❌ 明細[$i] エラー内容: " . print_r($entity->getErrors(), true));
                    }
                }
                 $this->set(compact('quantityValues', 'mode'));
                return $this->render('add_edit'); 
            }
        }

    }
        Log::debug('pre-set isActive={v}, today={t}, col={c}', [
        'v' => $isActive ? 'true' : 'false',
        't' => isset($today) ? $today->toDateString() : null,
        'c' => $dateCol?->toDateString()
        ]);

        $this->set(compact(
            'tDeliOrder',
            'mDelivery',
            'mode',
            'deliveryItems',
            'days',
            'quantityValues',
            'userName',          // ← 追加
            'ownerId',           // ← hidden用に渡しておく
            'isL1',              // ← 施設セレクトの出し分け用
            'facilityOptions',   // ← L1用の施設リスト
            'isActive'
        ));

        return $this->render('add_edit');
}

    /**
     * 数量入力の検証を行い、エラー連想配列を返す add add(ajax) editで使用
        * @param array<string,array<string,mixed>> $quantityValues [deliveryId => [Y-m-d => val]]
        * @return array<string,string> errors 例: ["quantity.12.2025-09-01" => "数量はN以上で入力してください。"]
    */
    private function validateQuantities(array $quantityValues): array
    {
        $errors = [];
        
        $systemMin = $this->fetchTable('MSystemSetting')
            ->find()
            ->select(['deli_min_chk'])
            ->where(['system_id' => 1])
            ->first();

        $upper = $systemMin ? (int)$systemMin->deli_min_chk : null;
        Log::debug('upper(min)=' . var_export($upper, true));

        foreach ($quantityValues as $deliveryId => $byDate) {
            if (!is_array($byDate)) { continue; }

            foreach ($byDate as $dateStr => $val) {
                // if ($val === '' || $val === null) {
                //     // $errors["quantity.$deliveryId.$dateStr"] = '食数を入力してください。';
                //     // continue;
                //     return true;
                // }
                if ($val === '' || $val === null) {
                    // 空欄は許可
                } elseif (!preg_match('/^\d+$/', (string)$val)) { // 負数を禁止するなら ^\d+$
                    $errors["quantity.$deliveryId.$dateStr"] = '数量は整数で入力してください。';
                    continue;
                } else {                
                    $q = (int)$val;
                    if ($q < $upper) {$errors["quantity.$deliveryId.$dateStr"] = '食数は 最低発注食 ' . $upper . '以上で入力してください。';
                    }
                }
            }
        }

        return $errors;
    }

    public function matrix()
    {
        $this->request->allowMethod(['post']);
        if (!$this->request->is('ajax')) {
            return $this->response->withStatus(400);
        }

        // 権限チェック（L1だけ許可なら）
        $perm  = $this->decidePermission((string)$this->request->getParam('controller'));
        $level = match ($perm) { 1 => 1, 2,4 => 2, 5 => 0, default => -1 };
        if ($level !== 1) {
            return $this->response->withStatus(403);
        }

        $termId         = (int)($this->request->getData('term_id') ?? 0);
        $facilityId     = (string)($this->request->getData('facility_id') ?? '');

        if ($termId <= 0 || $facilityId === '') {
            return $this->response->withStatus(422);
        }

        // 行列データ生成（オーナ=facilityIdで）
        [$deliveryItems, $days] = $this->buildDeliveryMatrix($termId, $facilityId);
        $quantityValues = (array)$this->request->getData('quantity');

        // add時は空でOK（editならサーバ側で詰めて渡す想定）
        Log::debug('[Matrix quantityValues] ' . print_r($quantityValues, true));
        $mode = 'add';

        $this->set(compact('deliveryItems', 'days', 'quantityValues', 'mode'));
        $this->viewBuilder()->disableAutoLayout();
        // テーブルだけの断片を返す
        return $this->render('/element/TDeliOrder/matrix');
    }
public function edit()
{
    Log::debug('edit開始');
    $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        // ❶ 最初に一度だけ決定
        $level = $this->currentLevel();
        $isL1  = ($level === 1);
        $isL2  = ($level === 2);

        // ② PK優先（セッションの SelectedDeliOrderId を最優先）※クエリは読まない
            $session            = $this->request->getSession();
            $selectedDeliPk     = (int)($session->read('SelectedDeliOrderId') ?? 0);

        // ③ ログイン者
            $identity      = $this->Authentication->getIdentity();
            $loginUserId   = (string)$identity?->get('user_id');
            $loginUserName = (string)$identity?->get('user_name');

        if ($selectedDeliPk > 0) {
            // PKで1件特定
            $tDeliOrder = $this->fetchTable('TDeliOrder')->find()
                    ->where(['deli_order_id' => $selectedDeliPk, 'del_flg' => 0]) 
                    ->first(); 
                    
            // 使い切り（誤再利用防止） 
            $session->delete('SelectedDeliOrderId'); 
        
            if (!$tDeliOrder) {
                $this->Flash->error('対象データが存在しません。');
                return $this->redirect(['action' => 'index']); }
            
            $termId = (int)$tDeliOrder->term_id; $ownerId = (string)$tDeliOrder->user_id; 
            
            // 認可
            if ($level === 0) {
                $this->Flash->error('閲覧専用のため編集できません。'); 
                return $this->redirect(['action' => 'index']); 
                }
                
            if ($level === 2 && $ownerId !== $loginUserId) {
                $this->Flash->error('自分のデータのみ編集可能です。'); 
                return $this->redirect(['action' => 'index']); 
                } 
                
            // 互換のためセッションにも同期（任意） 
            $session->write('SelectedTermId', $termId); 
            $session->write('SelectedOwnerId', $ownerId); 
        } else {
            
            // 旧ルート：セッションの term+owner のみで特定（クエリは読まない） 
            $termId = (int)($session->read('SelectedTermId') ?? 0); 
            $ownerId = (string)($session->read('SelectedOwnerId') ?? ''); 
            if ($termId <= 0 || $ownerId === '') { 
                Log::debug('タームID：'.$termId);
                Log::debug('ログインID：'.$ownerId);
                $this->Flash->error('選択情報が無効です。もう一度やり直してください。'); 
                return $this->redirect(['action' => 'index']); 
            }
            
            $tDeliOrder = $this->fetchTable('TDeliOrder')
                            ->find()
                            ->where(['term_id' => $termId, 'user_id' => $ownerId, 'del_flg' => 0]) 
                            ->first(); 
                            
                if (!$tDeliOrder) {
                    $this->Flash->warning('対象データが存在しないため、新規作成に切り替えます。'); 
                    $session->write('SelectedTermId', $termId); 
                    $session->write('SelectedOwnerId', $ownerId); 
                    return $this->redirect(['action' => 'add']);
            }
        }
        
        $ownerIdForSave = (string)($this->request->getData('owner_id') ?? $ownerId ?? $loginUserId);

        // ④ （GET初期表示用）期間・表示整形・明細→数量マトリクス
        $mTerm = $this->fetchTable('MTerm')->find()
            ->where(['term_id' => $termId])
            ->first();
        if (!$mTerm) {
            $this->Flash->error('指定された献立期間が存在しません');
            return $this->redirect(['action' => 'index']);
        }

        // 施設名（表示）
        if ($level === 1) {
            $userName = $this->fetchTable('MUser')->find()
                ->select(['user_name'])
                ->where(['user_id' => $tDeliOrder->user_id, 'del_flg' => 0])
                ->first()?->user_name ?? '';
        } else {
            $userName = $loginUserName;
        }
        $this->set('userName', $userName);

        // 表示用加工
        $mDelivery = $tDeliOrder;
        $mDelivery->period_text        = $mTerm->start_date->i18nFormat('yyyy/M/d') . ' ～ ' . $mTerm->end_date->i18nFormat('yyyy/M/d');
        $mDelivery->add_deadline_date  = $mTerm->add_deadline_date->i18nFormat('yyyy/M/d');
        // 12:00(JST)切替に基づく受付状態判定
            $nowJst          = $this->getSqlNowJst(); // JST 現在時刻（同一リクエスト内キャッシュ）
            $entryStart1200  = $this->addStart1200Jst($mTerm->entry_start_date ?? null, $mTerm->add_deadline_date ?? null); // ②が無ければ②-7日@12:00
            $addDeadline1200 = $this->at1200Jst($mTerm->add_deadline_date ?? null);  // ② 10/13 @12:00
            $updDeadline1200 = $this->at1200Jst($mTerm->upd_deadline_date ?? null);  // ③ 10/20 @12:00
            // 終了境界は ③優先、③が無ければ ②。両方無ければ判定不可→空文字
            $endBoundary     = $updDeadline1200 ?? $addDeadline1200;

            if ($endBoundary === null) {
                $mDelivery->order_status_text = '';
            } else {
                if ($entryStart1200 !== null && $nowJst < $entryStart1200) {
                    $mDelivery->order_status_text = '受付前';
                } elseif ($nowJst < $endBoundary) {
                    // ②〜③未満（= 10/13 12:00 〜 10/20 11:59:59）を受付中にする
                    $mDelivery->order_status_text = '受付中';
                } else {
                    // ③以降（= 10/20 12:00 以降、④含む）は受付終了
                    $mDelivery->order_status_text = '受付終了';
                }
            }

            // ログ（受付中区間は ②〜採用した終了境界）
                // Log::debug(sprintf(
                //     '[受付状態判定] now=%s | 受付中区間: %s ～ %s | add_deadline=%s | upd_deadline=%s | endBoundary=%s | 結果=%s',
                //     $nowJst?->format('Y-m-d H:i:s'),
                //     $entryStart1200?->format('Y-m-d H:i:s') ?? 'なし',
                //     $endBoundary?->format('Y-m-d H:i:s') ?? 'なし',
                //     $addDeadline1200?->format('Y-m-d H:i:s') ?? 'なし',
                //     $updDeadline1200?->format('Y-m-d H:i:s') ?? 'なし',
                //     $endBoundary?->format('Y-m-d H:i:s') ?? 'なし',
                //     $mDelivery->order_status_text
            // ));
        $mDelivery->request_status_text = ((int)$tDeliOrder->order_status === 1) ? '確定済み' : '未確定';

        Log::debug('mDelivery: ' . json_encode([
            'add_deadline_date' => $mDelivery->get('add_deadline_date'),
            'upd_deadline_text' => $mDelivery->get('upd_deadline_text'),
        ], JSON_UNESCAPED_UNICODE));

        // 明細→数量マトリクス
        $details = $this->fetchTable('TDeliOrderDtl')->find()
            ->where(['deli_order_id' => $tDeliOrder->deli_order_id])
            ->all();
        $quantityValues = [];
        foreach ($details as $d) {
            if ($d->term_date instanceof \Cake\I18n\Date || $d->term_date instanceof \Cake\I18n\FrozenDate) {
                $dateStr = $d->term_date->format('Y-m-d');
                $quantityValues[$d->delivery_id][$dateStr] = $d->quantity;
            } else {
                Log::error('❌ term_date が日付型でない: ' . print_r($d->term_date, true));
            }
        }

        //日付の活性非活性処理
            $TermCol = $this->fetchTable('MTerm')->get($termId);
            $dateCol =$TermCol->upd_deadline_date;
            $StatusCol =$tDeliOrder->order_status;
            $flag    = (int)$StatusCol; 

                // null でなければ文字列化してログ
                Log::debug('date_column={val}', [
                    'val' => $dateCol?->toDateString()
                ]);
                // null でなければ文字列化してログ
                Log::debug('date_column={val}', [
                    'val' => $dateCol?->toDateString()
                ]);
                // 今日の日付
                $today = FrozenDate::today();
                $isActive = true;
                

            if ($dateCol instanceof FrozenDate) {
                if ($today < $dateCol) {
                    Log::debug("【更新画面】今日({today})はカラム({col})より前です", [
                        'today' => $today->toDateString(),
                        'col'   => $dateCol->toDateString(),
                    ]);
                    $isActive = true;
                } elseif ($today > $dateCol) {
                    Log::debug("【更新画面】今日({today})はカラム({col})より後です", [
                        'today' => $today->toDateString(),
                        'col'   => $dateCol->toDateString(),
                    ]);
                    $isActive = false;
                } else {
                    Log::debug("【更新画面】今日({today})とカラム({col})は同じ日です");
                    // 同じ日
                    $now = FrozenTime::now();
                    $noon = new FrozenTime('today 12:00:00');

                    if ($now < $noon) {
                        Log::debug("【更新画面】今日({today})とカラム({col})は同じ日、かつ現在は正午・前です", [
                            'today' => $today->toDateString(),
                            'col'   => $dateCol->toDateString(),
                        ]);
                        $isActive = true;
                    } else {
                        Log::debug("【更新画面】今日({today})とカラム({col})は同じ日、かつ現在は正午後です", [
                            'today' => $today->toDateString(),
                            'col'   => $dateCol->toDateString(),
                        ]);
                        $isActive = false;
                    }
                }
        }
        if ((int)$flag === 1) {
                    Log::debug(
                        '【更新画面】edit: フラグ=1のため編集不可 id={id}, flag={flag}',
                        ['id' => $termId, 'flag' => $StatusCol]
                    );
                    // 必要ならここでガード
                    $isActive = false;
                    // return $this->redirect(['action' => 'index']);
                } else {
                    Log::debug(
                        '【更新画面】edit: フラグ=0（許可） id={id}, flag={flag}',
                        ['id' => $termId, 'flag' => $StatusCol]
                    );
                }

        // ⑥ 表示セット（ownerId を渡す）
            [$deliveryItems, $days] = $this->buildDeliveryMatrix($termId, $ownerId);

    // ⑤ POST更新（PKで再特定して防御すると尚良し）
    if ($this->request->is(['post', 'put', 'patch'])) {
        $nowJst = $this->getSqlNowJst();
        $quantityValues = (array)$this->request->getData('quantity');
        $this->set(compact('quantityValues'));
        $loginUser = $loginUserName;
        $mode = 'edit';
        $postedId = (int)($this->request->getData('deli_order_id') ?? 0);

        // ▼ MTerm テーブルから編集期限（edit_deadline）を取得
        $MTerm = $this->fetchTable('MTerm');

        //更新デッドラインと比較処理
            $TermRecord = $MTerm
                ->find()
                ->select(['upd_deadline_date'])
                ->where(['term_id' => $termId])
                ->first();

            // if ($TermRecord) {
                //     Log::debug(sprintf(
                //         '[MTerm ▶ upd_deadline_date] term_id=%d / upd_deadline_date=%s',
                //         $termId,
                //         (string)$TermRecord->upd_deadline_date
                //     ));
                // } else {
                //     Log::debug(sprintf('[MTerm ▶ upd_deadline_date] term_id=%d / 該当なし', $termId));
            // }

            // ▼ upd_deadline_date に 12時を加える
            if ($TermRecord && $TermRecord->upd_deadline_date) {

                // ▼ upd_deadline_date に 12時を足して現在時刻と比較
            if ($TermRecord && $TermRecord->upd_deadline_date) {

                // upd_deadline_date（日付）＋12時
                $deadline = new FrozenTime($TermRecord->upd_deadline_date->format('Y-m-d') . ' 12:00:00');
                $now = FrozenTime::now();

                // Log::debug(sprintf(
                    //     '[期限比較] 現在=%s / 締切=%s',
                    //     $now->format('Y-m-d H:i:s'),
                    //     $deadline->format('Y-m-d H:i:s')
                // ));

                if ($now < $deadline) {
                    // 期限前
                    Log::debug('[期限比較] 編集可能（12時前）');
                } else {
                    // 期限後
                    Log::debug('[期限比較] 編集期限を過ぎています');
                    $this->Flash->error('締切日を超えている為、更新できません。管理者にご確認ください。');
                    $this->set(compact(
                                    'quantityValues',
                                    'tDeliOrder',
                                    'mDelivery',
                                    'mode',
                                    'deliveryItems',
                                    'days',
                                    'quantityValues',
                                    'loginUser',
                                    'isActive'
                                ));

                // リダイレクトしない。テンプレートを直接描画
                 return $this->render('add_edit');
                }
            }
        }

            if ($isL2) { // L2/4のみ制限

                // upd_deadline_date の当日 12:00 を閾値にする
                $updCutoff = ($mTerm->upd_deadline_date instanceof \DateTimeInterface)
                    ? \DateTimeImmutable::createFromInterface($mTerm->upd_deadline_date)
                    : new \DateTimeImmutable((string)$mTerm->upd_deadline_date, new \DateTimeZone('Asia/Tokyo'));
                $updCutoff = $updCutoff->setTime(12, 0, 0);

                Log::debug(sprintf('[EDIT CUT] now=%s cutoff=%s',
                    $nowJst->format('Y-m-d H:i:s'), $updCutoff->format('Y-m-d H:i:s')));

                if ($nowJst >= $updCutoff) {
                    if ($this->request->is('ajax')) {
                        $this->viewBuilder()->setClassName('Json');
                        $this->set(['ok' => false, 'errors' => ['global' => '受付は本日12:00で終了しました。締切日を超えている為、登録できません。管理者にご確認ください。']]);
                        $this->viewBuilder()->setOption('serialize', ['ok','errors']);
                        return;
                    }
                    $this->Flash->error('受付は本日12:00で終了しました。締切日を超えている為、登録できません。管理者にご確認ください。');
                    return $this->redirect(['action' => 'index']);
                }
            }


            // --- PK再特定＆認可（そのまま） -------------------------
            if ($postedId > 0 && $postedId !== (int)$tDeliOrder->deli_order_id) {
                $t = $this->fetchTable('TDeliOrder')->find()
                    ->where(['deli_order_id' => $postedId, 'del_flg' => 0])
                    ->first();
                if (!$t) {
                    $this->Flash->error('対象データが存在しません。');
                    $this->set(compact('quantityValues', 'mode','deliveryItems'));
                    return $this->render('add_edit'); 
                }
                if ($level === 0 || ($level === 2 && (string)$t->user_id !== $loginUserId)) {
                    $this->Flash->error('編集権限がありません。');
                    $this->set(compact('quantityValues', 'mode','deliveryItems'));
                    return $this->render('add_edit'); 
                }
                $tDeliOrder = $t;
                $termId  = (int)$tDeliOrder->term_id;
                $ownerId = (string)$tDeliOrder->user_id;
            }

            Log::debug("🛠️ edit() 更新処理開始");
            $deliOrderTable    = $this->fetchTable('TDeliOrder');
            $deliOrderDtlTable = $this->fetchTable('TDeliOrderDtl');
            $connection        = $deliOrderTable->getConnection();

            // === ここから【TX前の共通検証】を追加 =======================
            // 1) 基本検証
            Log::debug('食数:' . print_r($quantityValues, true));
            $errors = $this->validateQuantities($quantityValues);

            // 2) 保存対象があるか（空 or 0のみ は弾く）
            $hasPositive = false;

            foreach ($quantityValues as $byDate) {
                foreach ($byDate as $v) {
                    // 空じゃなく、数値化して 1 以上なら true
                    if ((string)$v !== '' && (int)$v > 0) {
                        $hasPositive = true;
                        break 2; // 見つかった時点で2重ループ脱出
                    }
                }
            }
            // debug($deliveryItems);
            // debug($days);

            if (!empty($errors)) {
                // reset() は配列が前提
                $firstError = is_array($errors) ? reset($errors) : (string)$errors;
                $this->Flash->error($firstError);

                $this->set(compact(
                                    'quantityValues',
                                    'tDeliOrder',
                                    'mDelivery',
                                    'mode',
                                    'deliveryItems',
                                    'days',
                                    'quantityValues',
                                    'loginUser',
                                    'isActive'
                                ));

                // リダイレクトしない。テンプレートを直接描画
                 return $this->render('add_edit');
            } elseif (!$hasPositive) {
                Log::debug('1');
                $this->Flash->error('食数が未入力です。');
                $quantityValues = (array)$this->request->getData('quantity');
                $this->set(compact('quantityValues', 'mode','deliveryItems'));
                return $this->redirect(['action' => 'edit', $tDeliOrder->deli_order_id]);
            } else {
            // === ここまで【TX前の共通検証】 ============================

                try {
                    $connection->begin();
    //throw new Exception();
                    // 親更新（term_id/user_id はPOST信用せず、親の値を維持）
                    $allowed = $this->request->getData();
                    unset($allowed['term_id'], $allowed['user_id'], $allowed['deli_order_id']);
                    $tDeliOrder = $deliOrderTable->patchEntity($tDeliOrder, $allowed);
                    $tDeliOrder->update_user = $loginUserId;
                    if (!$deliOrderTable->save($tDeliOrder)) {
                        $this->Flash->error('親データの更新に失敗しました');
                        $this->set(compact('quantityValues', 'mode'));
                        return $this->render('add_edit'); 
                    }

                    // 既存明細の索引化
                    $existingDetails = $deliOrderDtlTable->find()
                        ->where(['deli_order_id' => $tDeliOrder->deli_order_id])
                        ->all()
                        ->indexBy(function ($row) {
                            return $row->delivery_id . '|' . $row->term_date->format('Y-m-d');
                        })
                        ->toArray();

                    $newKeys = [];

                    // ±幅の設定値
                    $sys = $this->fetchTable('MSystemSetting')->find()
                        ->select(['deli_chg_chk'])
                        ->first();
                    if (!$sys || $sys->deli_chg_chk === null) {
                        $this->Flash->error('システム設定 deli_chg_chk が未設定です。');
                        $this->set(compact('quantityValues', 'mode','deliveryItems'));
                        return $this->redirect(['action' => 'edit', $tDeliOrder->deli_order_id]);
                    }
                    $deliMinChk = (int)$sys->deli_chg_chk;

                    // ===== 明細の差分反映（※下限20チェックはここではしない※） =====
                    foreach ($quantityValues as $deliveryId => $dateValues) {
                        foreach ($dateValues as $date => $qty) {
                                if ($qty === '' || $qty === null) continue;

                                $newQty = (int)$qty;
                                if ($newQty <= 0) continue; // 0は保存対象外（下限20は事前検証済み）

                                $deliDate = \Cake\I18n\FrozenDate::createFromFormat('Y-m-d', $date);
                                if (!$deliDate) {
                                    Log::error("⛔ 日付パース失敗: $date");
                                    continue;
                                }

                                $key = $deliveryId . '|' . $deliDate->format('Y-m-d');
                                $newKeys[] = $key; // ★ 追加はこの1回だけ

                                if (isset($existingDetails[$key])) {
                                    // 既存行：基準は現在DBにある quantity
                                    $detail = $existingDetails[$key];

                                    // 基準数量（null/''なら基準なしとしてチェックをスキップ）
                                    $base = (isset($detail->quantity) && $detail->quantity !== '' && $detail->quantity !== null)
                                        ? (int)$detail->quantity
                                        : null;

                                    // ±deli_chg_chk チェック（基準がある時のみ）
                                    if ($base !== null && abs($newQty - $base) > $deliMinChk) {
                                        $this->Flash->error(
                                            // "食数変更は追加時{$base}に対して±{$deliMinChk}までです【配送ID: {$deliveryId}, 日付: {$deliDate->format('Y-m-d')}、新: {$newQty}）"
                                            "食数が変更可能増減数を超えています。"
                                        );
                                        // $this->set(compact('quantityValues', 'mode','deliveryItems'));
                                        $this->set(compact('quantityValues',
                                                            'tDeliOrder',
                                                            'mDelivery',
                                                            'mode',
                                                            'deliveryItems',
                                                            'days',
                                                            'quantityValues',
                                                            'loginUser',
                                                            'isActive'));
                                        // return $this->redirect(['action' => 'edit', $tDeliOrder->deli_order_id]); // ← 明示的に戻す
                                        // リダイレクトしない。テンプレートを直接描画
                                         return $this->render('add_edit');
                                    }

                                    // 更新（patchEntity 経由）※ base の取得は patch 前に済ませる
                                    $detail = $deliOrderDtlTable->patchEntity($detail, [
                                        'quantity'    => $newQty,
                                        'update_user' => $loginUserId,
                                    ]);
                                    if (!$deliOrderDtlTable->save($detail)) {
                                        Log::error('❌ 明細更新エラー: ' . print_r($detail->getErrors(), true));
                                        $this->Flash->error('明細の更新に失敗しました');
                                        // return $this->redirect(['action' => 'edit', $tDeliOrder->deli_order_id]);
                                        return $this->render('add_edit');
                                    }

                                } else {
                                    // 新規行（±幅チェックは不要、下限20は事前検証済み）
                                    $entity = $deliOrderDtlTable->newEntity([
                                        'deli_order_id' => $tDeliOrder->deli_order_id,
                                        'delivery_id'   => $deliveryId,
                                        'term_date'     => $deliDate,
                                        'quantity'      => $newQty,
                                        'del_flg'       => 0,
                                        'create_user'   => $loginUserId,
                                        'update_user'   => $loginUserId,
                                    ]);
                                    if (!$deliOrderDtlTable->save($entity)) {
                                        Log::error('❌ 明細追加エラー: ' . print_r($entity->getErrors(), true));
                                        $this->Flash->error('明細の追加に失敗しました');
                                        $this->set(compact('quantityValues', 'mode'));
                                        return $this->render('add_edit'); 
                                    }
                                }
                        }
                    }

                    // 入力が消された既存分を削除
                    foreach ($existingDetails as $key => $entity) {
                        if (!in_array($key, $newKeys, true)) {
                            if (!$deliOrderDtlTable->delete($entity)) {
                                $this->Flash->error('明細の削除に失敗しました');
                                $this->set(compact('quantityValues', 'mode'));
                                return $this->render('add_edit'); 
                            }
                        }
                    }

                    $connection->commit();
                    $this->Flash->success('更新しました');
                    return $this->redirect(['action' => 'index']);

    } catch (Exception $e) {
        $connection->rollback();
        
        $this->Flash->error('システムエラーです。更新に失敗しました。');
    }
    }
    }

        
        $loginUser = $loginUserName;
        $mode = 'edit';

        Log::debug('2回目=mDelivery: ' . json_encode([
            'add_deadline_date' => $mDelivery->get('add_deadline_date'),
            'upd_deadline_text' => $mDelivery->get('upd_deadline_text'),
        ], JSON_UNESCAPED_UNICODE));

        // 1) エンティティ $Ａ の中身
        Log::debug('entity A: ' . json_encode($mDelivery?->toArray(), JSON_UNESCAPED_UNICODE));

        // 2) 直近のPOSTデータ（フォーム送信後はこっちが優先される）
        Log::debug('request data: ' . json_encode($this->request->getData(), JSON_UNESCAPED_UNICODE));

        // ピンポイント
        Log::debug('entity add_deadline_date=' . json_encode($mDelivery->get('add_deadline_date'), JSON_UNESCAPED_UNICODE));
        Log::debug('data   add_deadline_date=' . json_encode($this->request->getData('add_deadline_date'), JSON_UNESCAPED_UNICODE));
        Log::debug('entity upd_deadline_text=' . json_encode($mDelivery->get('upd_deadline_text'), JSON_UNESCAPED_UNICODE));
        Log::debug('data   upd_deadline_text=' . json_encode($this->request->getData('upd_deadline_text'), JSON_UNESCAPED_UNICODE));


        $this->set(compact(
            'tDeliOrder',
            'mDelivery',
            'mode',
            'deliveryItems',
            'days',
            'quantityValues',
            'loginUser',
            'isActive'
        ));
       
    
     return $this->render('add_edit');
}

public function export()
{
    // 権限・ビューア情報（index と同じ基準）
    $perm = $this->decidePermission((string)$this->request->getParam('controller'));
    if ($perm < 0) {
        throw new \Cake\Http\Exception\ForbiddenException('このアカウントでは利用できません。');
    }
    $level = match ($perm) { 1 => 1, 2,4 => 2, 5 => 0, default => -1 };

    // $this->request->allowMethod(['get', 'post']);
    $query = $this->request->getQueryParams();
    Log::debug('[DST 1659] export GET query=' . json_encode($query, JSON_UNESCAPED_UNICODE));
    $conditions = [];

    $mUserTable = $this->fetchTable('MUser');
    $users = $mUserTable->find('list', 
            keyField : 'user_id',
            valueField : 'user_name'
        )->where([
            'del_flg' => 0,
            'use_service_id IN' => [2, 4]  // ← IN を明示
        ])->toArray();

    $filterUserId = $query['user_id'] ?? null;

    if (!empty($query['entry_start_date_from'])) {
        $conditions['entry_start_date >='] = $query['entry_start_date_from'];
    }
            if (!empty($query['entry_start_date_to'])) {
                $conditions['entry_start_date <='] = $query['entry_start_date_to'];
            }
        if (!empty($query['add_deadline_date_from'])) {
            $conditions['add_deadline_date >='] = $query['add_deadline_date_from'];
        }
                if (!empty($query['add_deadline_date_to'])) {
                    $conditions['add_deadline_date <='] = $query['add_deadline_date_to'];
                }
        if (!empty($query['create_date_from'])) {
            $conditions['TDeliOrder.create_date >='] = $query['create_date_from'];
        }
                if (!empty($query['create_date_to'])) {
                    $conditions['TDeliOrder.create_date <='] = $query['create_date_to'];
                }
        if (!empty($query['update_date_from'])) {
            $conditions['TDeliOrder.update_date >='] = $query['update_date_from'];
        }
                if (!empty($query['update_date_to'])) {
                    $conditions['TDeliOrder.update_date <='] = $query['update_date_to'];
                }
        // if (isset($query['order_status']) && $query['order_status'] !== '') {
        //     $conditions['order_status'] = $query['order_status'];
        // }
        if (!empty($query['user_id'])) {
            $conditions['user_id'] = $query['user_id'];
        }
    if (isset($query['confirm_status']) && $query['confirm_status'] !== '') {
        $conditions['order_status'] = $query['confirm_status'];
    }
    $queryParams = $this->request->getQuery();
    // // 303で来た場合はGETクエリから取得
    $from = $this->request->getQuery('entry_start_date_from');
    $to   = $this->request->getQuery('entry_start_date_to');
    Log::debug('1714 entry_start_date_from: ' . print_r($from, true));
    Log::debug('1715 queryParams内容: ' . print_r($queryParams, true));

    $identity    = $this->getRequest()->getAttribute('identity');
    $viewerId    = (string)($identity?->get('user_id') ?? '');
    $serviceId   = (int)($identity?->get('use_service_id') ?? 0);
    $dispUserIds = (array)($this->getRequest()->getAttribute('disp_user_ids') ?? []);

    $orders = $this->TDeliOrder->find()
                    ->contain(['MTerm'])
                    ->where($conditions); 

        //★ Log::debug('【post前】型: ' . gettype($orders));

        // // クラス確認（オブジェクトの場合）
        // if (is_object($orders)) {
        //     Log::debug('【post前】クラス: ' . get_class($orders));
        // }

        // // 配列風に見たいとき
        // Log::debug('【post前】中身: ' . print_r($orders, true)); 

    $count = $orders->count();
    Log::debug("B件数: {$count}");
    // 入力（POST/GET）
    $data   = $this->request->is('post') ? $this->request->getData() : $this->request->getQueryParams();
    $action = (string)($data['action'] ?? '');

    $this->set(compact('orders', 'count', 'users'));
    
    Log::debug('アクション分岐前');
    if ($this->request->is('post')) {
        Log::debug('ｅｘｐｏｒｔアクション');

        $conditions = [];
        $query = $this->request->getData(); // ← これOK

        $filterUserId = $query['user_id'] ?? null;

        if (!empty($query['entry_start_date_from'])) {
            $conditions['start_date >='] = $query['entry_start_date_from'];
        }
                if (!empty($query['entry_start_date_to'])) {
                    $conditions['start_date <='] = $query['entry_start_date_to'];
                }
            if (!empty($query['add_deadline_date_from'])) {
                $conditions['add_deadline_date >='] = $query['add_deadline_date_from'];
            }
                    if (!empty($query['add_deadline_date_to'])) {
                        $conditions['add_deadline_date <='] = $query['add_deadline_date_to'];
                    }
            if (!empty($query['create_date_from'])) {
                $conditions['TDeliOrder.create_date >='] = $query['create_date_from'];
            }
                    if (!empty($query['create_date_to'])) {
                        $conditions['TDeliOrder.create_date <='] = $query['create_date_to'];
                    }
            if (!empty($query['update_date_from'])) {
                $conditions['TDeliOrder.update_date >='] = $query['update_date_from'];
            }
                    if (!empty($query['update_date_to'])) {
                        $conditions['TDeliOrder.update_date <='] = $query['update_date_to'];
                    }
            if (!empty($query['user_id'])) {
                $conditions['user_id'] = $query['user_id'];
            }
        if (isset($query['confirm_status']) && $query['confirm_status'] !== '') {
            $conditions['order_status'] = $query['confirm_status'];
        }

        $q = $this->TDeliOrder->find()
                    ->contain(['MTerm'])
                    ->where($conditions)
                    ->contain(['TDeliOrderDtl']);

        $orders = $q->all();
        

        $rows = $orders->toArray();

            // Log::debug('型: ' . gettype($rows));
            // // クラス確認（オブジェクトの場合）
            // if (is_object($rows)) {
            //     Log::debug('クラス: ' . get_class($rows));
            // }
            // // 配列風に見たいとき
            // Log::debug('中身: ' . print_r($rows, true));   

            // Log::debug('型チェック: is_array=' . (is_array($orders) ? 'true' : 'false')
            //     . ' gettype=' . gettype($orders)
            //     . ' class=' . (is_object($orders) ? get_class($orders) : '(not object)')
            //     . ' 値=' . print_r($orders, true));

            // 表示名用のマップを最小限だけ作る（パフォーマンス配慮）
        $userIds = array_values(array_unique(array_map(fn($o) => (string)$o->user_id, $rows)));
        
        $userNameMap = [];
        if (!empty($userIds)) {
            $userNameMap = $this->fetchTable('MUser')
                ->find('list', keyField: 'user_id', valueField: 'user_name')
                ->where(['del_flg' => 0, 'user_id IN' => $userIds])
                ->toArray();
        }
        $deliveryIds = [];
        foreach ($orders as $o) {
            foreach ($o->t_deli_order_dtl as $dtl) {
                $deliveryIds[] = (int)$dtl->delivery_id;
            }
        }

        $deliveryIds = [];
    foreach ($orders as $o) {
        foreach ($o->t_deli_order_dtl as $dtl) {
            $deliveryIds[] = (int)$dtl->delivery_id;
        }
    }

        $deliveryIds = array_values(array_unique($deliveryIds));
        $deliveryNameMap = empty($deliveryIds)
            ? []
            : $this->fetchTable('MDelivery')
                ->find('list', keyField: 'delivery_id', valueField: 'delivery_name')
                ->where(['del_flg' => 0, 'delivery_id IN' => $deliveryIds])
                ->toArray();

        // 2) ユーザーごとの配食パターンIDを辞書化（user_id => use_pattern_id）
            $userPatternIdMap = [];
            if (!empty($userIds)) {
                $userPatternIdMap = $this->fetchTable('MUser')
                    ->find('list', keyField: 'user_id', valueField: 'use_pattern_id')
                    ->where(['del_flg' => 0, 'user_id IN' => $userIds])
                    ->toArray();
            }


            // 3) パターン名辞書（pattern_id => pattern_name）
            //    ※テーブル・カラム名はプロジェクト実名に合わせて修正
            $patternIds = array_values(array_unique(array_filter(
                array_map(fn($v) => (string)$v, $userPatternIdMap), // 型を文字列に統一
            )));
            $deliveryPatternNameMap = [];
            if (!empty($patternIds)) {
                $deliveryPatternNameMap = $this->fetchTable('MDeliveryPattern')
                    ->find('list', keyField: 'use_pattern_id', valueField: 'delivery_pattern_name')
                    ->where(['del_flg' => 0, 'use_pattern_id IN' => $patternIds])
                    ->toArray();
            }

        // CSV 生成
        // $fileName = $this->request->getData('export_file_name');
        $fileName = $this->request->getData('export_file_name');
        Log::debug('1968：'.$fileName);

        // 入力が空なら自動生成
        if (empty($fileName)) {
            $fileName = date('Ymd') . '.csv';
        } else {
            // 入力があって、末尾が .csv で終わっていなければ追加
            if (!preg_match('/\.csv$/i', $fileName)) {
                $fileName .= '.csv';
            }
        }

        try {
            $csv = 
            // 献立期間開始日,献立期間終了日,
            // 献立ID,
            "献立日,ユーザーID,配食商品名,配食パターン名称,数量,発注状態\n";
            $MDelivery = $this->fetchTable('MDelivery');
            $countRecords = 0;
            
            foreach ($orders as $order) {
                $uName = $userNameMap[(string)$order->user_id] ?? (string)$order->user_id;

                foreach ($order->t_deli_order_dtl as $dtl) {
                    $deliveryID   = (string)$dtl->delivery_id;  // 明細から
                    $deliName     = $deliveryNameMap[(int)$dtl->delivery_id] ?? null; // ←コレでOK
                    $countRecords++;

                    // Log::debug('delivery_id in detail: ' . var_export($dtl->delivery_id, true));
                    // Log::debug('deliveryID after cast: ' . var_export($deliveryID, true));

                    $patternID   = (string)($userPatternIdMap[(string)$order->user_id] ?? '');
                    $patternName = (string)($deliveryPatternNameMap[$patternID] ?? '');

                    $csv .= implode(',', array_map(
                    fn($v) => '"' . (string)$v . '"',
                    [
                        // $order->m_term?->start_date?->format('Y/m/d') ?? '',
                        // $order->m_term?->end_date?->format('Y/m/d') ?? '',
                        // $order->m_term?->term_id,
                        
                        $dtl->term_date?->format('Y/m/d') ?? '',        // ← 献立日
                        (string)$order->user_id,
                        // $deliveryID,    
                        // $patternName = (string)($deliveryPatternNameMap[$patternID] ?? ''),

                        $deliName ?? '',
                        $patternName, 
                        (int)$dtl->quantity,
                        ((int)($order->order_status ?? 0) === 1) ? '確定' : '未確定',
                        ]
                    )) . ",\n";
                }
            }
            // 件数行を追加
            // $csv .= "\"合計件数\",\"{$countRecords}\"\n";
            Log::debug('レコード数：' . $countRecords);

            // UTF-8 BOM
            $bom = "\xEF\xBB\xBF";
            return $this->response
                ->withType('csv')
                ->withDownload($fileName)
                ->withStringBody($bom . $csv);

        } catch (\Throwable $e) {
            $this->Flash->error('書出しに失敗しました：' . $e->getMessage());
            return $this->redirect(['action' => 'export']);
        }

    }
}
    //一覧構築
        private function composeIndexViewData(array $queryParams, string $viewerId, int $level, array $dispUserIds): array
        {
            // 1) レベル別 A×B を一括取得（A=MTerm基点）
            [$terms, $byTerm] = $this->fetchForIndex($queryParams, $viewerId, $level, $dispUserIds);

            // 配列の中身をログに出す
            Log::debug('📌 配列チェック: ' . print_r($queryParams, true));

            // 2) ユーザー名Map（表示用）※ byTerm から収集
            $needUserIds = [];
            foreach ($byTerm as $list) {
                foreach ($list as $r) {
                    $uid = (string)$r->user_id;
                    if ($uid !== '') $needUserIds[] = $uid;
                }
            }
            if ($level === 2 && $viewerId !== '') $needUserIds[] = $viewerId;
            $needUserIds = array_values(array_unique($needUserIds));
            $userNameMap = $this->loadUserNameMap($needUserIds);

            // 5) 行生成（仕様反映）
            $rows = $this->makeRows($terms, $byTerm, $level, $viewerId, $userNameMap, $dispUserIds);

            // ★ 初期ソート順：start_date DESC → end_date DESC → order_status → confirm_status → deli_order_id
            usort($rows, function ($a, $b) {
                // 開始日（新しい日付が先）
                $cmp = strcmp((string)$b->start_date, (string)$a->start_date);
                if ($cmp !== 0) return $cmp;

                // 終了日（新しい日付が先）
                $cmp = strcmp((string)$b->end_date, (string)$a->end_date);
                if ($cmp !== 0) return $cmp;

                // 発注状態（TDeli=0/1、Placeholder=null は最後に来るよう 2 扱い）
                $osA = is_null($a->order_status) ? 2 : (int)$a->order_status;
                $osB = is_null($b->order_status) ? 2 : (int)$b->order_status;
                if ($osA !== $osB) return $osA <=> $osB;

                // 確定状況
                $isATDeli = (($a->source ?? '') === 'TDeli');
                $isBTDeli = (($b->source ?? '') === 'TDeli');
                $csA = $isATDeli ? ((int)($a->order_status ?? 0) === 1 ? 1 : 0) : -1;
                $csB = $isBTDeli ? ((int)($b->order_status ?? 0) === 1 ? 1 : 0) : -1;
                if ($csA !== $csB) return $csA <=> $csB;

                // 発注ID（null は最後）
                $idA = isset($a->deli_order_id) ? (int)$a->deli_order_id : PHP_INT_MAX;
                $idB = isset($b->deli_order_id) ? (int)$b->deli_order_id : PHP_INT_MAX;
                return $idA <=> $idB;
            });

            // 6) セレクト用ユーザー候補（サービスで絞る／サービス5はdisp_user_idsで制限）
            $identity  = $this->getRequest()->getAttribute('identity');
            $serviceId = (int)($identity?->get('use_service_id') ?? 0);

            if ($level === 1) {
                // 管理（サービス1）：サービス2・4の全ユーザーを候補に
                $users = $this->loadUsersForSelectByServices([2, 4]);

            } elseif ($level === 0) {
                // 閲覧(サービス5想定): 閲覧許可ID × サービス2/4のみ
                $users = $this->buildUserSelectOptions($serviceId, $dispUserIds, [2, 4]);

            } else {
                $users = [];
            }

            // 7) ページ活性フラグ
            $pageFlags = $this->computePageFlags($rows);
            
            return [$rows, $users, $pageFlags];
        }

        private function fetchForIndex(array $queryParams, string $viewerId, int $level, array $dispUserIds): array
        {
            // --- A: 期間（常時3週上限＋任意の期間フィルタ）---
            $MTerm = $this->fetchTable('MTerm');

            $today = Date::now();
            $to    = $today->addWeeks(3);

            $qTerm = $MTerm->find()
                ->select(['term_id','start_date','end_date','entry_start_date','add_deadline_date','upd_deadline_date'])
                ->where(['del_flg' => '0'])
                ->andWhere(function ($exp) use ($to) {
                    return $exp->lte('start_date', $to); // start_date <= 今日+3週
                })
                ->order(['start_date' => 'ASC']);

            // 任意の期間フィルタ（存在時のみ）
            $this->applyTermDateFilters($qTerm, $queryParams);

            $terms = $qTerm->all()->toList(); // array<MTerm>
            if (empty($terms)) {
                return [[], []];
            }

            // --- B: TDeli（Aのterm_idに属する行だけ、レベル別に絞る）---
            $termIds = array_map(fn($t) => (int)$t->term_id, $terms);

            $TDeli = $this->fetchTable('TDeliOrder');
            $qDeli = $TDeli->find()
                ->select(['deli_order_id','term_id','user_id','order_status','create_user','update_user','create_date','update_date'])
                ->where(['del_flg' => '0', 'term_id IN' => $termIds]);

            // レベル別ユーザー絞り込み
            if ($level === 2) {
                if ($viewerId !== '') {
                    $qDeli->andWhere(['user_id' => $viewerId]);
                } else {
                    $qDeli->andWhere(['1 = 0']); // viewer 不明なら空
                }
            } elseif ($level === 0) {
                if (!empty($dispUserIds)) {
                    $qDeli->andWhere(['user_id IN' => $dispUserIds]);
                } else {
                    $qDeli->andWhere(['1 = 0']); // 許可IDなしなら空
                }
            }
            $deliRows = $qDeli->all()->toList(); // array<TDeliOrder>

            // term_id => TDeli[]
            $byTerm = [];
            foreach ($deliRows as $r) {
                $byTerm[(int)$r->term_id][] = $r;
            }

            return [$terms, $byTerm];
        }

        private function makeRows(array $terms, array $byTerm, int $level, string $viewerId, array $userNameMap, array $dispUserIds): array
        {
            Log::debug("makeRows:アクション開始");
            // ★SQL Server基準の“今日”(JST)に統一
            $nowJst = $this->getSqlNowJst(); // 先に追加済みのヘルパー
            $today  = \DateTimeImmutable::createFromFormat('Y-m-d', $nowJst->format('Y-m-d'), new \DateTimeZone('Asia/Tokyo'));

            $rows = [];

            foreach ($terms as $t) {
                $termId = (int)$t->term_id;
                $start  = $t->start_date;
                $end    = $t->end_date;
                $dead   = $t->add_deadline_date;
                

                [$periodPhase, $periodLabel] = $this->computeReceptionPhase(
                    $today, $t->entry_start_date, $t->add_deadline_date, $t->upd_deadline_date
                );
                // 既存行は期フェーズをそのまま使う
                $existingPhaseCode  = $periodPhase;
                $existingReception  = $periodLabel;
                $existing = $byTerm[$termId] ?? [];

                if ($level === 1) {
                    // 管理：既存TDeliを全展開＋プレースホルダ1行
                    foreach ($existing as $r) {
                        $isConfirmed = ((int)$r->order_status === 1);
                        // 管理は制限なしだが、フラグは一応付与しておく（viewのdisabledは効かない想定）
                    [$can, $why] = $this->computeRowTouchFlags(
                            $existingPhaseCode,
                            false,                                 // isPlaceholder
                            ((int)$r->order_status === 1),         // isConfirmed
                            $level,
                            $t->add_deadline_date,
                            $t->upd_deadline_date,
                            $t->entry_start_date
                        );

                        $rows[] = (object)[
                            'term_id'            => $termId,
                            'start_date'         => $start,
                            'end_date'           => $end,
                            'entry_start_date'   => $t->entry_start_date,
                            'add_deadline_date'  => $dead,
                            'user_id'            => (string)$r->user_id,
                            'display_user_id'    => (string)$r->user_id,
                            'display_user_name'  => $userNameMap[(string)$r->user_id] ?? (string)$r->user_id,
                            'source'             => 'TDeli',
                            'order_status'       => (int)$r->order_status,
                            'order_status_label' => '登録済', // ★ここを固定表示に
                            'reception_status' => $existingReception,
                            'reception_phase'  => $existingPhaseCode,
                            'confirm_status'     => ((int)$r->order_status === 1 ? '確定' : '未確定'),
                            'create_date'        => $r->create_date,
                            'update_date'        => $r->update_date,

                            'deli_order_id'      => (int)$r->deli_order_id,
                            'can_select'         => $can,
                            'disabled_reason'    => $why,
                        ];
                    }
                    // ★ 未登録行は「行用フェーズ」に補正して表示する
                    [$phPhaseCode, $phReception] = $this->resolveRowPhaseForPlaceholder(
                        $periodPhase,
                        $this->getSqlNowJst(),
                        $t->add_deadline_date,
                        $t->entry_start_date
                    );
                    // プレースホルダ
                    $ph = $this->makePlaceholder($termId, $start, $end, $dead, '新規登録', $phReception, $phPhaseCode);
                    $ph->entry_start_date = $t->entry_start_date;
                    // isPlaceholder=true, isConfirmed=false で判定
                    [$can, $why] = $this->computeRowTouchFlags(
                        $phPhaseCode,
                        true,                                  // isPlaceholder
                        false,                                 // isConfirmed
                        $level,
                        $t->add_deadline_date,
                        $t->upd_deadline_date,
                        $t->entry_start_date
                    );
                    $ph->can_select      = $can;
                    $ph->disabled_reason = $why;
                    $rows[] = $ph;

                } elseif ($level === 2) {
                    // 更新（ownerは無視）：該当TDeli全部＋該当なしならプレースホルダ1行
                    if (!empty($existing)) {
                        foreach ($existing as $r) {
                            $isConfirmed = ((int)$r->order_status === 1);
                            [$can, $why] = $this->computeRowTouchFlags(
                                $existingPhaseCode,
                                false,                                // isPlaceholder
                                ((int)$r->order_status === 1),        // isConfirmed
                                $level,
                                $t->add_deadline_date,
                                $t->upd_deadline_date,
                                $t->entry_start_date
                            );

                            $rows[] = (object)[
                                'term_id'            => $termId,
                                'start_date'         => $start,
                                'end_date'           => $end,
                                'entry_start_date'   => $t->entry_start_date,
                                'add_deadline_date'  => $dead,
                                'user_id'            => (string)$r->user_id,
                                'display_user_id'    => (string)$r->user_id,
                                'display_user_name'  => $userNameMap[(string)$r->user_id] ?? (string)$r->user_id,
                                'source'             => 'TDeli',
                                'order_status'       => (int)$r->order_status,
                                'order_status_label' => '登録済', // ★固定
                                'reception_status' => $existingReception,
                                'reception_phase'  => $existingPhaseCode,
                                'confirm_status'     => ((int)$r->order_status === 1 ? '確定' : '未確定'),
                                'create_date'        => $r->create_date,
                                'update_date'        => $r->update_date,

                                'deli_order_id'      => (int)$r->deli_order_id,
                                'can_select'         => $can,
                                'disabled_reason'    => $why,
                            ];
                        }
                    } else {
                        // ★ 未登録行は「行用フェーズ」に補正して表示する
                        [$phPhaseCode, $phReception] = $this->resolveRowPhaseForPlaceholder(
                            $periodPhase,
                            $this->getSqlNowJst(),
                            $t->add_deadline_date,
                            $t->entry_start_date
                        );
                        // プレースホルダ
                        $ph = $this->makePlaceholder($termId, $start, $end, $dead, '新規登録', $phReception, $phPhaseCode);
                        $ph->entry_start_date = $t->entry_start_date;

                        // ★ 可否判定
                        [$can, $why] = $this->computeRowTouchFlags(
                            $phPhaseCode,
                            true,   // isPlaceholder
                            false,  // isConfirmed
                            $level,
                            $t->add_deadline_date,
                            $t->upd_deadline_date,
                            $t->entry_start_date
                        );

                        // ★ 追加：受付開始前は“行ごと非表示”
                        if (!$can && $why === '追加受付の開始前です') {
                            // 表示しない
                            continue;
                        }
                        // 常に載せる。選択可否は can_select/disabled_reason で表現
                        $ph->can_select      = $can;
                        $ph->disabled_reason = $why;
                        $rows[] = $ph;  // ← can=false でも必ず追加
                        // can=false（例：開始12:00前）は何も追加しない→一覧に出ない
                    }

                } elseif ($level === 0) {
                    // 閲覧（disp_user_idsフィルタ）
                    $any = false;
                    // $any = ture;

                    foreach ($existing as $r) {
                        if (!in_array((string)$r->user_id, $dispUserIds, true)) {
                            continue;
                        }
                        $any = true;

                        // 既存TDeliは「期フェーズ」で判定（L0なので結果は can_select=false だが理由付与のため呼ぶ）
                        [$can, $why] = $this->computeRowTouchFlags(
                            $existingPhaseCode,                  // ★既存は期フェーズ
                            false,                               // isPlaceholder
                            ((int)$r->order_status === 1),       // isConfirmed
                            $level,                              // = 0（閲覧）
                            $t->add_deadline_date,
                            $t->upd_deadline_date,
                            $t->entry_start_date
                        );
                        Log::debug(var_export($can, true));

                        $rows[] = (object)[
                            'term_id'            => $termId,
                            'start_date'         => $start,
                            'end_date'           => $end,
                            'entry_start_date'   => $t->entry_start_date,
                            'add_deadline_date'  => $dead,
                            'user_id'            => (string)$r->user_id,
                            'display_user_id'    => (string)$r->user_id,
                            'display_user_name'  => $userNameMap[(string)$r->user_id] ?? (string)$r->user_id,
                            'source'             => 'TDeli',
                            'order_status'       => (int)$r->order_status,
                            'order_status_label' => '登録済',
                            'reception_status'   => $existingReception,
                            'reception_phase'    => $existingPhaseCode,
                            'confirm_status'     => ((int)$r->order_status === 1 ? '確定' : '未確定'),
                            'create_date'        => $r->create_date,
                            'update_date'        => $r->update_date,
                            'deli_order_id'      => (int)$r->deli_order_id,
                            'can_select'         => $can,          // L0 なので常に false になる
                            'disabled_reason'    => $why,          // '閲覧専用です'
                        ];
                    }

                    if (!$any) {
                        // 未登録（プレースホルダ）は「行用フェーズ」に補正
                        [$phPhaseCode, $phReception] = $this->resolveRowPhaseForPlaceholder(
                            $periodPhase,
                            $this->getSqlNowJst(),
                            $t->add_deadline_date,
                            $t->entry_start_date
                        );

                        $ph = $this->makePlaceholder(
                            $termId, $start, $end, $dead, '新規登録', $phReception, $phPhaseCode
                        );
                        $ph->entry_start_date = $t->entry_start_date;

                        // 閲覧なので可否は false、理由は '閲覧専用です'
                        [$can, $why] = $this->computeRowTouchFlags(
                            $phPhaseCode,          // ★PHは行用フェーズ
                            true,                  // isPlaceholder
                            false,                 // isConfirmed
                            $level,                // = 0
                            $t->add_deadline_date,
                            $t->upd_deadline_date,
                            $t->entry_start_date
                        );
                        $ph->can_select      = $can;   // false
                        $ph->disabled_reason = $why;   // '閲覧専用です'
                        $rows[] = $ph;
                    }
                }
            }

            return $rows;
        }

        /** プレースホルダ行の共通生成 */
        private function makePlaceholder(int $termId, $start, $end, $dead,string $displayName, string $reception, int $phaseCode): object {
            return (object)[
                'term_id'            => $termId,
                'start_date'         => $start,
                'end_date'           => $end,
                'add_deadline_date'  => $dead,
                'user_id'            => null,
                'display_user_id'    => null,
                'display_user_name'  => $displayName,
                'source'             => 'Placeholder',
                'order_status'       => 0,            // 未登録=未確定扱い
                'order_status_label' => '-',
                'reception_status'   => $reception,   // '受付中' / '更新期間中' / '入力期限終了'
                'reception_phase'    => $phaseCode,   // -1/0/1/2
                'confirm_status'     => '-',
                'create_user'        => null,
            ];
        }

        /** ページ活性フラグ（最小） */
        private function computePageFlags(array $rows): array
        {
            $hasAddSelectable  = false; // 新規プレースホルダが選択可
            $hasEditSelectable = false; // 既存TDeliが選択可
            $hasConfirmable    = false; // 未確定(TDeli)
            $hasUnconfirmable  = false; // 確定(TDeli)

            foreach ($rows as $r) {
                $isTDeli = (($r->source ?? '') === 'TDeli');
                $isPH    = (($r->source ?? '') === 'Placeholder');

                if (!empty($r->can_select)) {
                    if ($isPH)   { $hasAddSelectable  = true; }
                    if ($isTDeli){ $hasEditSelectable = true; }
                }

                if ($isTDeli) {
                    $st = (int)($r->order_status ?? -1);
                    if     ($st === 0) $hasConfirmable   = true;
                    elseif ($st === 1) $hasUnconfirmable = true;
                }

                if ($hasAddSelectable && $hasEditSelectable && $hasConfirmable && $hasUnconfirmable) {
                    break;
                }
            }

            // 従来キー（互換維持）
            $hasSelectable = ($hasAddSelectable || $hasEditSelectable);

            return compact('hasSelectable','hasAddSelectable','hasEditSelectable','hasConfirmable','hasUnconfirmable');
        }

        //MUser から user_id→user_name のマップを取得  MUser を where() でサービスIDや許可ユーザーIDで抽出。
        private function loadUserNameMap(array $userIds): array
        {
            $userIds = array_values(array_unique(array_filter($userIds, fn($v) => $v !== null && $v !== '')));
            if (empty($userIds)) { return []; }

            $MUser = $this->fetchTable('MUser');
            $rows = $MUser->find()
                ->select(['user_id','user_name'])
                ->where(['user_id IN' => $userIds, 'del_flg' => '0'])
                ->enableHydration(false)
                ->all()->toList();

            $map = [];
            foreach ($rows as $r) {
                $map[(string)$r['user_id']] = (string)$r['user_name'];
            }
            return $map;
        }

        //  指定サービスID群（例：[2,4]）のユーザーをセレクト用に id=>name で返す index セレクトリストに使用 user_id IN  で名前を抽出
        //  レベル1の処理                       
        private function loadUsersForSelectByServices(array $serviceIds): array
        {
            if (empty($serviceIds)) {
                return [];
            }
            $MUser = $this->fetchTable('MUser');

            $rows = $MUser->find()
                ->select(['user_id', 'user_name'])
                ->where([
                    'del_flg' => '0',
                    'use_service_id IN' => $serviceIds,
                ])
                ->order(['disp_no' => 'ASC', 'user_name' => 'ASC'])
                ->all();

            $out = [];
            foreach ($rows as $r) {
                $out[(string)$r->user_id] = (string)$r->user_name;
            }
            return $out;
        }

        // セレクト用ユーザー候補を取得（サービス/ID制限/サービス種別制限に対応）④ ユーザー候補の抽出
        private function buildUserSelectOptions(int $serviceId, ?array $limitIds = null, ?array $allowedServices = null): array
        {
            $MUser = $this->fetchTable('MUser');
            $q = $MUser->find()
                ->select(['user_id','user_name','use_service_id'])
                ->where(['del_flg' => '0']);

            // allowedServices が渡されていればそれを最優先で適用
            if (!empty($allowedServices)) {
                $q->andWhere(['use_service_id IN' => $allowedServices]);
            } else {
                // 既定: 自サービス(+共通0)。サービス2/4は束ねる
                $fallback = in_array($serviceId, [2,4], true) ? [2,4,0] : [$serviceId, 0];
                $q->andWhere(['use_service_id IN' => $fallback]);
            }

            // limitIds（閲覧許可IDなど）があればさらに限定
            if (!empty($limitIds)) {
                $q->andWhere(['user_id IN' => $limitIds]);
            }

            $rows = $q->order(['user_name' => 'ASC'])
                    ->enableHydration(false)
                    ->all()->toList();

            $options = [];
            foreach ($rows as $r) {
                $options[(string)$r['user_id']] = (string)$r['user_name'];
            }

            Log::debug('[user-select] svc=' . $serviceId
                . ' limit=' . json_encode($limitIds, JSON_UNESCAPED_UNICODE)
                . ' allow=' . json_encode($allowedServices, JSON_UNESCAPED_UNICODE)
                . ' -> ' . count($options) . ' users');

            return $options;
        }
    //受付／締切・状態判定関連
        private function computeReceptionPhase(\DateTimeImmutable $today, $entryStart, $addDeadline, $updDeadline): array
        {
            // --- 日付ベースでフェーズ決定 ---
            $es = $this->asDate0Jst($entryStart);
            $ad = $this->asDate0Jst($addDeadline);
            $ud = $this->asDate0Jst($updDeadline);
            $today0 = $today->setTime(0, 0, 0);

            if (!$es || !$ad || !$ud) {
                return [0, '受付中']; // 安全側
            }

            if ($today0 < $es) {
                return [-1, '受付前'];
            }

            if ($today0 <= $ad) {
                $code  = 0;
                $label = '受付中';
            } elseif ($today0 <= $ud) {
                $code  = 1;
                //登録済み表記
                $label = '受付中';
            } else {
                //登録済み表記　期限過ぎ
                return [2, '受付完'];
            }

            // --- “本日12:00以降”の注記を label にだけ付ける ---
            try {
                $nowJst = $this->getSqlNowJst();
                $ad1200 = $this->at1200Jst($addDeadline);
                $ud1200 = $this->at1200Jst($updDeadline);

                // Log::debug(sprintf(
                //     "[ReceptionPhase] now=%s today0=%s code=%d ad1200=%s ud1200=%s",
                //     $nowJst->format('Y-m-d H:i:s'),
                //     $today0->format('Y-m-d H:i:s'),
                //     $code,
                //     $ad1200?->format('Y-m-d H:i:s') ?? '-',
                //     $ud1200?->format('Y-m-d H:i:s') ?? '-'
                // ));

                // （オプション）更新期間 当日12:00超 → 入力期限終了へ移行
                // ※従来は label 装飾のみ。仕様で切り替えたい場合だけ有効化
                // 受付中(0) で、"add 締切の当日" の 12:00 を過ぎたら → 更新期間中(1) に上げる
                if ($code === 0 && $ad1200 && $ad1200->setTime(0,0,0) == $today0 && $nowJst >= $ad1200) {
                    $code  = 1;
                    // $label = '更新期間中（本日移行）';
                    $label = '受付中';
                }

                // 更新期間中(1) で、"upd 締切の当日" の 12:00 を過ぎたら → 入力期限終了(2) に上げる
                if ($code === 1 && $ud1200 && $ud1200->setTime(0,0,0) == $today0 && $nowJst >= $ud1200) {
                    $code  = 2;
                    // $label = '入力期限終了（本日締切終了）';
                    $label = '受付中';
                }

            } catch (\Throwable $e) {
                Log::warning("[ReceptionPhase] 12:00 code繰上げ失敗: " . $e->getMessage());
                // 失敗時はそのまま
            }

            return [$code, $label];
        }

        private function resolveRowPhaseForPlaceholder(
                    int $periodPhase,
                    \DateTimeImmutable $nowJst,
                    $addDeadline,
                    $entryStart
                ): array 
        {
            $ad1200 = $this->at1200Jst($addDeadline);

            if ($periodPhase === -1) {
                return [-1, '受付前'];
            }

            if ($periodPhase === 0) {
                // デフォルトは受付中
                $code  = 0;
                $label = '受付中';

                // add締切を過ぎていたら「新規不可」扱いに寄せる
                if ($ad1200 && $nowJst >= $ad1200) {
                    $today0  = $nowJst->setTime(0, 0, 0);
                    $isToday = ($ad1200->setTime(0, 0, 0) == $today0);

                    Log::debug(sprintf(
                        "now=%s / ad1200=%s / today0=%s / ad0=%s / isToday=%s",
                        $nowJst->format('Y-m-d H:i:s'),
                        $ad1200?->format('Y-m-d H:i:s'),
                        $today0->format('Y-m-d H:i:s'),
                        $ad1200?->setTime(0,0,0)->format('Y-m-d H:i:s'),
                        $isToday ? 'true' : 'false'
                    ));

                    $code  = 2; // 新規はもう不可
                    $label = $isToday ? '受付中（本日新規締切終了）' : '受付中（新規締切済）';
                }
                return [$code, $label];
            }

            if ($periodPhase === 1) {
                // 更新期間中は新規不可 → code=2, labelは注記付き　プレースホルダー期限過ぎ
                return [2, '受付完'];
            }

            return [2, '受付完'];
        }

        private function computeRowTouchFlags(
                        int $phaseCode,
                        bool $isPlaceholder,
                        bool $isConfirmed,
                        int $level,
                        $addDeadline = null,
                        $updDeadline = null,
                        $entryStart = null
                        ): array 
        {
            $now = $this->getSqlNowJst();
            $ad  = $this->at1200Jst($addDeadline);
            $ud  = $this->at1200Jst($updDeadline);
            $as  = $this->addStart1200Jst($entryStart, $addDeadline);

            // ---- ログ用クロージャ（TouchCheck風）----
            $logDecision = function (bool $can, string $why) use ($now, $phaseCode, $isPlaceholder, $ad, $ud, $as)
            {
                $phaseLabel = match ($phaseCode) {
                    -1 => '受付前',
                    0  => '受付中',
                    //更新期間の表示
                    1  => '受付中',
                    2  => '入力期限終了',
                    default => '不明',
                };
                $rowLabel = $isPlaceholder ? '新規' : '既存';
                $adStr = $ad ? $ad->format('Y-m-d H:i:s') : '-';
                $udStr = $ud ? $ud->format('Y-m-d H:i:s') : '-';
                $adState = ($ad && $now < $ad) ? '入力可能期間' : (($ad && $now >= $ad) ? '締切超過' : '-');
                $udState = ($ud && $now < $ud) ? '入力可能期間' : (($ud && $now >= $ud) ? '締切超過' : '-');
                $regAdd = $isPlaceholder ? '未登録' : '登録済み';
                $regUpd = $isPlaceholder ? '-' : '登録済み';
                $canStr = $can ? '可' : '不可';

                $asStr   = $as ? $as->format('Y-m-d H:i:s') : '-';
                $asState = ($as && $now < $as) ? '開始前' : (($as && $now >= $as) ? '開始済' : '-');
            };

            // ---- 権限レベル ----
            if ($level === 1) { $logDecision(true,  '');                  return [true,  '']; }
            if ($level === 0) { $logDecision(false, '閲覧専用です');      return [false, '閲覧専用です']; }
            // if ($level === 0) { $logDecision(true, '閲覧専用です');      return [true, '閲覧専用です']; }
            if ($level !== 2) { $logDecision(false, '権限を判定できません'); return [false, '権限を判定できません']; }

            // ---- ロック列（isConfirmed=1 など）による強制不可を最優先 ----
            if ($isConfirmed) { 
                $logDecision(false, 'この行はロックされています'); 
                return [false, 'この行はロックされています']; 
            }


            // ---- L2（更新レベル）ここが本体ロジック ----
            switch ($phaseCode) {
            case -1: // 受付前
                $logDecision(false, '受付前です');
                return [false, '受付前です'];

            case 0: // 新規期間（追加受付）
                if ($isPlaceholder) {
                    if ($ad === null) { 
                        $logDecision(false, '追加受付の締切未設定'); 
                        return [false, '追加受付の締切未設定']; 
                    }
                    if ($as === null) {
                        $logDecision(false, '追加受付の開始未設定'); 
                        return [false, '追加受付の開始未設定'];
                    }
                    if ($now < $as) {
                        $logDecision(false, '追加受付の開始前です'); 
                        return [false, '追加受付の開始前です'];
                    }
                    $can = ($now < $ad);
                    $why = $can ? '' : '追加受付の締切を過ぎています';
                    $logDecision($can, $why);
                    return [$can, $why];
                }
                // ★追加：既存行も「新規締切@12:00」を境に編集不可にする
                // 既存（isPlaceholder=false）
                if ($as !== null && $now < $as) {
                    $logDecision(false, '追加受付の開始前です');
                    return [false, '追加受付の開始前です'];
                }
                if ($ad !== null && $now >= $ad) {
                    $logDecision(false, '追加受付の締切を過ぎています');
                    return [false, '追加受付の締切を過ぎています'];
                }
                $logDecision(true, '');
                return [true, ''];

            case 1: // 更新期間（新規不可、既存＝upd締切まで）
                if ($isPlaceholder) { $logDecision(false, '新規登録はできません（更新期間）'); return [false, '新規登録はできません（更新期間）']; }
                if ($ud === null)   { $logDecision(false, '修正締切未設定');                 return [false, '修正締切未設定']; }
                $can = $now < $ud;  $why = $can ? '' : '修正締切を過ぎています';
                $logDecision($can, $why);
                return [$can, $why];

            case 2: // 入力期限終了
                $logDecision(false, '受付期間外です');
                return [false, '受付期間外です'];

            default:
                Log::warning("[computeRowTouchFlags] unknown phaseCode={$phaseCode}");
                $logDecision(false, '受付状態を判定できません');
                return [false, '受付状態を判定できません'];
            }
        }

        /** $v(DateTimeInterface|FrozenDate|string) を JST の「日付だけ(00:00)」に正規化 */
        private function asDate0Jst($v): ?\DateTimeImmutable
        {
            if ($v === null || $v === '') return null;
            try {
                $dt = $v instanceof \DateTimeInterface
                    ? \DateTimeImmutable::createFromInterface($v)
                    : new \DateTimeImmutable((string)$v, new \DateTimeZone('Asia/Tokyo'));
                return $dt->setTime(0,0,0);
            } catch (\Throwable $e) {
                return null;
            }
        }

        /** $v を JST の「12:00」に正規化（締切比較用） */
        private function at1200Jst($v): ?\DateTimeImmutable
        {
            if ($v === null || $v === '') return null;
            try {
                $dt = $v instanceof \DateTimeInterface
                    ? \DateTimeImmutable::createFromInterface($v)
                    : new \DateTimeImmutable((string)$v, new \DateTimeZone('Asia/Tokyo'));
                return $dt->setTime(12,0,0);
            } catch (\Throwable $e) {
                return null;
            }
        }

        // このリクエスト中の SQL 現在時刻(JST)をキャッシュ
        private ?\DateTimeImmutable $sqlNowJstCache = null;

        /**
         * SQL Server基準の現在時刻(JST)を取得（1回だけDBに当て、以降はキャッシュ）
         * computeRowTouchFlags　12時以降処理が出来ないコードの補助
         */
        private function getSqlNowJst(): \DateTimeImmutable
        {
            if ($this->sqlNowJstCache instanceof \DateTimeImmutable) {
                return $this->sqlNowJstCache;
            }
            $conn = $this->fetchTable('MTerm')->getConnection();
            // SQL Serverのローカル時刻 → JST(+09:00)へ明示変換して取得
            $row = $conn->execute(
                "SELECT CONVERT(varchar(23), SWITCHOFFSET(SYSDATETIMEOFFSET(), '+09:00'), 121) AS jst"
            )->fetch('assoc');

            // 例: "2025-08-20 16:58:12.345"
            $this->sqlNowJstCache = new \DateTimeImmutable((string)$row['jst'], new \DateTimeZone('Asia/Tokyo'));
            return $this->sqlNowJstCache;
        }

    //検索／フィルタ処理
        private function filterRowsByParams(array $rows, array $p, bool $isPostSearch): array
        {
            $today = \Cake\I18n\FrozenDate::today();
            $limit = $today->addWeeks(3);

            if (!$isPostSearch) {
                // POST検索でなければ、そのまま返す
                return $rows;
            }

            // 以降は POST抽出ボタン時のみ
            $filter = function($row) use ($p) {

                // ① 受付開始日（= entry_start_date）範囲
                if (!empty($p['entry_start_date_from'])) {
                    $es = isset($row->entry_start_date) ? new \DateTimeImmutable((string)$row->entry_start_date) : null;
                    if ($es === null || $es < new \DateTimeImmutable((string)$p['entry_start_date_from'])) {
                        return false;
                    }
                }
                if (!empty($p['entry_start_date_to'])) {
                    $es = isset($row->entry_start_date) ? new \DateTimeImmutable((string)$row->entry_start_date) : null;
                    if ($es === null || $es > new \DateTimeImmutable((string)$p['entry_start_date_to'])) {
                        return false;
                    }
                }

                // ② 新規締切日（= add_deadline_date）範囲
                if (!empty($p['add_deadline_date_from'])) {
                    if ($this->asDate0Jst($row->add_deadline_date) < $this->asDate0Jst($p['add_deadline_date_from'])) return false;
                }
                if (!empty($p['add_deadline_date_to'])) {
                    if ($this->asDate0Jst($row->add_deadline_date) > $this->asDate0Jst($p['add_deadline_date_to'])) return false;
                }


                // ③ 作成日/更新日（TDeliのみ対象。Placeholderは null なので範囲指定時は落とす）
                $isTDeli = (($row->source ?? '') === 'TDeli');

                if (!empty($p['create_date_from'])) {
                    if (!$isTDeli || new \DateTimeImmutable((string)$row->create_date) < new \DateTimeImmutable((string)$p['create_date_from'])) {
                        return false;
                    }
                }
                if (!empty($p['create_date_to'])) {
                    if (!$isTDeli || new \DateTimeImmutable((string)$row->create_date) > new \DateTimeImmutable((string)$p['create_date_to'])) {
                        return false;
                    }
                }
                if (!empty($p['update_date_from'])) {
                    if (!$isTDeli || new \DateTimeImmutable((string)$row->update_date) < new \DateTimeImmutable((string)$p['update_date_from'])) {
                        return false;
                    }
                }
                if (!empty($p['update_date_to'])) {
                    if (!$isTDeli || new \DateTimeImmutable((string)$row->update_date) > new \DateTimeImmutable((string)$p['update_date_to'])) {
                        return false;
                    }
                }

                // ④ 施設名（= display_user_id）
                if (!empty($p['user_id'])) {
                    if (!$isTDeli || (string)$row->display_user_id !== (string)$p['user_id']) {
                        return false;
                    }
                }

                // ⑤ 発注状態（registered=登録済 / not_registered=未登録）
                if (!empty($p['order_status'])) {
                    if ($p['order_status'] === 'registered') {
                        if (!$isTDeli) return false; // Placeholder を落とす
                    } elseif ($p['order_status'] === 'not_registered') {
                        if ($isTDeli) return false;  // TDeli を落とす
                    }
                }

                // ⑥ 確定状態（0=未確定,1=確定）
                if (isset($p['confirm_status']) && $p['confirm_status'] !== '') {
                    $want = (int)$p['confirm_status'];
                    $status = $row->order_status ?? null;

                    if ($status === null) return false; // null は NG
                    if (!in_array((int)$status, [0, 1], true)) return false; // 0,1 以外は NG

                    // ここに来た時点で order_status は 0 または 1 が保証される
                    if ($want === 1) {
                        if (!$isTDeli || (int)$status !== 1) return false;
                    } else {
                        if ($isTDeli && (int)$status !== 0) return false;
                    }
                }

                return true;
            };

            // ★ここで件数を計測→フィルタ→再計測→ログ
            $before = count($rows);
            $rows   = array_values(array_filter($rows, $filter));
            $after  = count($rows);

            \Cake\Log\Log::debug('[TDeli filter] end before=' . $before . ' after=' . $after
                . ' params=' . json_encode(array_filter($p, fn($v)=>$v!==''&&$v!==null), JSON_UNESCAPED_UNICODE));

            return $rows;
        }
        /** 期間検索の最小フィルタ（start_date / add_deadline_date の From/To が来た場合だけ適用）① Term（期間）の抽出 */
        private function applyTermDateFilters(\Cake\ORM\Query $q, array $p): void
        {
            // entry_start_date_* → entry_start_date に対応
            if (!empty($p['entry_start_date_from'])) {
                $q->andWhere(['entry_start_date >=' => $p['entry_start_date_from']]);
            }
            if (!empty($p['entry_start_date_to'])) {
                $q->andWhere(['entry_start_date <=' => $p['entry_start_date_to']]);
            }
            // add_deadline_date_* はそのまま
            if (!empty($p['add_deadline_date_from'])) {
                $q->andWhere(['add_deadline_date >=' => $p['add_deadline_date_from']]);
            }
            if (!empty($p['add_deadline_date_to'])) {
                $q->andWhere(['add_deadline_date <=' => $p['add_deadline_date_to']]);
            }

        }
    //権限・遷移制御／共通
        private function setSelectedTermOwnerAndDecideAction(array $data, int $serviceId, string $loginUserId): array
        {
            // 1) 選択 term_id を正規化（1件必須）
            $raw = $data['selected_ids']
                ?? $data['selectedIds']
                ?? $data['selected_term_ids']
                ?? $data['term_ids']
                ?? null;

            if ($raw === null && isset($data['select'])) {
                $sel = $data['select'];
                if (is_array($sel)) {
                    $truthy = array_filter($sel, static fn($v) => $v === '1' || $v === 1 || $v === true || $v === 'on');
                    $raw = array_keys($truthy);
                } else {
                    $raw = [$sel];
                }
            }

            $selected = array_values(array_filter(
                array_unique(array_map('intval', (array)$raw)),
                static fn($v) => $v > 0
            ));

            if (count($selected) === 0) return ['error' => 'select_none'];
            if (count($selected) > 1)  return ['error' => 'select_multi'];

            $termId = (int)$selected[0];

            // 2) Lv5 は add/edit へ入らない
            if ($serviceId === 5) return ['error' => 'blocked_service5'];

            $TDeli = $this->fetchTable('TDeliOrder');

            // 3) Lv2/4：自分のみ
            if (in_array($serviceId, [2, 4], true)) {
                $own = $TDeli->find()
                    ->select(['deli_order_id'])
                    ->where(['term_id' => $termId, 'user_id' => $loginUserId, 'del_flg' => '0'])
                    ->first();

                if ($own) {
                    return [
                        'action'        => 'edit',
                        'term_id'       => $termId,
                        'user_id'       => (string)$loginUserId,
                        'deli_order_id' => (int)$own->deli_order_id,
                    ];
                }
                return [
                    'action'  => 'add',
                    'term_id' => $termId,
                    'user_id' => (string)$loginUserId,
                ];
            }

            // 4) Lv1（管理）
            $ownerId = isset($data['user_id']) && $data['user_id'] !== '' ? (string)$data['user_id'] : null;

            if ($ownerId !== null) {
                $own = $TDeli->find()
                    ->select(['deli_order_id'])
                    ->where(['term_id' => $termId, 'user_id' => $ownerId, 'del_flg' => '0'])
                    ->first();
                if ($own) {
                    return [
                        'action'        => 'edit',
                        'term_id'       => $termId,
                        'user_id'       => $ownerId,
                        'deli_order_id' => (int)$own->deli_order_id,
                    ];
                }
                return [
                    'action'  => 'add',
                    'term_id' => $termId,
                    'user_id' => $ownerId,
                ];
            }

            // owner 未指定：既存があれば最初の1件で edit、無ければ loginUserId で add
            $existing = $TDeli->find()
                ->select(['deli_order_id','user_id'])
                ->where(['term_id' => $termId, 'del_flg' => '0'])
                ->orderAsc('deli_order_id')
                ->all()->toArray();

            if (!empty($existing)) {
                return [
                    'action'        => 'edit',
                    'term_id'       => $termId,
                    'user_id'       => (string)$existing[0]->user_id,
                    'deli_order_id' => (int)$existing[0]->deli_order_id,
                ];
            }

            return [
                'action'  => 'add',
                'term_id' => $termId,
                'user_id' => (string)$loginUserId,
            ];
        }
    // 入力UI生成
        private function buildDeliveryMatrix(int $termId, string|int $userId, ?string $startYmd = null): array
        {
            // ① ユーザーのuse_pattern_idを取得
            $usePatternId = $this->fetchTable('MUser')->find()
                ->select(['use_pattern_id'])
                ->where(['user_id' => $userId])
                ->firstOrFail()
                ->use_pattern_id;

            // ② pattern_idに対応するdelivery一覧を取得
            $deliveryItems = $this->fetchTable('MDeliveryPatternSet')->find()
            ->contain(['MDelivery'])
            ->where(['use_pattern_id' => $usePatternId])
            ->orderAsc('MDeliveryPatternSet.delivery_id') // 明示
            ->all();

            // ③ term_idに対応するstart_dateから7日分作成 → 入力開始日優先・クランプ・最大7日
            $term = $this->fetchTable('MTerm')->get($termId);

            // 開始日の決定：入力 > term.start_date
            $base = $term->start_date;
            if ($startYmd) {
                $cand = \Cake\I18n\FrozenDate::createFromFormat('Y-m-d', $startYmd);
                if ($cand instanceof \Cake\I18n\FrozenDate) {
                    if ($cand < $term->start_date) { $cand = $term->start_date; }
                    if ($cand > $term->end_date)   { $cand = $term->end_date; }
                    $base = $cand;
                }
            }

            // 期間内で最大7日
            $days = [];
            $cur  = $base;
            for ($i = 0; $i < 7 && $cur <= $term->end_date; $i++) {
                $days[] = $cur;
                $cur    = $cur->addDays(1);
            }

            return [$deliveryItems, $days];
        }
    /** 現在の権限レベルを返す: 1=管理 / 2=更新 / 0=閲覧 / -1=不可 */
        private function currentLevel(): int
        {
            $perm = $this->decidePermission((string)$this->request->getParam('controller'));
            return match ($perm) { 1 => 1, 2,4 => 2, 5 => 0, default => -1 };
        }
    /** 追加受付の開始境界（entry_start の12:00。無ければ add_deadline の7日前12:00） */
        private function addStart1200Jst($entryStart, $addDeadline): ?\DateTimeImmutable
        {
            $as = $this->at1200Jst($entryStart);
            if ($as) return $as;
            $ad = $this->at1200Jst($addDeadline);
            return $ad ? $ad->sub(new \DateInterval('P7D')) : null;
        }
}
