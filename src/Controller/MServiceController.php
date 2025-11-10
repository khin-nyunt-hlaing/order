<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use \Exception;

/**
 * MService Controller
 *
 * @property \App\Model\Table\MServiceTable $MService
 */
class MServiceController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        // 必須コンポーネント
        $this->loadComponent('Flash');
    }

    public function index()
    {
        if ($this->request->is('post')) {
            $action = $this->request->getData('action');
            $selected = $this->request->getData('select');

            if ($action === 'edit') {
                $selected = array_keys(array_filter($this->request->getData('select')));

                $selectcount = count($selected);

                if ($selectcount === 1) {
                    $id = $selected[0];

                    try {
                        $this->MService->find()
                        ->where(['use_service_id' => $id, 'del_flg' => '0'])
                        ->firstOrFail();

                        return $this->redirect(['action' => 'edit', $id]);
                    } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                        $this->Flash->error("指定されたサービス（ID: {$id}）は存在しません。");
                    }
                } elseif ($selectcount > 1) {
                    $this->Flash->error('更新は1件のみ選択可能です。');
                } else {
                    $this->Flash->error('発注サービスが選択されていません。');
                }
            }

            if ($action === 'delete') {
        $selected = array_keys(array_filter($this->request->getData('select')));

        if (!empty($selected)) {
            $services = $this->MService->find()
                ->where(['use_service_id IN' => $selected, 'del_flg' => 0])
                ->all();

            foreach ($services as $service) {
                $service->del_flg = 1;
                $this->MService->save($service);
            }

            $this->Flash->success('選択されたサービスを削除しました。');
        } else {
            $this->Flash->error('削除するサービスを選択してください。');
                }
            }
        }

        $query = $this->MService->find('all')
            ->where(['MService.del_flg' => '0'])
            ->order(['disp_no' => 'ASC']); // ← 並び順追加

        $mservices = $this->paginate($query);

        $this->set(compact('mservices'));
    }


    public function edit($id = null)
    {
        try {
            // 該当データを取得（主キーは use_service_id）
            $MService = $this->MService->find()
                ->where(['use_service_id' => $id, 'del_flg' => '0'])
                ->firstOrFail();
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error("指定されたサービス（ID: {$id}）は存在しません。");
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            try{
                $data = $this->request->getData();
                $loginUserId = $this->request->getAttribute('identity')->get('user_id');

                $MService = $this->MService->patchEntity($MService, $data);
                $MService->update_user = $loginUserId;

                if ($this->MService->save($MService)) {
                    $this->Flash->success('更新しました。');
                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error('更新に失敗しました。');
                }
            } catch (Exception $e) {    
                $this->Flash->error('システムエラーです。更新に失敗しました。');
                Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
                
        }

        Log::debug('🟡 編集対象のMService: ' . print_r($MService->toArray(), true));
        $this->set(compact('MService'));
    }

}
