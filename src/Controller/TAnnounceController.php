<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use DateInterval;
use Cake\I18n\Date;
use Cake\Datasource\ConnectionManager; // ← ファイル冒頭で必要
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;
use Cake\Collection\Collection;


/**
 * TAnnounce Controller
 *
 * @property \App\Model\Table\TAnnounceTable $TAnnounce
 */
class TAnnounceController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */

    /**
     * お知らせのコントローラ
     */
public function index()
{
    // =========================
    // 検索条件（GET）
    // =========================
    $q = $this->request->getQuery();

    $selectedDiv   = $q['announce_div'] ?? null;
    $title         = $q['title'] ?? null;
    $startFrom     = $q['start_from'] ?? null;
    $startTo       = $q['start_to'] ?? null;
    $facilityGroup = $q['facility_group'] ?? null;
    $serviceId     = $q['use_service_id'] ?? null;
    $includeEnd    = ($q['include_end'] ?? '') === '1';
    $includeDeleted = ($q['include_deleted'] ?? '') === '1';


    $query = $this->TAnnounce->find()
        ->distinct(['TAnnounce.announce_id'])
        ->order(['TAnnounce.announce_start_date' => 'DESC']);

    if (!$includeDeleted) {
        $query->where(['TAnnounce.del_flg' => '0']);
    }

    // =========================
    // 条件適用
    // =========================
    if (!empty($selectedDiv)) {
        $query->where(['TAnnounce.announce_div' => $selectedDiv]);
    }

    if (!empty($title)) {
        $query->where([
            'TAnnounce.announce_title LIKE' => '%' . trim($title) . '%'
        ]);
    }

    if (!empty($startFrom)) {
        $query->where([
            'TAnnounce.announce_start_date >=' => $startFrom
        ]);
    }

    if (!empty($startTo)) {
        $query->where([
            'TAnnounce.announce_start_date <=' => $startTo
        ]);
    }

    // 掲載終了を含めない
    if (!$includeEnd) {
        $query->where([
            'OR' => [
                'TAnnounce.announce_end_date IS' => null,
                'TAnnounce.announce_end_date >=' => date('Y-m-d')
            ]
        ]);
    }

    // 施設グループ（user_id 先頭一致）
    if (!empty($facilityGroup)) {

        // ① 施設グループ名 → ID取得
        $groupIds = $this->fetchTable('MUserGroup')->find()
            ->select(['user_group_id'])
            ->where([
                'MUserGroup.user_group_name LIKE' => '%' . $facilityGroup . '%'
            ])
            ->all()
            ->extract('user_group_id')
            ->toArray();

        if (!empty($groupIds)) {

            // ② EXISTS でお知らせ抽出（SQL Server対応）
            $subQuery = $this->fetchTable('TAnnounceUser')->find()
                ->select(['dummy' => 1])
                ->innerJoinWith('MUser')
                ->where([
                    'TAnnounceUser.announce_id = TAnnounce.announce_id',
                    'MUser.user_group_id IN' => $groupIds
                ]);

            $query->where(function ($exp) use ($subQuery) {
                return $exp->exists($subQuery);
            });

        } else {
            // グループが見つからない場合は0件
            $query->where(['1 = 0']);
        }
    }


        
    // 発注サービス
    if (!empty($serviceId)) {
        $subQuery = $this->fetchTable('TAnnounceUser')->find()
            ->select(['dummy' => 1])
            ->innerJoinWith('MUser')
            ->where([
                'TAnnounceUser.announce_id = TAnnounce.announce_id',
                'MUser.use_service_id' => $serviceId
            ]);

        $query->where(function ($exp) use ($subQuery) {
            return $exp->exists($subQuery);
        });
    }

    // =========================
    // 件数・ページング
    // =========================
    $totalCount = $query->count();
    $tAnnounce  = $this->paginate($query);

    // =========================
    // 区分リスト
    // =========================
    $announceDivList = $this->fetchTable('MAnnounceDiv')
        ->find('list',
            keyField: 'announce_div',
            valueField: 'announce_div_name'
        )
        ->where(['del_flg' => '0'])
        ->order(['disp_no' => 'ASC'])
        ->toArray();

    // =========================
    // 発注サービスリスト
    // =========================
    $MServiceList = $this->fetchTable('MService')
        ->find('list',
            keyField: 'use_service_id',
            valueField: 'service_name'
        )
        ->where(['del_flg' => 0, 'use_service_id IN' => [2,3,4]])
        ->order(['disp_no' => 'ASC'])
        ->toArray();

    // =========================
    // 添付ファイルMap
    // =========================
    $attachedFilesMap = [];
    foreach ($tAnnounce as $row) {
        $list = [];
        for ($i = 1; $i <= 5; $i++) {
            $fname = $row->{"temp_filename{$i}"} ?? null;
            if ($fname) {
                $list[] = ['name' => (string)$fname];
            }
        }
        $attachedFilesMap[$row->announce_id] = $list;
        if ($this->request->is('post')) {
            $DISPuTable = $this->fetchTable('MDispUser');
            $action = $this->request->getData('action'); // どのボタンが押されたか（add/edit/delete）

            Log::debug("🔍 action = {$action}");

            // 🔸 追加処理
            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }

            // 🔸 編集・削除で必要になる選択データを安全に取得（null対策込み）
            $rawSelect = $this->request->getData('select') ?? [];
            $selected = array_keys(array_filter($rawSelect)); // チェックされたIDだけ抽出

            Log::debug("📌 rawSelect = " . print_r($rawSelect, true));
            Log::debug("📌 selected = " . print_r($selected, true));

            // 🔸 編集処理
            if ($action === 'edit') {
                if (count($selected) === 1) {
                    $id = $selected[0];
                    try {
                        $tannounce = $this->TAnnounce->find()
                            ->where(['announce_id' => $id, 'del_flg' => '0'])
                            ->firstOrFail();

                        Log::debug('✅ 編集対象のTAnnounceデータ: ' . print_r($tannounce->toArray(), true));

                        return $this->redirect(['action' => 'edit', $id]);
                    } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                        $this->Flash->error("指定されたお知らせ（ID: {$id}）は存在しません。");
                        return $this->redirect(['action' => 'index']);
                    }
                } elseif (count($selected) > 1) {
                    $this->Flash->error('更新は1件のみ選択してください。');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('お知らせが選択されていません。');
                    return $this->redirect(['action' => 'index']);
                }
            }

            // 🔸 論理削除処理（editと同じ $selected 受け取り前提）
            if ($action === 'delete') {
                // edit と同じ配列を前提（ビューの name="selected[]"）
                if (empty($selected) || count((array)$selected) === 0) {
                    $this->Flash->error('お知らせが選択されていません。');
                    return $this->redirect(['action' => 'index']);
                }

                $ids    = array_values(array_unique(array_map('intval', (array)$selected)));
                $userId = $this->Authentication->getIdentity()->get('user_id');

                $conn = $this->TAnnounce->getConnection();
                $conn->begin();
                $affected = 0;

                try {
                    foreach ($ids as $id) {
                        // edit と同じ条件：存在し、かつ del_flg='0' のものだけ対象
                        $row = $this->TAnnounce->find()
                            ->where(['announce_id' => $id, 'del_flg' => '0'])
                            ->first();

                        if (!$row) {
                            // 無視して次へ（既に削除済み もしくは ID 不正）
                            continue;
                        }

                        $row->del_flg     = '1';      // ← 文字列で統一
                        $row->update_user = $userId;

                        if ($this->TAnnounce->save($row)) {
                            $affected++;
                        } else {
                            throw new \RuntimeException('save failed: announce_id=' . $id);
                        }
                    }

                    $conn->commit();

                    if ($affected > 0) {
                        $this->Flash->success('選択されたお知らせを削除しました。');
                    } else {
                        $this->Flash->warning('削除対象が見つかりませんでした。');
                    }
                    return $this->redirect(['action' => 'index']);

                } catch (\Throwable $e) {
                    $conn->rollback();
                    \Cake\Log\Log::error('[delete] 例外: ' . $e->getMessage());
                    $this->Flash->error('削除に失敗しました。もう一度お試しください。');
                    return $this->redirect(['action' => 'index']);
                }
            }
            }
            Log::debug('[announce] attachedFilesMap=' . print_r($attachedFilesMap, true));
            Log::debug(json_encode($attachedFilesMap, JSON_UNESCAPED_UNICODE)); 
    }
    $this->set(compact(
        'tAnnounce',
        'totalCount',
        'announceDivList',
        'MServiceList',
        'selectedDiv',
        'title',
        'startFrom',
        'startTo',
        'facilityGroup',
        'serviceId',
        'includeEnd',
        'includeDeleted'
    ));

    $this->set('attachedFilesMap', $attachedFilesMap);
}



    
/**
 * 複数の添付ファイルを保存し、TAnnounce にファイル名のみ設定する
 */
