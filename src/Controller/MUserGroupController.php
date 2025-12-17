<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use \Exception;
use Cake\ORM\TableRegistry;
/**
 * MUserGroup Controller
 *
 * @property \App\Model\Table\MUserGroupTable $MUserGroup
 */
class MUserGroupController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // ▼ MUserGroup 用
        $showDeleted = $this->request->getData('del_flg') === '1';

        $usergroupQuery = $this->MUserGroup->find()
            ->where($showDeleted ? [] : ['del_flg' => 0])
            ->order(['disp_no' => 'ASC']);
        $mUserGroup = $this->paginate($usergroupQuery);
        $this->set(compact('mUserGroup'));

        // 件数も同じ条件で
        $count = $usergroupQuery->count();
        $this->set(compact('count'));

        if ($this->request->is('post')) {
            $action = $this->request->getData('action');
            $selected = $this->request->getData('select') ?? [];
            $selectedIds = array_keys(array_filter($selected));
            $selectcount = count($selectedIds);

            // 追加処理
            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }
            
            // 更新処理
            if ($action === 'edit') {
                if ($selectcount === 1) {
                    return $this->redirect(['action' => 'edit', $selectedIds[0]]);
                } elseif ($selectcount === 0) {
                    $this->Flash->error('施設グループが選択されていません。');
                } else {
                    $this->Flash->error('更新は1件のみ選択可能です。');
                }
            }
            
           // 削除処理
            if ($action === 'delete') {
                if(!empty($selectedIds)){
                    Log::debug("🟠 Delete action triggered: selectedIds = " . json_encode($selectedIds));

                    $mUserGroupTable = $this->fetchTable('MUserGroup');
                    $mUserTable = $this->fetchTable('MUser');

                    $errorNames = [];

                    // 対象取得
                    $usergroups = $mUserGroupTable->find()
                        ->where(['user_group_id IN' => $selectedIds, 'del_flg' => 0])
                        ->all();

                    foreach ($usergroups as $usergroup) {
                        $prefix = substr((string)$usergroup->user_group_id, 0, 5);
                        Log::debug("🔍 処理対象: user_group_id = {$usergroup->user_group_id}, name = {$usergroup->user_group_name}");

                        $query = $mUserTable->find()
                            ->where(function (QueryExpression $exp, Query $q) use ($prefix) {
                        return $exp->add([
                            $q->newExpr("LEFT(user_id, 5) = '$prefix'"),
                            'del_flg' => '0'
                        ]);
                    });


                        Log::debug("🔍 SQL: " . $query->sql());

                        $count = $query->count();
                        Log::debug("🔍 使用中件数: {$count}");

                        if ($count > 0) {
                            $errorNames[] = $usergroup->user_group_name;
                            Log::warning("🛑 使用中: {$usergroup->user_group_name} は削除不可");
                            continue;
                        }

                        Log::info("✅ 未使用 → 削除処理へ: {$usergroup->user_group_name}");

                        $usergroup->del_flg = 1;
                        $usergroup->update_user = $this->request->getAttribute('identity')->get('user_id');

                        if ($mUserGroupTable->save($usergroup)) {
                            Log::info("💾 save() 成功: {$usergroup->user_group_id}");
                        } else {
                            Log::error("❌ save() 失敗: " . json_encode($usergroup->getErrors()));
                        }
                    }

                    if (!empty($errorNames)) {
                        $this->Flash->error(implode('、', $errorNames) . ' は施設で使用されている為、削除できません。');
                    } else {
                        $this->Flash->success('選択された施設グループを削除しました。');
                    }

                        return $this->redirect(['action' => 'index']);
                }else{
                        $this->Flash->error('施設グループが選択されていません。');
                    return $this->redirect(['action' => 'index']);
                }
            }
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mUserGroup = $this->MUserGroup->newEmptyEntity();

        if (!$this->request->is('post')) {
            $mUserGroup->disp_no = 0;
        }

        // 発注番号の最大値＋1を先にセット（フォーム表示用）
        $maxId = $this->MUserGroup->find()
            ->select(['max_id' => 'MAX(user_group_id)'])
            ->first()
            ->max_id ?? 0;
        $mUserGroup->user_group_id = $maxId + 1;

        Log::debug('セットされた次のuser_group_id: ' . $mUserGroup->user_group_id);

        if ($this->request->is('post')) {
            $mUserGroup = $this->MUserGroup->patchEntity($mUserGroup, $this->request->getData());
            $loginUserId = $this->request->getAttribute('identity')->get('user_id');
            $mUserGroup->del_flg = "0";
            $mUserGroup->create_user = $loginUserId;
            $mUserGroup->update_user = $loginUserId;
            try {
                if ($this->MUserGroup->save($mUserGroup)) {
                    $this->Flash->success(__('登録しました。'));
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error(__('登録に失敗しました。'));
                }
            } catch (Exception $e) {
                $this->Flash->error('システムエラーです。登録に失敗しました。');
                Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }

        $this->set(compact('mUserGroup'));
        $this->set('mode', 'add');
        $this->render('add_edit');
    }

    /**
     * Edit method
     *
     * @param string|null $id M User Group id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
{
    $mUserGroup = $this->MUserGroup->get($id);
    if ($this->request->is(['post', 'put', 'patch'])) {
        $mUserGroup = $this->MUserGroup->patchEntity($mUserGroup, $this->request->getData());
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');
        $mUserGroup->update_user = $loginUserId;

        // ★ 削除フラグがONなら使用中チェック
        if ($mUserGroup->del_flg === '1') {
            $mUserTable = $this->fetchTable('MUser');
            $prefix = substr((string)$mUserGroup->user_group_id, 0, 5);

            $isUsed = $mUserTable->find()
                ->where(function ($exp, $q) use ($prefix) {
                    return $exp->add([
                        $q->newExpr("CAST(user_id AS VARCHAR) LIKE '$prefix%'"),
                        'del_flg' => 0
                    ]);
                })
                ->count() > 0;

            // 値と型を確認
            Log::debug('中身：'.$isUsed);
            Log::debug('型：'.gettype($isUsed));
            Log::debug('変数の値: ' . ($isUsed ? 'true' : 'false'));

            if ($isUsed) {
                $this->Flash->error('施設グループが施設で使用されているため、削除状態にできません。');
                return $this->redirect(['action' => 'edit', $mUserGroup->user_group_id]);
            }
        }

        try {
            if ($this->MUserGroup->save($mUserGroup)) {
                $this->Flash->success(__('更新しました。'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('更新に失敗しました。'));
            }
        } catch (Exception $e) {
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
    Log::debug('edit対象のデータ: ' . print_r($mUserGroup->toArray(), true));
    $this->set(compact('mUserGroup'));
    $this->set('mode', 'edit');
    $this->render('add_edit'); // ← 共通テンプレートを呼び出す
}
public function search()
{
    // MUser を起点にする
    $mUserTable = $this->fetchTable('MUser');

    $q = $this->request->getQuery();

    $conditions = [];

    /* ========= 検索条件 ========= */

    // 施設コード（完全一致）
    if (!empty($q['facility_cd'])) {
        $conditions['MUser.user_id'] = $q['facility_cd'];
    }

    // 施設名称（部分一致）
    if (!empty($q['facility_name'])) {
        $conditions['MUser.user_name LIKE'] = '%' . $q['facility_name'] . '%';
    }

    // 施設グループコード（完全一致）
    if (!empty($q['user_group_id'])) {
        $conditions['mug.user_group_id'] = $q['user_group_id'];
    }

    // 施設グループ名（部分一致）
    if (!empty($q['user_group_name'])) {
        $conditions['mug.user_group_name LIKE'] = '%' . $q['user_group_name'] . '%';
    }

    // 削除除外（必要に応じて）
    $conditions['MUser.del_flg'] = 0;

    /* ========= クエリ ========= */

    $query = $mUserTable->find()
        ->select([
            'facility_cd'      => 'MUser.user_id',
            'facility_name'    => 'MUser.user_name',
            'user_group_id'    => 'mug.user_group_id',
            'user_group_name'  => 'mug.user_group_name',
        ])
        ->leftJoin(
            ['mug' => 'm_user_group'],
            "ISNUMERIC(SUBSTRING(CAST(MUser.user_id AS VARCHAR), 1, 5)) = 1
             AND TRY_CAST(SUBSTRING(CAST(MUser.user_id AS VARCHAR), 1, 5) AS INT) = mug.user_group_id"
        )
        ->where($conditions)
        ->order([
            'mug.user_group_id' => 'ASC',
            'MUser.user_id'     => 'ASC'
        ])
        ->limit(200)
        ->all();

    /* ========= View 用 ========= */

    $viewedUsers = [];
    foreach ($query as $row) {
        // 一覧は「施設グループ検索」なので
        // グループ単位で表示
        if (!empty($row->user_group_id)) {
            $viewedUsers[$row->user_group_id] = $row->user_group_name;
        }
    }

    $this->set(compact('viewedUsers'));
}
}
