<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController; 

use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Exception\PersistenceFailedException;

use \Exception;

/**
 * 食材商品コントローラー   Mfoods Controller
 *
 * @property \App\Model\Table\MfoodsTable $Mfoods
 */
class MfoodsController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
public function index()
{   

    $showDeleted = $this->request->is('post') ? $this->getRequest()->getData('del_flg') : null;
    
    //POSTデータ取得
    $request = $this->request->getData();
    //検索条件の初期化
    $foodId = $request['food_id'] ?? '';
    $foodName = $request['food_name'] ?? '';
    $userGroupId = $request['user_group_id'] ?? '';
    $showDeleted = isset($request['del_flg']) && $request['del_flg'] === '1';//状態確認、チェックが入っていればtrue

    // Mfoods テーブル
    $mfoodsTable = $this->fetchTable('Mfoods');

    // 一覧表示用クエリ（必要な JOIN をあとで足す）
    $query = $mfoodsTable->find()
        ->contain(['MFoodCategories'])        
        ->enableAutoFields(true)
        ->where(['Mfoods.disp_no IS NOT' => null])
        ->order(['Mfoods.disp_no' => 'ASC',
        'Mfoods.food_id' => 'ASC'
    ]);

    $mfoods = $query->all();    

    //削除フラグチェック
    if (!$showDeleted){
        $query->where(['Mfoods.del_flg' => '0']);
    }    

    // 食品コードによる完全一致
    if (!empty($foodId)) {
        $query->where(['Mfoods.food_id' => $foodId]);
    }

    // 食品名による部分一致
    if (!empty($foodName)) {
        $query->where(['Mfoods.food_name LIKE' => "%{$foodName}%"]);
    }

    // group_idで絞込があるとき
    if (!empty($userGroupId)) {
        $query
            ->innerJoin(['mfu' => 'm_food_user'], 'mfu.food_id = Mfoods.food_id')
            ->innerJoin(['mu' => 'm_user'], 'mu.user_id = mfu.user_id')
            // user_id の先頭5桁を user_group_id として扱うため SUBSTRING 使用
            ->innerJoin(
                ['mug' => 'm_user_group'],
                "ISNUMERIC(SUBSTRING(CAST(mu.user_id AS VARCHAR), 1, 5)) = 1 AND TRY_CAST(SUBSTRING(CAST(mu.user_id AS VARCHAR), 1, 5) AS INT) = mug.user_group_id"
            )//不正なデータ（数値じゃないもの）を除外して結果に含めない
 
            ->where(['mug.user_group_id' => $userGroupId])
           
            //商品を重複せず表示させる
            ->group([
                'Mfoods.food_id',
                'Mfoods.food_name',
                'Mfoods.category_id',
                'Mfoods.food_specification',
                'Mfoods.disp_no',
                'Mfoods.del_flg',
                'Mfoods.create_user',
                'Mfoods.create_date',
                'Mfoods.update_user',
                'Mfoods.update_date',
                'MFoodCategories.category_id',
                'MFoodCategories.category_name',
                'MFoodCategories.disp_no',
                'MFoodCategories.del_flg',
                'MFoodCategories.create_user',
                'MFoodCategories.create_date',
                'MFoodCategories.update_user',
                'MFoodCategories.update_date',
            ]);
    }

        $mfoods = $query->all();
        $count = $mfoods->count();

    // groupListを取得（ビューで使うため）
    $groupList = $this->fetchTable('MUserGroup')
        ->find('list', 
            keyField    : 'user_group_id',       // フォーム送信時の値（ID）
            valueField  : 'user_group_name'    // セレクトボックスの表示（名前）
        )
        ->where(['MUsergroup.del_flg' => 0])
        ->toArray();


    $this->set(compact('mfoods', 'count', 'foodId', 'foodName', 'userGroupId','showDeleted', 'groupList'));


        if ($this->request->is('post')) {
            $action = $this->request->getData('action'); 
            $selected = $this->request->getData('select') ?? [];
            $selectedIds = array_keys(array_filter($selected));
            $selectcount = count($selectedIds);
    
            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }
    
            if ($action === 'edit') {
            
                if ($selectcount === 1) {
                $id = $selectedIds[0];
                try {
                    $mfood = $mfoodsTable->find()
                        ->where(['food_id' => $id])
                        ->firstOrFail();

                    Log::debug('編集対象のMfoodデータ: ' . print_r($mfood->toArray(), true));

                    return $this->redirect(['action' => 'edit', $id]);
                            } catch (RecordNotFoundException $e) {
                                $this->Flash->error("指定された商品（ID: {$id}）は存在しません。");
                                
                            }
                        } elseif($selectcount > 1) {
                            $this->Flash->error('更新は1件のみ選択可能です。');
                            
                        } else {
                            $this->Flash->error('食材商品が選択されていません。');
                        }
                        return $this->redirect(['action' => 'index']);
            }
            
            
            //削除処理
            if ($action === 'delete') {
                if (!empty($selectedIds)) {
                    $conn = $mfoodsTable->getConnection();
                    $conn->begin();

                    try {
                        $loginUserId = $this->request->getAttribute('identity')->get('user_id');

                        //削除対象を取得
                        $foods = $mfoodsTable->find()
                            ->where(['food_id IN' => $selectedIds, 'del_flg' => 0])
                            ->all();

                        //1件でも発注があるものがあるかチェック
                        $ordersTable = $this->fetchTable('TFoodOrder');
                        foreach ($foods as $food) {
                            $hasOrder = $ordersTable->exists([
                                'food_id' => $food->food_id,
                                'del_flg' => 0
                            ]);
                            if ($hasOrder) {
                                throw new \Exception();
                            }
                        }

                        //削除処理（論理削除）
                        foreach ($foods as $food) {
                            $food->del_flg = 1;
                            $food->update_user = $loginUserId;
                            $mfoodsTable->saveOrFail($food);
                        }

                        $conn->commit();
                        $this->Flash->success('選択された商品を削除しました。');

                    } catch (\Exception $e) {
                        $conn->rollback();
                        $this->Flash->error('食材発注に登録されている為、削除できません。');
                        Log::error('[削除エラー] ' . $e->getMessage());
                    }

                } else {
                    $this->Flash->error('食材商品が選択されていません。');
                }

                return $this->redirect(['action' => 'index']);
            }

        }
}
    