private function handleAttachments(array $data,
                                     \App\Model\Entity\TAnnounce $TAnnounce,bool $isEdit = false): void
    {
        $uploadPath = WWW_ROOT . 'uploads' . DS . 'announce' . DS;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        for ($i = 1; $i <= 5; $i++) {
            $field = 'attachment' . $i;
            $nameField = 'temp_filename' . $i;

            if (
                !empty($data[$field]) &&
                $data[$field] instanceof \Laminas\Diactoros\UploadedFile &&
                $data[$field]->getError() === UPLOAD_ERR_OK
            ) {
                $uploadedFile = $data[$field];
                $originalFileName = $uploadedFile->getClientFilename();
                $fileName = $originalFileName;
                $filePath = $uploadPath . $fileName;

                // ファイル名の重複回避
                $j = 1;
                $pathInfo = pathinfo($originalFileName);
                while (file_exists($filePath)) {
                    $fileName = $pathInfo['filename'] . "($j)." . $pathInfo['extension'];
                    $filePath = $uploadPath . $fileName;
                    $j++;
                }

                // 保存
                $uploadedFile->moveTo($filePath);

                // ファイル名のみDBに保存
                $TAnnounce->{$nameField} = $fileName;
            } else {
                 // 新規(add)なら null で初期化、編集(edit)なら既存値を維持
                if (!$isEdit) {
                    $TAnnounce->{$nameField} = null;
                }
            }
        }
    }
