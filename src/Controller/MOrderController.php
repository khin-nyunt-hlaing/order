<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * MOrder Controller
 *
 * @property \App\Model\Table\MOrderTable $MOrder
 */
class MOrderController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->MOrder->find();
        $mOrder = $this->paginate($query);

        $this->set(compact('mOrder'));
    }

    /**
     * View method
     *
     * @param string|null $id M Order id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mOrder = $this->MOrder->get($id, contain: []);
        $this->set(compact('mOrder'));
    }
            // /**
                //  * Add method
                //  *
                //  * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
                //  */
                // public function add()
                // {
                //     $mOrder = $this->MOrder->newEmptyEntity();
                //     if ($this->request->is('post')) {
                //         $mOrder = $this->MOrder->patchEntity($mOrder, $this->request->getData());
                //         if ($this->MOrder->save($mOrder)) {
                //             $this->Flash->success(__('The m order has been saved.'));

                //             return $this->redirect(['action' => 'index']);
                //         }
                //         $this->Flash->error(__('The m order could not be saved. Please, try again.'));
                //     }
                //     $this->set(compact('mOrder'));
                // }

                // /**
                //  * Edit method
                //  *
                //  * @param string|null $id M Order id.
                //  * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
                //  * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
                //  */
                // public function edit($id = null)
                // {
                //     $mOrder = $this->MOrder->get($id, contain: []);
                //     if ($this->request->is(['patch', 'post', 'put'])) {
                //         $mOrder = $this->MOrder->patchEntity($mOrder, $this->request->getData());
                //         if ($this->MOrder->save($mOrder)) {
                //             $this->Flash->success(__('The m order has been saved.'));

                //             return $this->redirect(['action' => 'index']);
                //         }
                //         $this->Flash->error(__('The m order could not be saved. Please, try again.'));
                //     }
                //     $this->set(compact('mOrder'));
                // }

                // /**
                //  * Delete method
                //  *
                //  * @param string|null $id M Order id.
                //  * @return \Cake\Http\Response|null Redirects to index.
                //  * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
                //  */
                // public function delete($id = null)
                // {
                //     $this->request->allowMethod(['post', 'delete']);
                //     $mOrder = $this->MOrder->get($id);
                //     if ($this->MOrder->delete($mOrder)) {
                //         $this->Flash->success(__('The m order has been deleted.'));
                //     } else {
                //         $this->Flash->error(__('The m order could not be deleted. Please, try again.'));
                //     }

                //     return $this->redirect(['action' => 'index']);
            // }
    
}
