<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Log\Log;
use \Exception;

/**
 * MAnnounceDiv Controller
 *
 * @property \App\Model\Table\MAnnounceDivTable $MAnnounceDiv
 */
class MAnnounceDivController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $showDeleted = $this->request->is('post') ? $this->getRequest()->getData('del_flg') : null;

        $announcedivQuery = $this->MAnnounceDiv->find()
            ->where($showDeleted ? [] : ['del_flg' => 0])
            ->order(['disp_no' => 'ASC']);

        $mAnnounceDiv = $this->paginate($announcedivQuery);
        $this->set(compact('mAnnounceDiv'));
        // :チェックマーク_緑: 件数も同じ条件で
        $count = $announcedivQuery->count();
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
                    $this->Flash->error('お知らせ区分が選択されていません。');
                } else {
                    $this->Flash->error('更新は1件のみ選択可能です。');
                }
            }

            // 削除処理
            if ($action === 'delete') {
                if (!empty($selectedIds)) {
                    $announceDivs = $this->MAnnounceDiv->find()
                        ->where(['announce_div IN' => $selectedIds, 'del_flg' => 0])
                        ->all();

                if ($announceDivs->isEmpty()) {
                    $this->Flash->error('選択されたお知らせ区分はすでに削除済みか存在しません。');
                } else {
                    $tAnnounce = $this->fetchTable('TAnnounce');

                    $cannotDelete = [];

                    foreach ($announceDivs as $announceDiv) {
                        $announceDivId = $announceDiv->announce_div;

                        $inUse = $tAnnounce->exists([
                            'announce_div' => $announceDivId,
                            'del_flg' => 0
                        ]);

                        if ($inUse) {
                            $cannotDelete[] = $announceDivId;
                        }
                    }

                    if (!empty($cannotDelete)) {
                        if (count($selectedIds) === 1) {
                            $this->Flash->error("お知らせ区分がお知らせで使用されているため、削除できません。");
                        } else {
                            $this->Flash->error('削除できないお知らせ区分が含まれています。');
                        }
                    } else {
                        foreach ($announceDivs as $announceDiv) {
                            $announceDiv->del_flg = 1;
                            $announceDiv->update_user = $this->request->getAttribute('identity')->get('user_id');
                            $this->MAnnounceDiv->save($announceDiv);
                        }
                        $this->Flash->success('選択されたお知らせ区分を削除しました。');
                    }
                }
            } else {
                $this->Flash->error('お知らせ区分が選択されていません。');
            }
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
    $mAnnounceDiv = $this->MAnnounceDiv->newEmptyEntity();

    // ✅ 次の自動連番（表示専用）
    $next = $this->MAnnounceDiv->find()
        ->select(['max_div' => 'MAX(announce_div)'])
        ->first();
    $nextAnnounceDiv = ($next->max_div ?? 0) + 1;

    // 初期表示
    if (!$this->request->is('post')) {
        $mAnnounceDiv->disp_no = 0;
    }

    try {
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // ❌ announce_div は保存に使わない
            unset($data['announce_div']);

            $mAnnounceDiv = $this->MAnnounceDiv->patchEntity($mAnnounceDiv, $data);

            $loginUserId = $this->request->getAttribute('identity')->get('user_id');
            $mAnnounceDiv->del_flg = '0';
            $mAnnounceDiv->create_user = $loginUserId;
            $mAnnounceDiv->update_user = $loginUserId;

            if ($this->MAnnounceDiv->save($mAnnounceDiv)) {
                $this->Flash->success('登録しました。');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('登録に失敗しました。');
        }
    } catch (Exception $e) {
        $this->Flash->error('システムエラーです。登録に失敗しました。');
        Log::error($e->getMessage());
    } finally {
        $this->set(compact('mAnnounceDiv', 'nextAnnounceDiv'));
        $this->set('mode', 'add');
        $this->render('add_edit');
    }
}


    /**
     * Edit method
     *
     * @param string|null $id M Announce Div id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
public function edit($id = null)
{
    try {
        $mAnnounceDiv = $this->MAnnounceDiv->get($id);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            // ❌ IDは変更不可
            unset($data['announce_div']);

            $mAnnounceDiv = $this->MAnnounceDiv->patchEntity($mAnnounceDiv, $data);

            $loginUserId = $this->request->getAttribute('identity')->get('user_id');
            $mAnnounceDiv->update_user = $loginUserId;

            if ($this->MAnnounceDiv->save($mAnnounceDiv)) {
                $this->Flash->success('更新しました。');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('更新に失敗しました。');
        }
    } catch (Exception $e) {
        $this->Flash->error('システムエラーです。更新に失敗しました。');
        Log::error($e->getMessage());
    } finally {
        $this->set(compact('mAnnounceDiv'));
        $this->set('mode', 'edit');
        $this->render('add_edit');
    }
}

}