public function download(string $fname)
{
    try {
        $this->request->allowMethod(['post']);

        // 入力ログ
        \Cake\Log\Log::debug('[download] ENTER fname(raw)=' . $fname
            . ' method=' . $this->request->getMethod());

        // ファイル名バリデーション（日本語/空白/()はOK、.. と / \ はNG）
        $fname = trim($fname);
        if ($fname === '' || str_contains($fname, '..') || str_contains($fname, '/') || str_contains($fname, '\\')) {
            \Cake\Log\Log::error('[download] BadRequest: invalid fname=' . $fname);
            throw new \Cake\Http\Exception\BadRequestException('不正なファイル名です。');
        }

        $dir  = WWW_ROOT . 'uploads' . DS . 'announce' . DS;
        $path = $dir . $fname;

        // 事前診断ログ
        $exists = is_file($path);
        $size   = $exists ? @filesize($path) : 0;
        \Cake\Log\Log::debug(sprintf('[download] path=%s exists=%s size=%s',
            $path, $exists ? '1' : '0', (string)$size));

        if (!$exists) {
            \Cake\Log\Log::error('[download] NotFound: ' . $path);
            throw new \Cake\Http\Exception\NotFoundException('ファイルが見つかりません。');
        }
        // MIME を拡張子で指定（PDF/Excel）
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'  => 'application/pdf',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => null,
        };

        $options = ['download' => true, 'name' => $fname];
        if ($mime) $options['contentType'] = $mime;

        // 余計な出力の可能性を潰す
        while (ob_get_level() > 0) { @ob_end_clean(); }

        // MIME 確定（既に $mime 生成済みならそれを使う）
        $mime = $mime ?? 'application/octet-stream';
        $disposition = "attachment; filename*=UTF-8''" . rawurlencode($fname);

        // 手動で読んで返す（Windows + 日本語名でも安定）
        $data = @file_get_contents($path);
        if ($data === false) {
            \Cake\Log\Log::error('[download] file_get_contents failed for ' . $path);
            throw new \RuntimeException('read failed');
        }

        return $this->response
            ->withType($mime)
            ->withHeader('Content-Disposition', $disposition)
            ->withHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withStringBody($data);
    } catch (\Throwable $e) {
        \Cake\Log\Log::error('[download FATAL] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        throw $e;
    }
   

}


