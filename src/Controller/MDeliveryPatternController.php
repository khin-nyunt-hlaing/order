<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use Exception;

class MDeliveryPatternController extends AppController
{
    /**
     * Index
     */
    public function index()
    {
        $deliveryPatternId   = $this->request->getQuery('delivery_pattern_id');
        $deliveryPatternName = $this->request->getQuery('delivery_pattern_name');
        $includeDeleted      = $this->request->getQuery('del_flg') === '1';

        $query = $this->MDeliveryPattern->find();

        if (!empty($deliveryPatternId) && ctype_digit($deliveryPatternId)) {
            $query->where(['use_pattern_id' => (int)$deliveryPatternId]);
        }

        if (!empty($deliveryPatternName)) {
            $query->where(['delivery_pattern_name LIKE' => '%' . $deliveryPatternName . '%']);
        }

        if (!$includeDeleted) {
            $query->where(['del_flg' => 0]);
        }

        $this->paginate = [
            'order' => ['use_pattern_id' => 'ASC']
        ];

        $mDeliveryPattern = $this->paginate($query);
        $count = $query->count();

        if ($this->request->is('post')) {

            $action   = $this->request->getData('action');
            $selected = array_keys(array_filter($this->request->getData('select') ?? []));

            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }

            if ($action === 'edit') {
                if (empty($selected)) {
                    $this->Flash->error('編集するデータを選択してください。');
                    return $this->redirect(['action' => 'index']);
                }
                return $this->redirect(['action' => 'edit', $selected[0]]);
            }

            if ($action === 'delete') {
                if (empty($selected)) {
                    $this->Flash->error('削除するデータを選択してください。');
                    return $this->redirect(['action' => 'index']);
                }

                $loginUserId = $this->request->getAttribute('identity')->get('user_id');

                $this->MDeliveryPattern->updateAll(
                    [
                        'del_flg'     => 1,
                        'update_user' => $loginUserId,
                    ],
                    ['use_pattern_id IN' => $selected]
                );

                $this->Flash->success('削除しました。');
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->set(compact(
            'mDeliveryPattern',
            'count',
            'deliveryPatternId',
            'deliveryPatternName',
            'includeDeleted'
        ));
    }

    /**
     * Add 完全版
     */
    public function add()
{
    $mDeliveryPattern = $this->MDeliveryPattern->newEmptyEntity();
    $mDeliveryPattern->del_flg = '0';

    // ▼ 画面表示用の「次のID」だけ計算（DBには送らない）
    $row = $this->MDeliveryPattern->find()
        ->select(['max_id' => 'MAX(use_pattern_id)'])
        ->first();
    $nextId = ($row->max_id ?? 0) + 1;
    $this->set('nextId', $nextId);   // ← Viewに渡すだけ

    // ▼ 初期表示：表示順
    if (!$this->request->is('post')) {
        $mDeliveryPattern->disp_no = 0;
    }

    // ▼ 献立商品マスタ一覧
    $mDeliveries = $this->fetchTable('MDelivery')->find('list', [
            'keyField'   => 'delivery_id',
            'valueField' => 'delivery_name',
        ])
        ->where(['del_flg' => 0])
        ->order(['delivery_id' => 'ASC'])
        ->toArray();

    // ▼ POST処理
    if ($this->request->is('post')) {

        $postData    = $this->request->getData();
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');

        // ★ 画面用のIDは無視（DBの自動採番に任せる）
        unset($postData['use_pattern_id']);
        unset($postData['use_pattern_id_view']);

        // エンティティに反映
        $mDeliveryPattern = $this->MDeliveryPattern->patchEntity($mDeliveryPattern, $postData);

        $selected = array_keys(array_filter($this->request->getData('selected_deliveries') ?? []));

        if (empty($selected)) {
            $this->Flash->error('献立商品が選択されていません。');
            $this->set('selectedIds', []);
            $this->set(compact('mDeliveryPattern', 'mDeliveries'));
            $this->set('mode', 'add');
            return $this->render('add_edit');
        }

        // 登録者情報
        $mDeliveryPattern->set([
            'create_user' => $loginUserId,
            'update_user' => $loginUserId,
            'del_flg'     => '0',
        ]);

        try {
            if ($this->MDeliveryPattern->save($mDeliveryPattern)) {

                // ★ ここで DB が採番した use_pattern_id が入る
                $patternId = $mDeliveryPattern->use_pattern_id;

                // 子テーブル保存
                $setTable = $this->fetchTable('MDeliveryPatternSet');

                foreach ($selected as $deliveryId) {
                    $set = $setTable->newEmptyEntity();
                    $set->use_pattern_id = $patternId;
                    $set->delivery_id    = $deliveryId;
                    $set->disp_no        = 0;
                    $set->del_flg        = '0';
                    $set->create_user    = $loginUserId;
                    $set->update_user    = $loginUserId;
                    $setTable->save($set);
                }

                $this->Flash->success('登録しました。');
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error('登録に失敗しました。');

        } catch (Exception $e) {
            \Cake\Log\Log::error("【ADD】例外: " . $e->getMessage());
            $this->Flash->error('システムエラーです。更新に失敗しました。');
        }
    }

    // ▼ 初期表示
    $this->set('selectedIds', []);
    $this->set(compact('mDeliveryPattern', 'mDeliveries'));
    $this->set('mode', 'add');
    $this->render('add_edit');
}

    /**
     * Edit 完全版
     */
    public function edit($id = null)
    {
        $mDeliveryPattern = $this->MDeliveryPattern->get($id);

        $loginUserId = $this->request->getAttribute('identity')->get('user_id');

        $mDeliveries = $this->fetchTable('MDelivery')->find('list', [
            'keyField'   => 'delivery_id',
            'valueField' => 'delivery_name',
        ])
            ->where(['del_flg' => 0])
            ->order(['disp_no' => 'ASC'])
            ->toArray();

        $selectedIds = $this->fetchTable('MDeliveryPatternSet')->find()
            ->select(['delivery_id'])
            ->where(['use_pattern_id' => $mDeliveryPattern->use_pattern_id, 'del_flg' => 0])
            ->enableHydration(false)
            ->all()
            ->extract('delivery_id')
            ->map(fn($v) => (string)$v)
            ->toList();

        if ($this->request->is(['post', 'put', 'patch'])) {

            $postData = $this->request->getData();
            $selected = array_keys(array_filter($this->request->getData('selected_deliveries') ?? []));

            $mDeliveryPattern = $this->MDeliveryPattern->patchEntity($mDeliveryPattern, $postData);
            $mDeliveryPattern->update_user = $loginUserId;

            if (empty($selected)) {
                $this->Flash->error('献立商品が選択されていません。');
                $this->set(compact('mDeliveryPattern', 'mDeliveries', 'selectedIds'));
                $this->set('mode', 'edit');
                return $this->render('add_edit');
            }

            try {
                if ($this->MDeliveryPattern->save($mDeliveryPattern)) {

                    $patternId = $mDeliveryPattern->use_pattern_id;

                    $setTable = $this->fetchTable('MDeliveryPatternSet');

                    // 現在の SET を取る
                    $rows = $setTable->find()
                        ->where(['use_pattern_id' => $patternId, 'del_flg' => 0])
                        ->all();

                    $existing = [];
                    foreach ($rows as $row) {
                        $existing[$row->delivery_id] = $row;
                    }

                    // 新規・更新
                    foreach ($selected as $deliveryId) {

                        if (isset($existing[$deliveryId])) {
                            $rec = $existing[$deliveryId];
                            $rec->update_user = $loginUserId;

                            if ($setTable->hasField('update_date')) {
                                $rec->update_date = date('Y-m-d H:i:s');
                            }

                            $setTable->save($rec);
                            unset($existing[$deliveryId]);

                        } else {

                            $new = $setTable->newEmptyEntity();
                            $new->use_pattern_id = $patternId;
                            $new->delivery_id    = $deliveryId;
                            $new->disp_no        = 0;
                            $new->del_flg        = '0';
                            $new->create_user    = $loginUserId;
                            $new->update_user    = $loginUserId;

                            if ($setTable->hasField('create_date')) {
                                $new->create_date = date('Y-m-d H:i:s');
                            }
                            if ($setTable->hasField('update_date')) {
                                $new->update_date = date('Y-m-d H:i:s');
                            }

                            $setTable->save($new);
                        }
                    }

                    // 削除扱い
                    foreach ($existing as $rec) {
                        $rec->del_flg = 1;
                        $rec->update_user = $loginUserId;

                        if ($setTable->hasField('update_date')) {
                            $rec->update_date = date('Y-m-d H:i:s');
                        }

                        $setTable->save($rec);
                    }

                    $this->Flash->success('更新しました。');
                    return $this->redirect(['action' => 'index']);
                }

                $this->Flash->error('更新に失敗しました。');

            } catch (Exception $e) {
                Log::error("【EDIT】例外: " . $e->getMessage());
                $this->Flash->error('システムエラーです。更新に失敗しました。');
            }
        }

        $this->set(compact('mDeliveryPattern', 'mDeliveries', 'selectedIds'));
        $this->set('mode', 'edit');
        $this->render('add_edit');
    }
}
