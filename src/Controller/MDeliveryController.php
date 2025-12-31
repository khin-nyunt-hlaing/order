<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use Exception;

class MDeliveryController extends AppController
{
    /**
     * Index
     */
    public function index()
{
    // ===== 1. 検索条件 ========================================
    $deliveryId     = $this->request->getQuery('delivery_id');
    $deliveryName   = $this->request->getQuery('delivery_name');
    $includeDeleted = !empty($this->request->getQuery('include_deleted'));

    // ===== 2. クエリ生成 =======================================
    $query = $this->MDelivery->find();
    $conditions = [];

    if ($deliveryId !== null && $deliveryId !== '') {
        $conditions['MDelivery.delivery_id'] = $deliveryId;
    }

    if ($deliveryName !== null && $deliveryName !== '') {
        $conditions['MDelivery.delivery_name LIKE'] = '%' . $deliveryName . '%';
    }

    // 削除データを含めない（デフォルト）
    if (!$includeDeleted) {
        $query->where(['MDelivery.del_flg' => 0]);
    }

    $query->where($conditions);

    // ===== 3. 件数取得（paginate前に clone）====================
    $count = (clone $query)->count();

    // ===== 4. 一覧取得（paginate）=============================
    $mDelivery = $this->paginate($query);

    $this->set(compact(
        'mDelivery',
        'count',
        'deliveryId',
        'deliveryName',
        'includeDeleted'
    ));

    // ===== 5. POST：操作処理 ===================================
    if (!$this->request->is('post')) {
        return;
    }

    $action        = $this->request->getData('action');
    $selected      = $this->request->getData('select') ?? [];
    $selectedIds   = array_keys(array_filter($selected));
    $selectCount   = count($selectedIds);

    // ==== 新規 =================================================
    if ($action === 'add') {
        return $this->redirect(['action' => 'add']);
    }

    // ==== 編集 =================================================
    if ($action === 'edit') {
        if ($selectCount === 1) {
            return $this->redirect(['action' => 'edit', $selectedIds[0]]);
        }
        $this->Flash->error(
            $selectCount === 0
                ? '配食商品が選択されていません。'
                : '編集は1件のみ選択可能です。'
        );
        return;
    }

    // ==== 削除 =================================================
    if ($action === 'delete') {

        if ($selectCount === 0) {
            $this->Flash->error('配食商品が選択されていません。');
            return $this->redirect(['action' => 'index']);
        }

        $MDelivery = $this->fetchTable('MDelivery');

        $deliveries = $MDelivery->find()
            ->where(['delivery_id IN' => $selectedIds, 'del_flg' => 0])
            ->all();

        if ($deliveries->isEmpty()) {
            $this->Flash->error('選択された配食商品はすでに削除済みか存在しません。');
            return $this->redirect(['action' => 'index']);
        }

        // 使用中チェック
        $tDtl   = $this->fetchTable('TDeliOrderDtl');
        $tOrder = $this->fetchTable('TDeliOrder');

        foreach ($deliveries as $d) {
            if ($tDtl->exists(['delivery_id' => $d->delivery_id])) {
                $this->Flash->error('配食商品が配食発注で使用されているため、削除できません。');
                return $this->redirect(['action' => 'index']);
            }
        }

        // 論理削除
        $userId = $this->request->getAttribute('identity')->get('user_id');

        $MDelivery->updateAll(
            [
                'del_flg'      => 1,
                'update_user'  => $userId,
            ],
            ['delivery_id IN' => $selectedIds, 'del_flg' => 0]
        );

        $this->Flash->success('選択された配食商品を削除しました。');
        return $this->redirect(['action' => 'index']);
    }
}


    /**
     * Add
     */
    public function add()
    {
        $mDelivery = $this->MDelivery->newEmptyEntity();

        if (!$this->request->is('post')) {
            $mDelivery->disp_no = 0;

            $maxId = $this->MDelivery->find()
                ->select(['max_id' => 'MAX(delivery_id)'])
                ->first()
                ->max_id;

            $mDelivery->delivery_id = (string)(isset($maxId) ? $maxId + 1 : 0);
        }

        if ($this->request->is('post')) {
            $mDelivery = $this->MDelivery->patchEntity($mDelivery, $this->request->getData());
            $uid = $this->request->getAttribute('identity')->get('user_id');

            $mDelivery->del_flg = "0";
            $mDelivery->create_user = $uid;
            $mDelivery->update_user = $uid;

            $mDelivery->create_at = new \DateTime();
            $mDelivery->update_at = new \DateTime();

            if ($this->MDelivery->save($mDelivery)) {
                $this->Flash->success('登録しました。');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('登録に失敗しました。');
        }

        $this->set(compact('mDelivery'));
        $this->set('mode', 'add');
        $this->render('add_edit');
    }

    /**
     * Edit
     */
    public function edit($id = null)
    {
        $mDelivery = $this->MDelivery->get($id);

        if ($this->request->is(['post', 'put', 'patch'])) {

            $mDelivery = $this->MDelivery->patchEntity($mDelivery, $this->request->getData());
            $uid = $this->request->getAttribute('identity')->get('user_id');

            $mDelivery->update_user = $uid;
            $mDelivery->update_at = new \DateTime();

            if ($this->MDelivery->save($mDelivery)) {
                $this->Flash->success('更新しました。');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('更新に失敗しました。');
        }

        $this->set(compact('mDelivery'));
        $this->set('mode', 'edit');
        $this->render('add_edit');
    }
}
