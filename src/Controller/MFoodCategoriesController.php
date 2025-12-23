<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Log\Log;
use \Exception;

/**
 * 食材分類コントローラー   MFoodCategories Controller
 *
 * @property \App\Model\Table\MFoodCategoriesTable $MFoodCategories
 */
class MFoodCategoriesController extends AppController
{
    public function index()
    {
        /* =========================
     * 検索条件（GET）
     * ========================= */
        $q = $this->request->getQuery();
        $conditions = [];

        // 削除データを含める
        $showDeleted = ($q['del_flg'] ?? '') === '1';
        if (!$showDeleted) {
            $conditions['MFoodCategories.del_flg'] = 0;
        }

        // 分類ID（完全一致）
        if (!empty($q['category_id'])) {
            $conditions['MFoodCategories.category_id'] = $q['category_id'];
        }

        // 分類名称（部分一致）
        if (!empty($q['category_name'])) {
            $conditions['MFoodCategories.category_name LIKE']
                = '%' . $q['category_name'] . '%';
        }
        /* =========================
        * 一覧取得
        * ========================= */
        $MFoodCategories = $this->MFoodCategories->find()
            ->where($conditions)
            ->order(['DISP_NO' => 'ASC']);

        $mFoodCategories = $this->paginate($MFoodCategories);

        // ✅ 件数も同じ条件で
        $count = $MFoodCategories->count();

        $this->set(compact(
            'mFoodCategories',
            'count',
            'q',
            'showDeleted'
        ));
       

        if ($this->request->is('post')) {
            $action = $this->request->getData('action');
            $selected = $this->request->getData('select') ?? [];
            $selectedIds = array_keys(array_filter($selected));
            $selectcount = count($selectedIds);

            $selectID = array_keys(array_filter($this->request->getData('select') ?? []));

            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }

             // 更新処理
            if ($action === 'edit') {
                   if ($selectcount === 1) {
                        return $this->redirect(['action' => 'edit', $selectedIds[0]]);
                   } elseif ($selectcount === 0) {
                        $this->Flash->error('食材分類が選択されていません。');
                   } else {
                        $this->Flash->error('更新は1件のみ選択可能です。');
                   }
            }

           
                
            // 削除
            if ($action === 'delete') {
                 if ($selectcount === 0) {
                $this->Flash->error('食材分類が選択されていません。');
                return $this->redirect(['action' => 'index']);
                }
                if (empty($selectedIds)) {
                        $this->Flash->error('データが選択されていません。');
                        return $this->redirect(['action' => 'index']);
                    }

                    $MFoodCategoryTable = $this->fetchTable('MFoodCategories');
                    $foodcategoryselect = $MFoodCategoryTable->find()->where(['category_id IN' => $selectedIds, 'del_flg' => 0])->all();
                    
                    $MFoods = $this->fetchTable('MFoods');

                    if ($foodcategoryselect->isEmpty()) {
                        $this->Flash->error('選択されたカテゴリは既に削除済みか存在しません。');
                        return $this->redirect(['action' => 'index']);
                    }

                    $cannotDelete = [];

                    foreach ($foodcategoryselect as $cat) {
                        $cateId = $cat->category_id;

                        $inFoods = $MFoods->exists(['category_id' => $cateId]);

                        if ($inFoods) {
                            $cannotDelete[] = [
                                'category_id' => $cateId,
                                'inFoods'     => $inFoods,
                            ];
                        }
                        Log::debug($cat);

                        // 特定のプロパティ確認
                        Log::debug('category_id=' . ($cat->category_id ?? 'null'));
                        Log::debug('id=' . ($cat->food_id ?? 'null'));
                    }

                    // 判定
                    if (!empty($cannotDelete)) {
                        if (count($selectedIds) === 1) {
                            $this->Flash->error('食材商品マスタで使用されている為、削除できません。');
                            return $this->redirect(['action' => 'index']);
                        } else {
                            $ids = array_column($cannotDelete, 'category_id');
                            // $this->Flash->error('削除できないカテゴリが含まれています。（ID: ' . implode(', ', $ids) . '）');
                            $this->Flash->error('食材商品マスタで使用されている為、削除できません。');
                            return $this->redirect(['action' => 'index']);
                        }
                    }

                    // 全て削除可能 → 論理削除を実行
                    $identity = $this->request->getAttribute('identity');
                    $updateUserId = $identity ? $identity->get('user_id') : null;

                    $conn = $MFoodCategoryTable->getConnection();
                    $conn->begin();
                    try {
                    foreach ($foodcategoryselect as $cat) {
                            $cat->del_flg = 1;
                            $cat->update_user = $updateUserId;
                            if (!$MFoodCategoryTable->save($cat)) {
                                throw new \RuntimeException(json_encode($cat->getErrors(), JSON_UNESCAPED_UNICODE));
                            }
                        }
                        $conn->commit();
                        $this->Flash->success('削除しました。');
                } catch (\Throwable $e) {
                        $conn->rollback();
                        \Cake\Log\Log::error('削除失敗: ' . $e->getMessage());
                        $this->Flash->error('削除に失敗しました。');
                }
                return $this->redirect(['action' => 'index']);
            }

        }
    }

    public function add()
{
    $mFoodCategory = $this->MFoodCategories->newEmptyEntity();

    // =========================
    // 新規表示用：次の分類ID（MAX + 1）
    // ※ 表示専用（DBには保存しない）
    // =========================
    $nextCategoryId = $this->MFoodCategories
        ->find()
        ->select(['max' => 'MAX(category_id)'])
        ->first()
        ->max ?? 0;
    $nextCategoryId++;

    // 初期表示時のデフォルト
    if (!$this->request->is('post')) {
        $mFoodCategory->disp_no = 0;
    }

    try {
    if ($this->request->is('post')) {

        $mFoodCategory = $this->MFoodCategories->patchEntity(
            $mFoodCategory,
            $this->request->getData()
        );

        $loginUserId = $this->request
            ->getAttribute('identity')
            ->get('user_id');

        $mFoodCategory->del_flg     = 0;
        $mFoodCategory->create_user = $loginUserId;
        $mFoodCategory->update_user = $loginUserId;

        if ($this->MFoodCategories->save($mFoodCategory)) {

            // ✅ 成功
            $this->Flash->success(__('登録しました。'));
            return $this->redirect(['action' => 'index']);

        } else {

            // ❌ 保存失敗（バリデーション等）
            Log::debug('登録失敗: ' . print_r($mFoodCategory->getErrors(), true));
            $this->Flash->error(__('登録に失敗しました。'));
        }
    }

} catch (Exception $e) {

    // ❌ システムエラー
    $this->Flash->error('システムエラーです。登録に失敗しました。');
    Log::error(
        '[システムエラー] ' . $e->getMessage(),
        ['trace' => $e->getTraceAsString()]
    );
}

    $this->set(compact('mFoodCategory', 'nextCategoryId'));
    $this->set('mode', 'add');
    $this->render('add_edit');
}


 


    public function edit($id = null)
    {
        try{
                $mFoodCategory = $this->MFoodCategories->get($id);
                Log::debug('🟡 edit用エンティティ: ' . print_r($mFoodCategory->toArray(), true));

                $loginUserId = $this->request->getAttribute('identity')->get('user_id');
                $mFoodCategory->update_user = $loginUserId;

                if ($this->request->is(['post', 'put', 'patch'])) {
                    $postData = $this->request->getData();
                    $loginUserId = $this->request->getAttribute('identity')->get('user_id'); // ★ ログインユーザーのID

                Log::debug('✅ フォーム受信値: ' . print_r($postData, true));

                    $mFoodCategory = $this->MFoodCategories->patchEntity($mFoodCategory,$postData);
                    
                    // 1) エラー確認（これで弾かれていれば値は入らない）
                    // debug($mFoodCategory->getErrors());

                    // // 2) そのフィールドに反映トライがあったか
                    // debug($mFoodCategory->isDirty('disp_no')); // true なら当てにいっている

                    Log::debug('✅ patch後のエンティティ: ' . print_r($mFoodCategory->toArray(), true));

                    $mFoodCategory->set([
                        'update_user' => $loginUserId,
                    ]);
                    

                    if ($this->MFoodCategories->save($mFoodCategory)) {
                        $this->Flash->success(__('更新しました。'));
                        return $this->redirect(['action' => 'index']);
                    } else {
                        Log::debug('❌ 登録失敗: ' . print_r($mFoodCategory->getErrors(), true));

                        $this->Flash->error(__('更新に失敗しました。'));
                        // $this->set(compact('mFoodCategory'));
                        // $this->set('mode', 'edit');
                        // $this->render('add_edit');
                    }
                }

            } catch (Exception $e){
                $this->Flash->error('システムエラーです。更新に失敗しました。');
                Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }


        $this->set(compact('mFoodCategory'));
        $this->set('mode', 'edit');
        $this->render('add_edit');
    }
}