public function add()
{
    // ② 明示的に空エンティティを作って代入
    $TAnnounce = $this->TAnnounce->newEmptyEntity();
    // ② セレクト用リスト取得
    $MServiceList = $this->fetchTable('MService')        //サービステーブル
            ->find('list',keyField: 'use_service_id',valueField: 'service_name')
            ->where(['del_flg' => 0, 'use_service_id IN' => [2, 3, 4] ])
            ->order(['disp_no' => 'ASC'])
            ->toArray();
    $groupList = $this->fetchTable('MUserGroup')         //施設グループ取得
            ->find('list',keyField: 'user_group_id',valueField: 'user_group_name')
            ->where(['del_flg' => 0,'user_group_id NOT IN' => [40000]])
            ->order(['disp_no' => 'ASC'])
            ->toArray();
    $announceDivList = $this->fetchTable('MAnnounceDiv') //お知らせ区分
            ->find('list',keyField: 'announce_div',valueField: 'announce_div_name')
            ->where(['del_flg' => '0']);

    // GET or POST（バリデーションエラー時含む）: 初期値準備
    $MUser = $this->fetchTable('MUser');
    $deliveryTargets = $MUser->find()
        ->where(['del_flg' => 0, 'status' => 1])
        ->all();

    // 条件に合致するレコード数をカウント
    $MUser = $this->fetchTable('MUser');
    $deliveryTargets = $MUser ->find()
        ->where(['del_flg' => 0, 'status' =>1 ])
        ->all();
    
    Log::debug('✅ $TAnnounceの型: ' . gettype($TAnnounce));
    Log::debug('✅ $TAnnounceのクラス: ' . (is_object($TAnnounce) ? get_class($TAnnounce) : 'オブジェクトでない'));

    if ($this->request->is('post')) {
        // ① request データの取得
        $data = $this->request->getData();
        Log::debug('✅ POST受付'); // ← これが出ていないなら、そもそも POST として受け取れていない
        
        // 入力チェック：announce_start_date が未入力なら即 return（変数定義なし）
            if (empty($data['announce_start_date'])) {
                $this->Flash->error('掲載開始日が未入力です。');

                // 値の再セットを追加
                $this->set(compact(
                    'TAnnounce', 'deliveryTargets', 'announceDivList', 'groupList', 'MServiceList'
                ));
                $this->set('selectedUserIds', $selectedUserIds ?? []); 
                $this->set('mode', 'add');

                return $this->render('add_edit');
            }
        // ✅ 値がある場合のみ代入処理
        $TAnnounce->announce_start_date = $data['announce_start_date'];
        // 添付ファイル
        $uploadPath = WWW_ROOT . 'uploads' . DS . 'announce' . DS;
        // 添付ファイルを一括処理（ファイル名のみ保存）
        $this->handleAttachments($data, $TAnnounce, false);

        $loginUserId = $this->request->getAttribute('identity')->get('user_id');
        // ユーザー情報をセット
            $TAnnounce->create_user = $loginUserId;
            $TAnnounce->update_user = $loginUserId;
            $TAnnounce->del_flg = '0';
        // ★ チェックボックス選択ユーザーの処理
            //ajax処理
            $selectedUserIds = array_values(array_filter(array_map('strval', $data['selected_user_ids'] ?? []), fn($id) => $id !== ''));

            Log::write('debug', '選択された user_id 一覧: ' . print_r($selectedUserIds, true));
            //配信先設定
            if (empty($selectedUserIds)) {
                $this->Flash->error('施設が選択されていません。');

                // 入力済みの情報を再セット（POST値保持用）
                $this->set(compact(
                    'TAnnounce', 'deliveryTargets', 'announceDivList','groupList','MServiceList'
                ));
                $this->set('selectedUserIds', $selectedUserIds); // ビューで再チェック用
                $this->set('mode', 'add');

                return $this->render('add_edit');
            }
            // ★ 正しくここで取得と開始
            $conn = ConnectionManager::get('default');
            $conn->begin();

            $TAnnounce = $this->TAnnounce->patchEntity($TAnnounce, $data);

            // ★ 保存処理
            try {
                if (!$this->TAnnounce->save($TAnnounce)) {
                throw new \RuntimeException('TAnnounce 保存に失敗しました。');
            }
            // ← ここから下は「保存成功時」にしか実行されない
            $announceId = $TAnnounce->announce_id;
            Log::debug("✅ TAnnounce 保存成功。ANNOUNCE_ID=$announceId");
                $this->processAnnounceUsers($announceId, $selectedUserIds, $TAnnounce);

                // ✅ ここが抜けていた
                $conn->commit();


                        // ★ 成功したらコミットyattekur
                        $this->Flash->success('保存できました');
                        return $this->redirect(['action' => 'index']);

                    } catch (\Throwable $e) {
                        // ★ エラーが出たらロールバック
                        $conn->rollback();
                        Log::error('保存失敗: ' . $e->getMessage());
                    }   
                }
            $this->set(compact('TAnnounce', 'deliveryTargets','announceDivList','groupList','MServiceList'));
            $this->set('mode', 'add');
            $this->render('add_edit');
}
//★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★
public function edit($id = null)
{
    Log::debug("🟡 editアクション開始: id = {$id}");
    // 対象データ取得（バリデーション済み）
    // $TAnnounce = $this->TAnnounce->get($id);                    //更新対象のレコード取得
    $TAnnounce = $this->TAnnounce->get($id,
    //  [
    //         'contain' => ['TAnnounceUser'] //閲覧テーブル
    //     ]
    );
    
    $TAUserList = $this->fetchTable('TAnnounceUser');   //お知らせ閲覧者テーブル
    // 既存の閲覧ユーザー一覧（user_id の配列としてセット）
    $selectedUserIds = $TAUserList->find()
            ->where(['announce_id' => $id])
            ->all()                     // ✅ Collection に変換
            ->extract('user_id')        // ✅ extract は Collection メソッド
            ->toList();                 // ✅ 値だけの配列に変換

    // ② セレクト用リスト取得
    $MServiceList = $this->fetchTable('MService')        //サービステーブル
            ->find('list',keyField: 'use_service_id',valueField: 'service_name')
            ->where(['del_flg' => 0, 'use_service_id IN' => [2, 3, 4] ])
            ->order(['disp_no' => 'ASC'])
            ->toArray();
    $groupList = $this->fetchTable('MUserGroup')         //施設グループ取得
            ->find('list',keyField: 'user_group_id',valueField: 'user_group_name')
            ->where(['del_flg' => 0,'user_group_id NOT IN' => [40000]])
            ->order(['disp_no' => 'ASC'])
            ->toArray();
    $announceDivList = $this->fetchTable('MAnnounceDiv') //お知らせ区分
            ->find('list',keyField: 'announce_div',valueField: 'announce_div_name')
            ->where(['del_flg' => '0']);
    

    //① 添付ファイルのログ出力とファイルリンク作成（temp_filename1～5）
    $fileLinks = $this->buildFileLinks($TAnnounce);
        // このお知らせに紐づく対象ユーザーID一覧（TAnnounceUser）
        $announceuserIds = $this->getUserIdsFromAnnounce((int)$id);
            // その user_id の接頭5文字（＝施設グループID）を取得
            $prefixes = array_unique(array_map(fn($uid) => substr((string)$uid, 0, 5), $announceuserIds));
            $selectedGroupList = $this->getSelectedGroupList($prefixes);
                //該当施設グループ名を取得（初期表示セレクト用）
                $registeredUsers = $TAUserList->find()
                        ->where(['announce_id' => $id])
                        ->all()
                        ->extract('user_id')
                        ->toArray();
                    $deliveryTargets = $this->fetchTable('MUser')->find()
                        ->where(['del_flg' => 0, 'status' => 1])
                        ->all();
                    Log::debug('🐛 $prefixes = ' . var_export($prefixes, true));

                    $firstGroupId = null;
                    if (!empty($registeredUsers)) {
                        $firstUser = $this->fetchTable('MUser')
                            ->find()
                            ->where(['user_id IN' => $registeredUsers])
                            ->first(); // 最初の1人から group_id を使う

                        $firstGroupId = $firstUser?->use_service_id;
                }

        //セレクトの値を取得
            // セレクト初期選択用：1件目があれば使う
            $selectedGroupId = $prefixes[0] ?? null;
            $selectedServiceId = $firstGroupId;

            Log::debug('[初期選択] $prefixes = ' . print_r($prefixes, true));
            Log::debug('[初期選択] $selectedGroupId = ' . $selectedGroupId);
            Log::debug('[初期選択] $firstGroupId = ' . $firstGroupId);
            Log::debug('[初期選択] $selectedServiceId = ' . $selectedServiceId);


    
    // POST or PUT のときのみ処理
    if ($this->request->is(['post', 'put', 'patch'])) {
        $data = $this->request->getData();
        Log::debug('✅ POST受付: ' . print_r($data, true));

        $TAnnounce = $this->TAnnounce->patchEntity($TAnnounce, $data);
        // 添付ファイル更新
        $this->handleAttachments($data, $TAnnounce, true);
        //ajax処理
            $selectedUserIds = array_values(array_filter(array_map('strval', $data['selected_user_ids'] ?? []), fn($id) => $id !== ''));

            Log::write('debug', '選択された user_id 一覧: ' . print_r($selectedUserIds, true));
            //配信先設定
            if (empty($selectedUserIds)) {
                $this->Flash->error('施設が選択されていません。');

                // 入力済みの情報を再セット（POST値保持用）
                $this->set(compact(
                    'TAnnounce', 'deliveryTargets', 'announceDivList','groupList','MServiceList'
                ));
                $this->set('selectedUserIds', $selectedUserIds); // ビューで再チェック用
                $this->set('mode', 'add');

                return $this->render('add_edit');
            }

        // 保存本体
        if ($this->TAnnounce->save($TAnnounce)) {
            $announceId = $TAnnounce->announce_id;
            $tAnnounceuserTable = $this->fetchTable('TAnnounceUser');

            $selectedUserIds = array_values(array_filter(array_map('strval', $data['selected_user_ids'] ?? []), fn($id) => $id !== ''));

            $existingUsers = $tAnnounceuserTable->find()
                ->where(['announce_id' => $announceId])
                ->all()
                ->indexBy('user_id')
                ->toArray();

            $loginUserId = $this->request->getAttribute('identity')->get('user_id');
            $newUserKeys = [];

            $this->processAnnounceUsers($announceId, $selectedUserIds, $TAnnounce);


            $this->Flash->success('お知らせを更新しました。');
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error('保存に失敗しました。');
            Log::debug(print_r($TAnnounce->getErrors(), true));
        }
    }

    $this->set(compact('TAnnounce', 'deliveryTargets', 'announceDivList', 'groupList', 'fileLinks', 'MServiceList',
                            'selectedGroupId','selectedServiceId','selectedUserIds'));
    $this->set('mode', 'edit');
    $this->render('add_edit');
}
/**
 * お知らせ閲覧ユーザーの差分更新処理
 */