public function add()
{
    $mfood = $this->Mfoods->newEmptyEntity();

     if (!$this->request->is('post')) {
        $mfood->disp_no = 0;
    }

    // カテゴリ一覧（未削除のみ）
    $mFoodCategories = $this->Mfoods->MFoodCategories
        ->find('list', keyField: 'category_id', valueField: 'category_name')
        ->where(['del_flg' => '0'])
        ->toArray();

    // 施設グループリスト
    $mUserGroup = $this->fetchTable('MUserGroup')
        ->find('list', keyField: 'user_group_id', valueField: 'user_group_name')
        ->where(['del_flg' => '0'])
        ->toArray();

    // 施設一覧（未削除のみ）
    $mUser = $this->fetchTable('MUser')
        ->find('list', keyField: 'user_id', valueField: 'user_name')
        ->where(['del_flg' => '0','use_service_id IN'=>[1,3,4]])
        ->toArray();
        
    Log::debug(print_r($mUser, true));


    // 初期表示用（POSTされていないとき）
    $selectedGroupId = null;
    $selectedUserIds = [];

    if ($this->request->is('post')) {
        $data = $this->request->getData();
        Log::debug('POSTされたデータ: ' . print_r($data, true));

        // チェックされた施設IDのみ抽出
        $selectedUsers = $data['selected_users'] ?? [];
        $selectedUserIds = array_keys(array_filter($selectedUsers, fn($v) => $v === '1'));
        $data['user_ids'] = $selectedUserIds;
        Log::debug('選択された施設IDs: ' . print_r($selectedUserIds, true));

        // グループID保持
        $selectedGroupId = $data['user_group_id'] ?? null;

        // ログインユーザー情報補完
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');
        $data['create_user'] = $loginUserId;
        $data['update_user'] = $loginUserId;
        $data['del_flg'] = 0;

        // エンティティにデータを反映（バリデーション含む）
        $mfood = $this->Mfoods->patchEntity($mfood, $data);

        // バリデーションエラー（施設未選択）
        if (empty($selectedUserIds)) {
            $mfood->setError('user_ids', ['施設が選択されていません']);
            //$this->Flash->error('施設が選択されていません');
        }

        if ($mfood->hasErrors()) {
            $errors = $mfood->getErrors();
            if (!empty($errors['user_ids'])) {
                $this->Flash->error($errors['user_ids'][0]);
            } else {
                $this->Flash->error(__('入力内容にエラーがあります。内容をご確認ください。'));
            }
        } else {
            // トランザクション開始
            $conn = $this->Mfoods->getConnection();
            $conn->begin();

            try {
                //throw new \Exception('テスト用のシステムエラー');
                // 食材商品を保存
                $this->Mfoods->saveOrFail($mfood);

                $mFoodUserTable = $this->fetchTable('MFoodUser');
                $foodId = $mfood->food_id;
                $loginUserId = $this->request->getAttribute('identity')->get('user_id');

                foreach ($selectedUserIds as $userId) {
                    $entity = $mFoodUserTable->newEntity([
                        'food_id' => $foodId,
                        'user_id' => $userId,
                        'create_user' => $loginUserId,
                        'update_user' => $loginUserId,
                    ]);

                    $mFoodUserTable->saveOrFail($entity);
                }

                $conn->commit();
                $this->Flash->success(__('登録しました。'));
                return $this->redirect(['action' => 'index']);

            } catch (PersistenceFailedException $e) {
                $conn->rollback();
                $this->Flash->error('入力内容に誤りがあり保存できませんでした。');
                Log::error('[PersistenceFailedException] ' . $e->getMessage());
            //想定外のエラーはここでキャッチ(DBエラー、SQL構文エラーなど)
            } catch (\Exception $e) {
                $conn->rollback();
                $this->Flash->error('システムエラーです。登録に失敗しました。');
                Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }
    }

    // ビューへ渡す（POSTの有無に関わらずセット）
    $this->set('selectedGroupId', $selectedGroupId);
    $this->set('selectedUserIds', $selectedUserIds);
    $this->set(compact('mfood', 'mFoodCategories', 'mUserGroup', 'mUser'));
    $this->set('mode', 'add');
    $this->render('add_edit');
}

           

public function edit($id = null)
{
    try {
        $mfood = $this->Mfoods->get($id, contain:  ['MUsers'] );//施設テーブル
        Log::debug("[edit] 編集対象取得: food_id={$id}", ['data' => $mfood->toArray()]);
    } catch (RecordNotFoundException $e) {
        $this->Flash->error('対象データが存在しません。');
        return $this->redirect(['action' => 'index']);
    }

    // 必要なリスト類
    $mFoodCategories = $this->Mfoods->MFoodCategories->find('list', 
        keyField : 'category_id',
        valueField : 'category_name'
    )->where(['del_flg' => 0])->toArray();

    $mUserGroup = $this->fetchTable('MUserGroup')->find('list', 
        keyField : 'user_group_id',
        valueField : 'user_group_name'
    )->where(['del_flg' => 0])->toArray();

    $mUser = $this->fetchTable('MUser')->find('list', 
        keyField : 'user_id',
        valueField : 'user_name'
    )->where(['del_flg' => 0])->toArray();

    if ($this->request->is(['patch', 'post', 'put'])) {
        $data = $this->request->getData();
        Log::debug('post入った');

        //ここでAjax変更後の選択グループを優先
        if (!empty($data['selected_group_id'])) {
        $data['user_group_id'] = $data['selected_group_id'];
        }

        // ログインユーザー設定
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');
        $data['update_user'] = $loginUserId;

        
        //エンティティ更新
        $mfood = $this->Mfoods->patchEntity($mfood, $data);

        // チェックされた施設IDのみ抽出
        $selectedUsers = $data['selected_users'] ?? [];
        $selectedUserIds = array_keys(array_filter($selectedUsers, fn($v) => $v === '1'));
        $data['user_ids'] = $selectedUserIds;
        Log::debug('選択された施設IDs: ' . print_r($selectedUserIds, true));


        if (empty($selectedUserIds)){
            $mfood->setError('user_ids', ['施設が選択されていません']);
            //$this->Flash->error('施設が選択されていません');
        }
        
        //通常バリデーションチェック
        if ($mfood->hasErrors()) {
            $errors = $mfood->getErrors();
            if (!empty($errors['user_ids'])) {
                $this->Flash->error($errors['user_ids'][0]);
            } else {
                $this->Flash->error('入力内容にエラーがあります。内容をご確認ください。');
            }
            
            Log::warning('[edit] バリデーションエラー: ' . print_r($mfood->getErrors(), true));

            $this->set(compact(
                'mfood',
                'mFoodCategories',
                'mUserGroup',
                'mUser',
                'selectedGroupId',
                'selectedUserIds'
            ));
            
            $this->set('mode', 'edit');
            $this->render('add_edit');
            return;
        }

        //トランザクション開始
        $conn = $this->Mfoods->getConnection();
        $conn->begin();
        try {
//            throw new \Exception('テスト用のシステムエラー');
            //食材情報保存（失敗したらPersistenceFailedExceptionが投げられる）
            $this->Mfoods->saveOrFail($mfood);
            Log::debug("[edit] 食材情報保存成功: food_id={$id}");

            // チェックされた施設一覧を配列に変換
            $selectedUserIds = [];
            foreach ($selectedUsers as $userId => $checked) {
                if ($checked === '1') {
                    $selectedUserIds[] = $userId;
                }
            }

            Log::debug('[edit] 選択施設ID', ['ids' => $selectedUserIds]);

            //差分保存ロジック
            $this->saveFoodUser($mfood->food_id, $selectedUserIds);

            $conn->commit();
            $this->Flash->success('更新しました。');
            return $this->redirect(['action' => 'index']);

        } catch (PersistenceFailedException $e) {
            $conn->rollback();
            $this->Flash->error('入力内容に誤りがあり保存できませんでした。');
            Log::error('[PersistenceFailedException] ' . $e->getMessage());

        } catch (Exception $e) {
            $conn->rollback();
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

           
    //グループIDを抽出（施設が１つ以上ある場合）
    $groupIds = collection($mfood->m_users)
    ->map(function($user){
        //user_idの先頭５桁がグループId
        return(int)substr((string)$user->user_id, 0, 5);
    })
    ->unique()
    ->toList();
    
    $selectedGroupId = $groupIds[0] ?? null;
    
    //チェック済み施設IDを抽出
    $selectedUserIds = collection($mfood->m_users)->extract('user_id')->toList();
    Log::debug('[選択済ユーザー] ' . print_r($selectedUserIds, true));


    $this->set(compact(
        'mfood',
        'mFoodCategories',
        'mUserGroup',
        'mUser',
        'selectedGroupId',
        'selectedUserIds'
    ));
    
    $this->set('mode', 'edit');
    $this->render('add_edit');
}

//add-mode用
public function ajaxUsersByGroupAdd()
{
    $this->request->allowMethod(['post']);
    $groupId = $this->request->getData('user_group_id');

    $allUsers = $this->fetchTable('MUser')->find('list', 
        keyField    : 'user_id',
        valueField  : 'user_name',
    )
    ->where([
        'del_flg' => 0,
        "user_id LIKE" => $groupId . '%'
    ])
    ->toArray();

    // add用：POSTされた selected_users を使う
    $selectedUsers = $this->request->getData('selected_users') ?? [];
    $selectedUserIds = array_keys(array_filter($selectedUsers, fn($v) => $v === '1'));

    Log::debug('ajaxUsersByGroupAdd called');
    Log::debug('user_group_id: ' . $groupId);
    Log::debug('selected_users: ' . json_encode($selectedUsers));

    $this->set(compact('allUsers', 'selectedUserIds'));
    $this->set('users', $allUsers);
    $this->set('selectedUserIds', $selectedUserIds);
    $this->viewBuilder()->disableAutoLayout();
    $this->render('ajax_user_list'); 
}
//edit-mode用
public function ajaxUsersByGroup()
{
    $this->request->allowMethod(['post']);

    $userGroupId = $this->request->getData('user_group_id');
    $foodId = $this->request->getData('food_id');

    $mUserTable = $this->fetchTable('MUser');
    
    $users = $mUserTable->find('list', 
        keyField    : 'user_id',
        valueField  : 'user_name'
    )
        ->where([
        'del_flg' => '0',
        'user_id LIKE' => $userGroupId . '%'
    ])
        ->order(['user_id' => 'ASC'])
        ->toArray();

    $selectedUserIds = [];
    if (!empty($foodId)) {
        $mFoodUserTable = $this->fetchTable('MFoodUser');
        $query = $mFoodUserTable->find()
            ->select(['user_id'])
            ->where(['food_id' => $foodId]);
        
        // ここでCollectionに変換
        $collection = new Collection($query->all());
        $selectedUserIds = $collection->extract('user_id')->toList();
    }

    Log::debug('user_group_id: ' . $this->request->getData('user_group_id'));
    Log::debug('food_id: ' . $this->request->getData('food_id'));


    $this->set(compact('users', 'selectedUserIds'));
    $this->viewBuilder()->setLayout('ajax');
    $this->render('ajax_user_list');
}




public function saveFoodUser($foodId, $selectedUserIds)
{
    $mFoodUsersTable = TableRegistry::getTableLocator()->get('MFoodUser');
    $loginUserId = $this->request->getAttribute('identity')->get('user_id');

    // 既存の関連データをすべて取得
    $existingFoodUsers = $mFoodUsersTable->find()
        ->where(['food_id' => $foodId])
        ->all() // ここで Collection に変換
        ->indexBy('user_id') // user_id でキー付け
        ->toArray();

    $newEntities = [];

    // 1. 送信された user_id をループして insert またはスキップ
    foreach ($selectedUserIds as $userId) {
        if (isset($existingFoodUsers[$userId])) {
            // 既に存在 → 何もしない
            unset($existingFoodUsers[$userId]); // 差分判定のために除外
        } else {
            // 新規作成
            $entity = $mFoodUsersTable->newEntity([
                'food_id' => $foodId,
                'user_id' => $userId,
                'create_user' => $loginUserId,
                'update_user' => $loginUserId, // 明示的にセット
            ]);
            $newEntities[] = $entity;
        }
    }

    // 2. 差分として残ったもの（未選択になった user）は削除対象
    // 保存（新規）
    if (!empty($newEntities)) {
        $mFoodUsersTable->saveMany($newEntities);
    }

    // 削除（差分として残った分）
    foreach ($existingFoodUsers as $entity) {
        $mFoodUsersTable->delete($entity);
    }
}
}