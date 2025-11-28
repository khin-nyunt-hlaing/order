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
        $deliveryId = $this->request->getQuery('delivery_id');
        $deliveryName = $this->request->getQuery('delivery_name');
        $includeDeleted = $this->request->getQuery('include_deleted') === '1';

        // ===== 2. クエリ生成 =======================================
        $query = $this->MDelivery->find();
        $conditions = [];

        if (!empty($deliveryId)) {
            $conditions['MDelivery.delivery_id'] = $deliveryId;
        }
        if (!empty($deliveryName)) {
            $conditions['MDelivery.delivery_name LIKE'] = '%' . $deliveryName . '%';
        }

        // 削除データ含めない
        if (!$includeDeleted) {
            $conditions['MDelivery.del_flg'] = 0;
        }

        $query->where($conditions);
        // $this->paginate = [
        //     'order' => [
        //         'MDelivery.del_flg' => 'ASC',   // 未削除 → 削除
        //         'MDelivery.disp_no' => 'ASC'    // 次に表示順
        //     ]
        // ];    

        // ===== 3. 一覧取得 =========================================
        $mDelivery = $this->paginate($query);
        $count = $query->count();

        $this->set(compact('mDelivery', 'count', 'deliveryId', 'deliveryName', 'includeDeleted'));

        // ===== 4. POST：操作処理 ===================================
        if ($this->request->is('post')) {

            $action = $this->request->getData('action');
            $selected = $this->request->getData('select') ?? [];
            $selectedIds = array_keys(array_filter($selected));
            $selectCount = count($selectedIds);

            // ==== 新規 =================================================
            if ($action === 'add') {
                return $this->redirect(['action' => 'add']);
            }

            // ==== 編集 =================================================
            if ($action === 'edit') {
                if ($selectCount === 1) {
                    return $this->redirect(['action' => 'edit', $selectedIds[0]]);
                } elseif ($selectCount === 0) {
                    $this->Flash->error('配食商品が選択されていません。');
                } else {
                    $this->Flash->error('編集は1件のみ選択可能です。');
                }
            }

            // ==== 削除 =================================================
            if ($action === 'delete') {

                if ($selectCount === 0) {
                    $this->Flash->error('配食商品が選択されていません。');
                    return $this->redirect(['action' => 'index']);
                }

                $MDelivery = $this->getTableLocator()->get('MDelivery');

                // 未削除のみ
                $deliveries = $MDelivery->find()
                    ->where(['delivery_id IN' => $selectedIds, 'del_flg' => 0])
                    ->all();

                if ($deliveries->isEmpty()) {
                    $this->Flash->error('選択された配食商品はすでに削除済みか存在しません。');
                    return $this->redirect(['action' => 'index']);
                }

                // 使用中チェック
                $tDtl = $this->fetchTable('TDeliOrderDtl');
                $tOrder = $this->fetchTable('TDeliOrder');

                $cannotDelete = [];

                foreach ($deliveries as $d) {
                    $id = (int)$d->delivery_id;

                    $hasDtl = $tDtl->exists(['delivery_id' => $id]);
                    if (!$hasDtl) continue;

                    $hasOrder = $tOrder->exists([
                        'deli_order_id IN' => $tDtl->find()
                            ->select('deli_order_id')
                            ->where(['delivery_id' => $id])
                    ]);

                    if ($hasOrder) {
                        $cannotDelete[] = $id;
                    }
                }

                if (!empty($cannotDelete)) {
                    if ($selectCount === 1) {
                        $this->Flash->error('配食商品ID: ' . $cannotDelete[0] . ' は配食発注で使用されているため、削除できません。');
                    } else {
                        $this->Flash->error('配食商品が配食発注で使用されているため、削除できません。');
                    }
                    return $this->redirect(['action' => 'index']);
                }

                // 論理削除（★ ここを修正）
                $userId = $this->request->getAttribute('identity')->get('user_id');

                $MDelivery->updateAll(
                    [
                        'del_flg' => 1,
                        'update_user' => $userId,
                        // 'update_at' => date('Y-m-d H:i:s') // ← DateTime の代わりに文字列
                    ],
                    ['delivery_id IN' => $selectedIds, 'del_flg' => 0]
                );

                $this->Flash->success('選択された配食商品を削除しました。');
                return $this->redirect(['action' => 'index']);
            }
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