//ADD EDIT のお知らせ配信先テーブルの処理
private function processAnnounceUsers(int $announceId, array $selectedUserIds, \App\Model\Entity\TAnnounce $TAnnounce): void
{
    $loginUserId = $this->request->getAttribute('identity')->get('user_id');
    $tAnnounceuserTable = $this->fetchTable('TAnnounceUser');

    // 既存データを取得（user_idをキーに）
    $existingUsers = $tAnnounceuserTable->find()
        ->where(['announce_id' => $announceId])
        ->all()
        ->indexBy('user_id')
        ->toArray();

    $newUserKeys = [];

    foreach ($selectedUserIds as $userId) {
        $newUserKeys[] = $userId;

        if (isset($existingUsers[$userId])) {
            // 既に存在 → スキップ（削除対象から除外）
            unset($existingUsers[$userId]);
            continue;
        }

        // 新規作成
        $entity = $tAnnounceuserTable->newEntity([
            'announce_id' => $announceId,
            'user_id' => $userId,
            'create_user' => $TAnnounce->create_user,
            'create_date' => $TAnnounce->create_date,
            'update_user' => $loginUserId,
        ]);

        if (!$tAnnounceuserTable->save($entity)) {
            Log::error("保存失敗: user_id=$userId エラー: " . print_r($entity->getErrors(), true));
            throw new \RuntimeException("TAnnounceUser 保存失敗: user_id=$userId");
        }

        Log::debug("🆕 閲覧者追加: {$userId}");
    }

    // 残っているのは削除対象
    foreach ($existingUsers as $userId => $entity) {
        if (!in_array($userId, $newUserKeys, true)) {
            $tAnnounceuserTable->delete($entity);
            Log::debug("🗑 閲覧者削除: {$userId}");
        }
    }
}

