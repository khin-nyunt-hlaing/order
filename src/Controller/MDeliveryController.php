<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use \Exception;

/**
 * MDelivery Controller
 *
 * @property \App\Model\Table\MDeliveryTable $MDelivery
 */
class MDeliveryController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
{
    $showDeleted = $this->request->is('post') ? $this->getRequest()->getData('del_flg') : null;

        $deliveryQuery = $this->MDelivery->find()
            ->where($showDeleted ? [] : ['del_flg' => 0])
            ->order(['disp_no' => 'ASC']);

        $mDelivery = $this->paginate($deliveryQuery);
        $this->set(compact('mDelivery'));

        // ✅ 件数も同じ条件で
        $count = $deliveryQuery->count();
        $this->set(compact('count'));

        if ($this->request->is('post')) {
            $action = $this->request->getData('action'); 
            $selected = $this->request->getData('select') ?? [];
            $selectedIds = array_keys(array_filter($selected));
            $selectcount = count($selectedIds);
            //MUserテーブル
            $MDeliveryTable = $this->fetchTable('MDelivery');  

            // 追加処理
            if ($action === 'add') {
                    return $this->redirect(['action' => 'add']);
             
            }

            // 更新処理
            if ($action === 'edit') {
                if ($selectcount === 1) {
                    return $this->redirect(['action' => 'edit', $selectedIds[0]]);
                } elseif ($selectcount === 0) {
                    $this->Flash->error('配食商品が選択されていません。');
                } else {
                    $this->Flash->error('更新は1件のみ選択可能です。');
                }
            }

            // 削除処理
        if ($action === 'delete') {
            // 1) 選択チェック
            // ★ ここを置き換える：$selected を使わないで、必ず request から読む
            $selectedIds = [];

            // 1) select[ID] 方式（キーが ID、値が 0/1/“on”）
            $select = $this->request->getData('select');
            if (is_array($select)) {
                $selectedIds = array_map(
                    'intval',
                    array_keys(array_filter($select, function ($v) {
                        return $v === '1' || $v === 1 || $v === true || $v === 'on';
                    }))
                );
            }

            // 2) selected[] 方式（値が ID の配列）
            $selected = $this->request->getData('selected');
            if (is_array($selected)) {
                // 両方式が混在してもマージできるように追加
                $selectedIds = array_merge($selectedIds, array_map('intval', $selected));
            }

            // 重複削除
            $selectedIds = array_values(array_unique($selectedIds));

            if (empty($selectedIds)) {
                $this->Flash->error('配食商品が選択されていません。');
                return $this->redirect(['action' => 'index']);
            }

            // 2) 対象存在チェック（未削除のものだけ）
            // ※ $MDeliveryTable / $this->MDelivery の混在を解消
            $MDelivery = $this->getTableLocator()->get('MDelivery');

            $deliveries = $MDelivery->find()
                ->where(['delivery_id IN' => $selectedIds, 'del_flg' => 0])
                ->all();

            if ($deliveries->isEmpty()) {
                $this->Flash->error('選択された配食商品はすでに削除済みか存在しません。');
                return $this->redirect(['action' => 'index']);
            }

            // 3) 参照整合性チェック（明細→発注があるなら削除不可）
            $tDeliOrderDtl = $this->fetchTable('TDeliOrderDtl');
            $tDeliOrder = $this->fetchTable('TDeliOrder');

            $cannotDelete = [];
            foreach ($deliveries as $deli) {
                $deliveryId = (int)$deli->delivery_id;

                // 明細が無ければOK候補
                $hasDetail = $tDeliOrderDtl->exists(['delivery_id' => $deliveryId]);
                if (!$hasDetail) {
                    continue;
                }

                // 明細の親発注が存在するなら削除不可
                $hasOrder = $tDeliOrder->exists([
                    'deli_order_id IN' => $tDeliOrderDtl->find()
                        ->select('deli_order_id')
                        ->where(['delivery_id' => $deliveryId])
                ]);

                if ($hasOrder) {
                    $cannotDelete[] = $deliveryId;
                }
            }

            // 4) 結果分岐
            if (!empty($cannotDelete)) {
                if (count($selectedIds) === 1) {
                    // $this->Flash->error('配食商品ID: ' . $cannotDelete[0] . ' は配食発注で使用されているため、削除できません。');
                    $this->Flash->error('配食商品が配食発注で使用されているため、削除できません。');
                } else {
                    // $this->Flash->error('削除できない配食商品が含まれています。（ID: ' . implode(', ', $cannotDelete) . '）');
                    $this->Flash->error('配食商品が配食発注で使用されているため、削除できません。');
                }
                return $this->redirect(['action' => 'index']);
            }

            // 5) 論理削除（まとめて updateAll が高速）
            $userId = $this->request->getAttribute('identity')->get('user_id');
            $MDelivery->updateAll(
                ['del_flg' => 1, 'update_user' => $userId],
                ['delivery_id IN' => $selectedIds, 'del_flg' => 0]
            );

            $this->Flash->success('選択された配食商品を削除しました。');
            return $this->redirect(['action' => 'index']);
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
    $mDelivery = $this->MDelivery->newEmptyEntity();

     if (!$this->request->is('post')) {
        $mDelivery->disp_no = 0;
    }

    if (!$this->request->is('post')) {
        $maxId = $this->MDelivery->find()
            ->select(['max_id' => 'MAX(delivery_id)'])
            ->first()
            ->max_id;

        $mDelivery->delivery_id = (string)(isset($maxId) ? $maxId + 1 : 0);
        Log::debug('🟢 セットされた次のdelivery_id: ' . $mDelivery->delivery_id);
    }

    if ($this->request->is('post')) {
        $mDelivery = $this->MDelivery->patchEntity($mDelivery, $this->request->getData());
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');
        $mDelivery->del_flg = "0";
        $mDelivery->create_user = $loginUserId;
        $mDelivery->update_user = $loginUserId;

        try {
            if ($this->MDelivery->save($mDelivery)) {
                $this->Flash->success(__('登録しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('登録に失敗しました。'));
        } catch (Exception $e) {
            $this->Flash->error('システムエラーです。登録に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    $this->set(compact('mDelivery'));
    $this->set('mode', 'add');
    $this->render('add_edit');
}



    /**
     * Edit method
     *
     * @param string|null $id M Delivery id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
public function edit($id = null)
{
    $mDelivery = $this->MDelivery->get($id);

    if ($this->request->is(['post', 'put', 'patch'])) {
        try{
//throw new Exception();
            $mDelivery = $this->MDelivery->patchEntity($mDelivery, $this->request->getData());

            $loginUserId = $this->request->getAttribute('identity')->get('user_id');
            $mDelivery->update_user = $loginUserId;

            if ($this->MDelivery->save($mDelivery)) {
                $this->Flash->success(__('更新しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('更新に失敗しました。'));
        } catch (Exception $e){
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
    Log::debug('✅ edit対象のデータ: ' . print_r($mDelivery->toArray(), true));

    $this->set(compact('mDelivery'));
    $this->set('mode', 'edit');
    $this->render('add_edit'); // ← 共通テンプレートを呼び出す
}


}
