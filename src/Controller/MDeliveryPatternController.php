<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log; // ファイル冒頭で未記載なら追加
use \Exception;

/**
 * MDeliveryPattern Controller
 *
 * @property \App\Model\Table\MDeliveryPatternTable $MDeliveryPattern
 */
class MDeliveryPatternController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $showDeleted = $this->request->is('post') ? $this->getRequest()->getData('del_flg') : null;


        $deliveryPatternQuery = $this->MDeliveryPattern->find()
            ->where($showDeleted ? [] : ['del_flg' => 0])
            ->order(['disp_no' => 'ASC']);

        $mDeliveryPattern = $this->paginate($deliveryPatternQuery);
        $this->set(compact('mDeliveryPattern'));

        // ✅ 件数も同じ条件で
        $count = $deliveryPatternQuery->count();
        $this->set(compact('count'));

            $this->set(compact('mDeliveryPattern'));
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
                    $this->Flash->error('配食商品パターンが選択されていません。');
                } else {
                    $this->Flash->error('更新は1件のみ選択可能です。');
                }
            }

            // 削除処理
            if ($action === 'delete') {
                if (!empty($selectedIds)) {
                    $deliveries = $this->MDeliveryPattern->find()
                            ->where(['use_pattern_id IN' => $selectedIds, 'del_flg' => 0])
                            ->all();
                        foreach ($deliveries as $delivery) {
                        $delivery->del_flg = 1;
                        $delivery->update_user = $this->request->getAttribute('identity')->get('user_id');
                        $this->MDeliveryPattern->save($delivery);
                    }

                    $this->Flash->success('選択された商品を削除しました。');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('配食商品パターンが選択されていません。');
                }
            }
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
   // MDeliveryPatternController.php の add()