//EDIT ① 添付ファイルのログ出力とファイルリンク作成（temp_filename1～5）
private function buildFileLinks($TAnnounce): array
{
    $links = [];
    for ($i = 1; $i <= 5; $i++) {
        $field = "temp_filename{$i}";
        $value = $TAnnounce->$field ?? null;

        Log::debug("[🧾 DEBUG] {$field} = " . var_export($value, true));
        $links[$i] = !empty($value) ? $value : null;
    }
    return $links;
}
//EDIT  お知らせに紐づく対象ユーザーID一覧（TAnnounceUser）
private function getUserIdsFromAnnounce(int $announceId): array
{
    return $this->fetchTable('TAnnounceUser')->find()
        ->select(['user_id'])
        ->where(['announce_id' => $announceId])
        ->distinct()
        ->all()
        ->extract('user_id')
        ->toArray();
}
//EDIT   紐づいたユーザー　user_id の接頭5文字（＝施設グループID）を取得
private function getSelectedGroupList(array $prefixes): array
{
    $query = $this->fetchTable('MUserGroup')->find('list', 
        keyField : 'user_group_id',
        valueField : 'user_group_name'
    )->order(['disp_no' => 'ASC']);

    if (!empty($prefixes)) {
        $query->where(function ($exp, $q) use ($prefixes) {
            $conditions = [];
            foreach ($prefixes as $prefix) {
                $conditions[] = $exp->like('user_group_id', "{$prefix}%");
            }
            return $exp->or($conditions);
        });
    }

    return $query->toArray();
}

