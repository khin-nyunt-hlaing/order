<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log; 
use \Exception;

/**
 * システム設定コントローラー   MSystemSetting Controller
 *
 * @property \App\Model\Table\MSystemSettingTable $MSystemSetting
 */
class MSystemSettingController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function edit()
{
    $id = '1'; // 固定主キー
    $MSystemSetting = $this->MSystemSetting->get($id);
    Log::write('debug', print_r($MSystemSetting->toArray(), true));
    
    // 初期値0
    if ($MSystemSetting->deli_min_chk === null) {
            $MSystemSetting->deli_min_chk = 0;
        } 
        
        if ($MSystemSetting->deli_chg_chk === null) {
            $MSystemSetting->deli_chg_chk = 0;
        }
        
        if ($MSystemSetting->deli_req_chk === null) {
            $MSystemSetting->deli_req_chk = 0;
    }

    $userId = $this->Authentication->getIdentity()->get('user_id');

    try{
            if ($this->request->is(['post', 'put'])) {
                $this->MSystemSetting->patchEntity($MSystemSetting, $this->request->getData());

                $MSystemSetting->update_user = $userId;

                if ($this->MSystemSetting->save($MSystemSetting)) {
                    $this->Flash->success('設定を保存しました。');
                    return $this->redirect(['action' => 'edit']);
                }
                $this->Flash->error('保存に失敗しました。もう一度お試しください。');
            }
            $this->set(compact('MSystemSetting'));

        } catch (Exception $e){
            $this->Flash->error('システムエラーです。登録に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->redirect(['action' => 'edit']);
        } finally{
        }
    }
}