public function add()
{
    $mDeliveryPattern = $this->MDeliveryPattern->newEmptyEntity();
    $mDeliveryPattern->del_flg = '0';

    if (!$this->request->is('post')) {
        $mDeliveryPattern->disp_no = 0;
    }
    $mDeliveries = $this->fetchTable('MDelivery')->find('list', 
            keyField : 'delivery_id',
            valueField : 'delivery_name'
        )->where(['del_flg' => 0])
        ->order(['disp_no' => 'ASC'])
        ->toArray();

    if ($this->request->is('post')) {
        $postData = $this->request->getData();
        $loginUserId = $this->request->getAttribute('identity')->get('user_id'); // ★ ログインユーザーのID

        $mDeliveryPattern = $this->MDeliveryPattern->patchEntity($mDeliveryPattern, $postData);
        $selected = array_keys(array_filter($this->request->getData('selected_deliveries') ?? []));
        $deliverySetTable = $this->fetchTable('MDeliveryPatternSet');

        $mDeliveryPattern->set([
            'create_user' => $loginUserId,
            'update_user' => $loginUserId,
            'del_flg'        => '0',
        ]);

        if (empty($selected)) {
            $this->Flash->error('配食商品が選択されていません。');

            $this->set('mDeliveries', $this->request->getData(),'mDeliveries');
            $this->set(compact('mDeliveryPattern', 'mDeliveries'));
            $this->set('mode', 'add');
            return $this->render('add_edit'); // リダイレクトしない → 値保持
        }

        try{
//throw new Exception();
                $selected = array_values(array_filter((array)$this->request->getData('selected_deliveries')));

                if (empty($selected)) {
                    $this->Flash->warning('配送を選択してください。');
                    return $this->redirect($this->request->referer());
                }

                $deliverySetTable = $this->fetchTable('MDeliveryPatternSet');

            if ($this->MDeliveryPattern->save($mDeliveryPattern)) {
                 $patternId = $mDeliveryPattern->use_pattern_id;

                foreach ($selected as $deliveryId) {
                    $setEntity = $deliverySetTable->newEmptyEntity();
                    $setEntity->use_pattern_id = $patternId;
                    $setEntity->delivery_id = $deliveryId;
                    $setEntity->disp_no = 0; // 並び順が必要なら別途処理
                    $setEntity->del_flg = '0';
                    $setEntity->create_user = $loginUserId;
                    $setEntity->update_user = $loginUserId;

                    if (!$deliverySetTable->save($setEntity)) {
                        Log::error("❌ 配食パターンセット保存失敗: " . print_r($setEntity->getErrors(), true));
                    }
                }

                $this->Flash->success('登録しました。');
                Log::debug('✅ 登録成功');
                return $this->redirect(['action' => 'index']);
            } else{
                $selectedIds = (array)($this->request->getData('selected_deliveries') ?? []);
                $this->set('selectedIds', $selectedIds); 

                Log::debug('❌ 登録失敗: ' . print_r($mDeliveryPattern->getErrors(), true));
                $this->Flash->error('登録に失敗しました。');
                $this->set(compact('mDeliveryPattern', 'mDeliveries'));
                $this->set('mode', 'add');
                return $this->render('add_edit');
            }
        } catch (Exception $e){
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

    }

    $mDeliveryPattern->disp_no = 0;

    $this->set(compact('mDeliveryPattern', 'mDeliveries'));
    $this->set('mode', 'add');
    $this->render('add_edit');
}
    /**
     * Edit method
     *
     * @param string|null $id M Delivery Pattern id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
{
    $mDeliveryPattern = $this->MDeliveryPattern->get($id);

    $loginUserId = $this->request->getAttribute('identity')->get('user_id');
    Log::debug("🟢 ログインユーザー: {$loginUserId}");

    // 親①: チェックボックスの選択肢
    $mDeliveries = $this->fetchTable('MDelivery')->find('list', 
            keyField : 'delivery_id',
            valueField : 'delivery_name'
        )
        ->where(['del_flg' => 0])
        ->order(['disp_no' => 'ASC'])
        ->toArray();

    $selectedIds = $this->fetchTable('MDeliveryPatternSet')->find()
        ->select(['delivery_id'])
        ->where([
            'use_pattern_id' => $mDeliveryPattern->use_pattern_id,
            'del_flg' => '0',
        ])
        ->enableHydration(false)          // CakePHP 5
        ->all()
        ->extract('delivery_id')
        ->map(fn($v) => (string)$v)       // 型そろえ（ビューの in_array 厳密比較用）
        ->toList();

            // $selectedIds を作った直後
        Log::debug('[EDIT GET] selectedIds=' . json_encode($selectedIds, JSON_UNESCAPED_UNICODE));
        Log::debug('[EDIT GET] selectedIds(types)=' . json_encode(array_map(
            fn($v) => gettype($v) . ':' . $v, 
            (array)$selectedIds
        ), JSON_UNESCAPED_UNICODE));

        // 候補側のキー（存在確認用）
        Log::debug('[EDIT GET] deliveryOptions.keys=' . json_encode(
            array_map('strval', array_keys($mDeliveries)), JSON_UNESCAPED_UNICODE
        ));

    if ($this->request->is(['post', 'put', 'patch'])) {
            $mDeliveryPattern = $this->MDeliveryPattern->get($id);
            $postData = $this->request->getData();
            $selected = array_keys(array_filter($this->request->getData('selected_deliveries') ?? []));

            // 子テーブル（MDeliveryPatternSet）から、このパターンで選択済みの delivery_id を配列で取得
            $selectedIds = $this->fetchTable('MDeliveryPatternSet')->find()
                ->select(['delivery_id'])
                ->where([
                    'use_pattern_id' => $mDeliveryPattern->use_pattern_id,
                    'del_flg' => '0'
                ])
                ->disableHydration() // CakePHP4: オブジェクトではなく配列/スカラーで欲しい時
                ->all()
                ->extract('delivery_id')
                ->toList();

                $selectedCurrent = array_map('strval', (array)($this->request->getData('selected_deliveries') ?? []));

                // ← ★ フォーム再描画用にエンティティへ“見せ値”をセット
                $mDeliveryPattern->set('selected_deliveries', $selectedCurrent);

                    $loginUserId = $this->request->getAttribute('identity')->get('user_id'); // ★ ログインユーザーのID
                    $mDeliveryPattern = $this->MDeliveryPattern->patchEntity($mDeliveryPattern, $postData);
                    $inputselected = array_keys(array_filter($this->request->getData('selected_deliveries') ?? []));

                    $mDeliveryPattern->set([
                        'create_user' => $loginUserId,
                        'update_user' => $loginUserId,
                    ]);

                    $mDeliveries = $this->fetchTable('MDelivery')->find('list', 
                        keyField : 'delivery_id',
                        valueField : 'delivery_name'
                        )->where(['del_flg' => 0])
                        ->order(['disp_no' => 'ASC'])
                    ->toArray();
            


            if (empty($inputselected)) {
                    $this->Flash->error('配食商品が選択されていません。');

                    
                    $this->set('mDeliveries', $this->request->getData(),'mDeliveries');
                    $this->set(compact('mDeliveryPattern', 'mDeliveries','selectedIds'));
                    $this->set('mode', 'edit');
                    return $this->render('add_edit'); // リダイレクトしない → 値保持
                }

        try{
//throw new Exception();
            // 🔻 追加登録処理：M_DELIVERY_PATTERN_SET にチェックされた delivery_id を保存
            $selected = array_values(array_filter((array)$this->request->getData('selected_deliveries')));
            //ビューから受け取る

            // 1) 先に patternId を決める（既存編集なのでここで取得可能）
                $patternId =
                    ($mDeliveryPattern->use_pattern_id ?? null) // エンティティに既に入っているなら最優先
                    ?? (int)$this->request->getData('use_pattern_id') // フォームに hidden 等で来ているなら
                    ?? (int)$this->request->getParam('pass.0')       // /edit/{id} などURLから
                    ?? null;

            if (!$patternId) {
                throw new \RuntimeException('patternId を取得できません（既存編集）。');
            }

            // 2) 関連テーブル用意
            $setTable = $this->fetchTable('MDeliveryPatternSet');



            if ($this->MDeliveryPattern->save($mDeliveryPattern)) {
                
            // 既存を delivery_id をキーにして取得（pattern_id で絞る想定）
            $rows = $setTable->find()
                ->where(['use_pattern_id' => $patternId, 'del_flg' => '0'])
                ->all();

            $existing = [];
            foreach ($rows as $row) {
                $existing[$row->delivery_id] = $row;
            }

                
                Log::debug('patternId=' . var_export($patternId, true));
                Log::debug('setTable=' . get_class($setTable));


                foreach ($selected as $deliveryId) {
                    if (isset($existing[$deliveryId])) {
                        $record = $existing[$deliveryId];
                        $record->update_user = $loginUserId;
                        $setTable->save($record);
                        unset($existing[$deliveryId]);
                    } else {
                        $new = $setTable->newEmptyEntity();
                        $new->use_pattern_id = $patternId;
                        $new->delivery_id = $deliveryId;
                        $new->disp_no = 0;
                        $new->del_flg = '0';
                        $new->create_user = $loginUserId;
                        $new->update_user = $loginUserId;
                        $setTable->save($new);
                    }
                }

                foreach ($existing as $deliveryId => $record) {
                     $record->del_flg = '1'; 
                     $record->update_user = $loginUserId; 
                     $setTable->save($record); 
                    }

                $this->Flash->success('更新しました。');
                return $this->redirect(['action' => 'index']);
            } else{
                // 🔴 保存失敗時
                Log::debug('❌ 更新失敗: ' . print_r($mDeliveryPattern->getErrors(), true));
                $this->Flash->error('更新に失敗しました。');

            }
        } catch (Exception $e){
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    $this->set(compact('mDeliveryPattern', 'mDeliveries','selectedIds'));
    $this->set('mode', 'edit');
    $this->render('add_edit');
}


}