/**
 * 添付ファイルを削除する
 */
public function ajaxResetAttachment($i)
{
 	Log::debug('ajaxResetattachment called');
    $this->request->allowMethod(['post']);
    $this->autoRender = false;
    $TAnnounceTable = $this->fetchTable('TAnnounce');

    // POSTデータからIDを取得
    $announceId = $this->request->getData('announceId');

    if (empty($announceId)) {
        echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
        return;
    }

    try {
        // レコード取得
        $TAnnounce  = $TAnnounceTable->get($announceId);

        // 動的に対象の添付ファイルフィールド名を組み立て
        $fieldName = 'temp_filename' . $i;

        // 現在のフィールドの値を取得
        $currentValue = $TAnnounce->$fieldName;

        if ($currentValue !== null) {
            // DB上のフィールドを null にして保存
            $TAnnounce->$fieldName = null;

            if ($TAnnounceTable->save($TAnnounce)) {
                echo json_encode(['success' => true, 'message' => '添付ファイルを削除しました。']);
            } else {
                echo json_encode(['success' => false, 'message' => '添付ファイル削除を失敗しました。']);
            }
        }else {
            // もし削除対象のファイルが既にnullなら、削除する必要がないことを通知
            echo json_encode(['success' => false, 'message' => '添付ファイルが存在しません。']);
        }
    } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
        echo json_encode(['success' => false, 'message' => '指定されたユーザーが存在しません。']);
    }
}
    
//★★★★★★★★★★★★★★★★
public function ajaxDeliveryTargets()
{
    $this->request->allowMethod(['post']);
    Log::debug('✅ ajaxDeliveryTargets called');

    // ▼ 受信
    $serviceCode = (string)($this->request->getData('serviceCode') ?? '');
    $groupCode   = (string)($this->request->getData('groupCode') ?? '');
    $mode        = (string)($this->request->getData('mode') ?? 'add');
    $announceId  = $this->request->getData('announceId') ?? $this->request->getData('announce_id');

    Log::debug("[📩 受信] serviceCode={$serviceCode}, groupCode={$groupCode}, mode={$mode}, announceId={$announceId}");

    // ▼ サービスコードのマッピング（必要なら調整）
    $serviceMap = [
        '2' => 2,
        '3' => 3,
        '4' => 4,
    ];

    // ▼ edit用：登録済ユーザー
    $registeredUserIds = [];
    if (!empty($announceId)) {
        $registeredUserIds = $this->fetchTable('TAnnounceUser')
            ->find()
            ->where(['announce_id' => $announceId])
            ->all()
            ->extract('user_id')
            ->toArray();
        Log::debug("✅ 登録済ユーザーID: " . implode(', ', $registeredUserIds));
    }

    // ▼ ベースクエリ（常時かかる条件）
    $MUser = $this->fetchTable('MUser');
    $query = $MUser->find()
        ->where(['MUser.del_flg' => 0, 'MUser.status' => 1,'MUser.use_service_id IN' => [2,3,4]]);

    // ▼ フィルタ（空なら追加しない＝全件）
    if ($serviceCode !== '' && isset($serviceMap[$serviceCode])) {
        $query->where(['MUser.use_service_id' => $serviceMap[$serviceCode]]);
        Log::debug("🔍 条件追加: use_service_id = {$serviceMap[$serviceCode]}");
    }
    if ($groupCode !== '') {
        $query->where(function ($exp, $q) use ($groupCode) {
            return $exp->like('MUser.user_id', $groupCode . '%');
        });
        Log::debug("🔍 条件追加: user_id LIKE '{$groupCode}%'");
    }

    // ▼ 取得 & ログ
    $count = $query->count();
    Log::debug("📦 ヒット件数: {$count}");
    if (method_exists($query, 'sql')) {
        Log::debug("🧾 生成SQL: " . $query->sql());
    }

    $deliveryTargets = $query->all();

    // ▼ Viewへ
    $this->set(compact('deliveryTargets'));
    $this->set('selectedUserIds', $registeredUserIds);
    $this->viewBuilder()->disableAutoLayout();
}
}
