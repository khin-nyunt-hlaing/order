<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;
use Cake\Log\Log; 
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\I18n\DateTime;
use Cake\ORM\Exception\PersistenceFailedException;
use \Exception;
use Cake\Utility\Hash;


/**
 * MUser Controller
 * 施設マスタのコントローラです。
 *
 * @property \App\Model\Table\MUserTable $MUser
 */
class MUserController extends AppController
{
    /**
     * Index method
     * 初期表示・検索処理を行います。
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
public function initialize(): void
    {
        parent::initialize();
        $this->MUser = $this->fetchTable('MUser');
    }

public function index()
{
    // POSTリクエスト取得（検索条件）
    $request = $this->request->getData();

    // 削除フラグ（検索条件）
    $showDeleted = isset($request['del_flg']) && $request['del_flg'] === '1';

    // 施設グループリスト取得
    $MUserGroup = $this->fetchTable('MUserGroup');
    $groupList = $MUserGroup->find('list',
        keyField: 'user_group_id',
        valueField: 'user_group_name',
    )->where(['del_flg' => 0])->toArray();

    // サービステーブルのリスト取得
    $MService = $this->fetchTable('MService');
    $serviceList = $MService->find('list',
        keyField: 'use_service_id',
        valueField: 'service_name',
    )->where(['del_flg' => 0])->toArray();

    // 状態リスト
    $statusList = [
        ''  => '',
        '0' => '準備中',
        '1' => '利用中',
        '2' => '取引停止',
    ];

    // 検索条件の初期化
    $userId      = $request['user_id'] ?? '';
    $userName    = $request['user_name'] ?? '';
    $serviceId   = $request['use_service_id'] ?? '';
    $userGroupId = $request['user_group_id'] ?? '';

    // status（checkbox複数）を配列に統一
    $status = $request['status'] ?? [];
    if (!is_array($status)) {
        $status = [$status];
    }

    // MUserテーブル
    $mUserTable = $this->fetchTable('MUser');

    // =========================
    // クエリ作成（★修正ポイント）
    // =========================
    $query = $mUserTable->find()
        ->select([
            'MUser.user_id',
            'MUser.user_name',
            'MUser.status',
            'MUser.disp_no',
            'MUser.del_flg',
            'MService.service_name',
            'user_group_id'   => 'mug.user_group_id',
            'user_group_name' => 'mug.user_group_name',
        ])
        ->contain(['MService'])
        ->leftJoin(
            ['mug' => 'm_user_group'],
            "ISNUMERIC(SUBSTRING(CAST(MUser.user_id AS VARCHAR), 1, 5)) = 1
             AND TRY_CAST(SUBSTRING(CAST(MUser.user_id AS VARCHAR), 1, 5) AS INT) = mug.user_group_id"
        )
        ->where(['MUser.disp_no IS NOT' => null])
        ->order([
            'MUser.disp_no' => 'ASC',
            'MUser.user_id' => 'ASC'
        ]);

    // 削除フラグ
    if (!$showDeleted) {
        $query->where(['MUser.del_flg' => '0']);
    }

    // 施設番号（完全一致）
    if ($userId !== '') {
        $query->where(['MUser.user_id' => $userId]);
    }

    // 施設名（部分一致）
    if ($userName !== '') {
        $query->where(['MUser.user_name LIKE' => "%{$userName}%"]);
    }

    // 発注サービス
    if ($serviceId !== '') {
        $query->where(['MService.use_service_id' => $serviceId]);
    }

    // 状態（複数対応）
    $validStatus = array_values(array_intersect($status, ['0', '1', '2']));
    if (!empty($validStatus)) {
        $query->where(['MUser.status IN' => $validStatus]);
    }

    // 施設グループによる絞込
    if ($userGroupId !== '') {
        $query->where(['mug.user_group_id' => $userGroupId]);
    }

    // 取得
    $mUser = $query->all();
    $count = $mUser->count();

    // =========================
    // ボタン処理
    // =========================
    if ($this->request->is('post')) {
        $action   = $this->request->getData('action');
        $selected = array_keys(array_filter($this->request->getData('select') ?? []));

        // 新規
        if ($action === 'add') {
            return $this->redirect(['action' => 'add']);
        }

        // 編集
        if ($action === 'edit') {
            if (count($selected) === 1) {
                return $this->redirect(['action' => 'edit', $selected[0]]);
            }
            $this->Flash->error(
                count($selected) > 1
                ? '更新は1件のみ選択可能です。'
                : '施設が選択されていません。'
            );
            return $this->redirect(['action' => 'index']);
        }

        // 削除
        if ($action === 'delete') {
            if (empty($selected)) {
                $this->Flash->error('施設が選択されていません。');
                return $this->redirect(['action' => 'index']);
            }

            $users = $mUserTable->find()
                ->where(['user_id IN' => $selected, 'del_flg' => 0])
                ->all();

            if ($users->isEmpty()) {
                $this->Flash->error('選択された施設はすでに削除済みか存在しません。');
                return $this->redirect(['action' => 'index']);
            }

            $mDispUser  = $this->fetchTable('MDispUser');
            $tFoodOrder = $this->fetchTable('TFoodOrder');
            $tDeliOrder = $this->fetchTable('TDeliOrder');

            $cannotDelete = [];

            foreach ($users as $user) {
                $uid = $user->user_id;
                if (
                    $mDispUser->exists(['disp_user_id' => $uid]) ||
                    $tFoodOrder->exists(['user_id' => $uid]) ||
                    $tDeliOrder->exists(['user_id' => $uid])
                ) {
                    $cannotDelete[] = $uid;
                }
            }

            if (!empty($cannotDelete)) {
                $this->Flash->error('削除できない施設が含まれています。');
            } else {
                foreach ($users as $user) {
                    $user->del_flg = 1;
                    $user->update_user = $this->request->getAttribute('identity')->get('user_id');
                    $mUserTable->save($user);
                }
                $this->Flash->success('選択された施設を削除しました。');
            }

            return $this->redirect(['action' => 'index']);
        }
    }

    // Viewへ渡す
    $this->set(compact(
        'mUser', 'count',
        'userId', 'userName', 'serviceId', 'status', 'userGroupId',
        'showDeleted', 'groupList', 'serviceList', 'statusList'
    ));
}



/**
 * 施設マスタを登録します。
 */
 public function add()
{
    //新規エンティティ
    $mUser = $this->MUser->newEmptyEntity();

     if (!$this->request->is('post')) {
        $mUser->disp_no = 0;
    }
    
    //初期化
    $selectedGroupId = null;

    //施設グループ取得
    $mUserGroups = $this->fetchTable('MUserGroup')->find('list', 
        keyField : 'user_group_id',
        valueField : 'user_group_name'
    )->where(['del_flg' => '0'])->toArray();

    //    debug($mUserGroups);
    
        //サービス一覧取得、整形
        $mServices = $this->fetchTable('MService')->find('list', 
            keyField : 'use_service_id',
            valueField : 'service_name'
        )->where(['del_flg' => '0'])
            ->order(['use_service_id' => 'ASC'])
            ->toArray();

        $serviceList = [];
            foreach ($mServices as $id => $name) {
            $serviceList[$id] = $id . ' / ' . $name;
        }


        //パスワード？
        //質問リスト    
        $questionList = [
            '1' => '好きな食べ物は？',
            '2' => '母親の旧姓は？',
            '3' => '初めて飼ったペットの名前は？',
        ];

        //配食パターン一覧、整形
        $mPatterns = $this->fetchTable('MDeliveryPattern')->find('list', 
            keyField : 'use_pattern_id',
            valueField : 'delivery_pattern_name'
        )->where(['del_flg' => '0'])
            ->order(['disp_no' => 'ASC'])
            ->toArray();    

        $patternList = [];
            foreach ($mPatterns as $id => $name) {
            $patternList[$id] = $id . ' / ' . $name;
        }

        //リードタイム
        //m_system_setting テーブルから deli_req_chk 取得
        $systemSettingsTable = $this->fetchTable('MSystemSetting');
        $setting = $systemSettingsTable->find()->first();
        $minLeadTime = $setting ? (int)$setting->deli_req_chk : 0;

        // ユーザーが既に値を持っていればそれを初期値に、なければ最小値
        $defaultLeadTime = $minLeadTime;
        //モデルにリードタイムを渡す（バリデーション用）
        $this->MUser->minLeadTime = $minLeadTime;
        
        // 利用状態一覧
        $statusList = [
            0 => '準備中',
            1 => '利用中',
            2 => '取引停止'
        ];

        // サービスIDが2,3,4の閲覧施設対象一覧
        $viewedUsers = $this->fetchTable('MUser')
            ->find('list', 
                keyField : 'user_id',
                valueField : 'user_name'
            )
            ->where([
                'use_service_id IN' => [2,3,4],
                'del_flg' => 0
            ])
            ->order(['user_name' => 'ASC'])
            ->toArray();

    //    debug($viewedUsers);
        
        
        //チェックボックス
        $selectedDispUserIds = []; // 初回は全オフ

        //保存処理    
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            Log::debug('POSTされたデータ: ' . print_r($data, true));
        
        // サービスIDを取得
        $selectedServiceId = $data['use_service_id'] ?? null;
        //閲覧施設を取得
        $selectedDispUsers = $data['disp_user_ids'] ?? [];
        $selectedDispUserIds = array_keys($selectedDispUsers);
    
        Log::debug('選択された閲覧施設IDs: ' . print_r($selectedDispUserIds, true));
        
        // disp_user_idsはpatchEntity対象から外す
        unset($data['disp_user_ids']);

        // グループIDを取得
        $groupId = $data['user_group_id'];

        // user_idを自動採番
        $data['user_id'] = $this->createUserId($groupId);

        // グループID保持
        $selectedGroupId = $data['user_group_id'] ?? null;

        // ログインユーザー情報補完
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');

        $data['create_user'] = $loginUserId;
        $data['update_user'] = $loginUserId;
        $data['del_flg'] = 0;
    
        
        // エンティティにデータを反映（バリデーション含む）
        $mUser = $this->MUser->patchEntity($mUser, $data);
    
        Log::debug('登録内容: ' . print_r($mUser->toArray(), true));
        Log::debug('2');

        
        if ($mUser->hasErrors()) {
            Log::debug('3');
            $this->Flash->error(__('入力内容にエラーがあります。内容をご確認ください。'));
        } else {
            Log::debug('4');
            // トランザクション開始
            $conn = $this->MUser->getConnection();
            $conn->begin();

            // 「サービスIDが5 かつ 閲覧施設が未選択なら止める」
        if ($selectedServiceId == 5 && empty($selectedDispUserIds) ){
            $this->Flash->error(__('閲覧先を指定してください'));
             $selectedGroupId = null;

            $this->set(compact(
            'mUser',  'mUserGroups','mServices', 'serviceList','selectedGroupId',
            'questionList','mPatterns', 'patternList','statusList', 'defaultLeadTime',
            'minLeadTime', 'viewedUsers', 'selectedDispUserIds'));
            $this->set('mode', 'add');
            return $this->render('add_edit'); // ← return を忘れない
        }
                
            //テーブルへの保存処理
            try {
//throw new Exception();
                if ($this->MUser->saveOrFail($mUser)) {
                    if ($selectedServiceId == 5 && !empty($selectedDispUserIds)) {

                        $mDispUserTable = $this->fetchTable('MDispUser');
                        foreach ($selectedDispUserIds as $dispUserId) {
                            if (!$mDispUserTable->exists([
                                'user_id' => $mUser->user_id,
                                'disp_user_id' => $dispUserId
                            ])) {
                                $entity = $mDispUserTable->newEntity([
                                    'user_id' => $mUser->user_id,
                                    'disp_user_id' => $dispUserId,
                                    'create_user' => $loginUserId,
                                    'update_user' => $loginUserId,
                                ]);
                                $mDispUserTable->saveOrFail($entity);
                            }
                        }   
                    }
                    $conn->commit();
                    $this->Flash->success(__('登録しました。'));
                    return $this->redirect(['action' => 'index']);
                }
            } catch (PersistenceFailedException $e) {

                $conn->rollback();
                Log::error('[PersistenceFailedException] ' . $e->getMessage());
                $this->Flash->error('入力内容に誤りがあり保存できませんでした。');
            //想定外のエラーはここでキャッチ(DBエラー、SQL構文エラーなど)
            } catch (Exception $e) {
                Log::debug('7');
                $conn->rollback();
                Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $this->Flash->error('システムエラーです。登録に失敗しました。');
            }
        }
    }            
        
        // ビューに渡す
        $this->set(compact(
            'mUser',  'mUserGroups','mServices', 'serviceList',
            'questionList','mPatterns', 'patternList','statusList', 'defaultLeadTime',
            'minLeadTime', 'viewedUsers', 'selectedDispUserIds'));
        $this->set('selectedGroupId', $selectedGroupId);
        $this->set('mode', 'add');
        $this->render('add_edit');
}

/**
     * 施設マスタを更新します。
     */
public function edit($id = null)
{
    //対象レコードが存在しない場合に、早期リダイレクト
    try{
        $mUser = $this->MUser->get($id, contain: ['MDispUser']);
    }catch(RecordNotFoundException $e){
        $this->Flash->error('対象データが存在しません。');
        return $this->redirect(['action' => 'index']);
    }

    Log::debug('①登録内容: ' . print_r($mUser->toArray(), true));

    //user_group_idはuser_idの先頭5桁で取得
    $selectedGroupId = (int)substr((string)$mUser->user_id, 0, 5);

    //施設グループ取得
    $mUserGroups = $this->fetchTable('MUserGroup')->find('list', 
        keyField : 'user_group_id',
        valueField : 'user_group_name'
    )->where(['del_flg' => '0'])->toArray();
   
    //サービス一覧取得、整形
    $mServices = $this->fetchTable('MService')->find('list',
        keyField : 'use_service_id',
        valueField : 'service_name'
    )->where(['del_flg' => '0'])
        ->order(['use_service_id' => 'ASC'])
        ->toArray();

    $serviceList = [];
        foreach ($mServices as $id => $name) {
        $serviceList[$id] = $id . ' / ' . $name;
    }


    //配食パターン一覧、整形
    $mPatterns = $this->fetchTable('MDeliveryPattern')->find('list', 
        keyField : 'use_pattern_id',
        valueField : 'delivery_pattern_name'
    )->where(['del_flg' => '0'])
        ->order(['disp_no' => 'ASC'])
        ->toArray();    

    $patternList = [];
        foreach ($mPatterns as $id => $name) {
        $patternList[$id] = $id . ' / ' . $name;
    }

    
    //リードタイム
    //m_system_setting テーブルから deli_req_chk 取得
    $systemSettingsTable = $this->fetchTable('MSystemSetting');
    $setting = $systemSettingsTable->find()->first();
    $minLeadTime = $setting ? (int)$setting->deli_req_chk : 0;

    // ユーザーが既に値を持っていればそれを初期値に、なければ最小値
    $defaultLeadTime = $minLeadTime;
    //モデルにリードタイムを渡す（バリデーション用）
    $this->MUser->minLeadTime = $minLeadTime;
    

    // 利用状態一覧
    $statusList = [
        0 => '準備中',
        1 => '利用中',
        2 => '取引停止'
    ];

    // サービスIDが2,3,4の閲覧施設対象一覧
    $viewedUsers = $this->fetchTable('MUser')
        ->find('list', 
            keyField : 'user_id',
            valueField : 'user_name'
        )
        ->where([
            'use_service_id IN' => [2,3,4],
            'del_flg' => 0
        ])
        ->order(['user_name' => 'ASC'])
        ->toArray();


    // チェック済み閲覧施設IDを抽出
    $selectedDispUserIds = collection($mUser->m_disp_user)
        ->extract('disp_user_id') // disp_user_idで抽出
        ->toList();
    Log::debug('[選択済ユーザー] ' . print_r($selectedDispUserIds, true));
       

   if ($this->request->is(['patch', 'post', 'put'])) {
        $data = $this->request->getData();

        if (empty($data['password'])) {
            unset($data['password']);
            Log::debug('アンセット処理');
        }

        Log::debug('[edit] POSTデータ: ' . print_r($data, true));

        
        //ログインユーザー設定
        $loginUserId = $this->request->getAttribute('identity')->get('user_id');
        $data['update_user'] = $loginUserId;

        //user_group_idは画面から送られてくるがDBにはないので使うのはuser_idの生成時のみ
        // グループIDを取得
        $groupId = $data['user_group_id'] ?? $selectedGroupId;
        // サービスIDを取得
        $selectedServiceId = $data['use_service_id'] ?? null;
        

        //エンティティ更新
        $mUser = $this->MUser->patchEntity($mUser, $data);
        Log::debug('[edit] patchEntity後: ' . print_r($mUser->toArray(), true));
        
        //バリデーションエラー
        if($mUser->hasErrors()){
            $this->Flash->error(__('入力内容にエラーがあります。内容をご確認ください。'));
            Log::debug('[edit] バリデーションエラー: ' . print_r($mUser->getErrors(), true));
            $this->set(compact(
            'mUser',  'mUserGroups','mServices', 'serviceList','selectedGroupId',
            'mPatterns', 'patternList','statusList', 'defaultLeadTime',
            'minLeadTime', 'viewedUsers', 'selectedDispUserIds'));
            $this->set('mode', 'edit');
            return $this->render('add_edit');
        }

        try{
            //トランザクション開始
            $conn = $this->MUser->getConnection();
            $conn->begin();
        
//throw new Exception();

            // ユーザー情報保存（例外を投げる saveOrFail）
            $this->MUser->saveOrFail($mUser);

            //チェックされた施設一覧を配列に変換
            $selectedDispUsers = $data['disp_user_ids'] ?? [];
            $selectedDispUserIds = [];
            foreach ($selectedDispUsers as $dispUserId => $checked) {
                if ($checked === '1') {
                        $selectedDispUserIds[] = $dispUserId;
                }
            }


            // 「サービスIDが5 かつ 閲覧施設が未選択なら止める」
        if ($selectedServiceId == 5 && empty($selectedDispUserIds) ){
            $this->Flash->error(__('閲覧先を指定してください'));
             $selectedGroupId = null;

            $this->set(compact(
            'mUser',  'mUserGroups','mServices', 'serviceList','selectedGroupId',
            'mPatterns', 'patternList','statusList', 'defaultLeadTime',
            'minLeadTime', 'viewedUsers', 'selectedDispUserIds'));
            $this->set('mode', 'edit');
            return $this->render('add_edit'); // ← return を忘れない
        }

            
            // 差分保存ロジック呼び出し
            $this->saveDispUser($mUser->user_id, $selectedDispUserIds);

            $conn->commit();
            $this->Flash->success(__('更新しました。'));
            return $this->redirect(['action' => 'index']);
        //保存時のエラー
        } catch (PersistenceFailedException $e) {
            // $conn->rollback();
            // Log::error('[PersistenceFailedException] ' . $e->getMessage());
            // $this->Flash->error('入力内容に誤りがあり保存できませんでした。');

            $conn->rollback();

            $entity = $e->getEntity(); // ← 失敗したエンティティ
            $errors = Hash::flatten($entity->getErrors());

            Log::error('[PersistenceFailedException] ' . $e->getMessage(), [
                'errors' => $errors,
                'data'   => $this->request->getData(),
            ]);

            $this->Flash->error('入力内容に誤りがあり保存できませんでした。');

        //その他システムエラー
        } catch (Exception $e) {
            $conn->rollback();
            $this->Flash->error('システムエラーです。更新に失敗しました。');
            Log::error('[システムエラー] ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
       
    // ビューに渡す
    $this->set(compact(
            'mUser',  'mUserGroups','mServices', 'serviceList',
            'mPatterns', 'patternList','statusList', 'defaultLeadTime',
            'minLeadTime', 'viewedUsers','selectedGroupId', 'selectedDispUserIds'));
    
    $this->set('mode', 'edit');
    $this->render('add_edit');

}
/**
 * View method
    * パスワード再発行（パスワード忘れた人用
    *
    * @param string|null $id M User id.
    * @return \Cake\Http\Response|null|void Renders view
    * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
 */
public function request()
{
        //秘密の質問リスト
        $MQuestion = $this->fetchTable('MKubun');
        $questionList = $MQuestion->find('list', 
            keyField : 'kubun_value',
            valueField : 'kubun_name'
        )
            ->where(['kubun_cd' => 'MU', 'del_flg' => 0])
            ->order(['disp_no' => 'ASC'])
            ->toArray();

            $this->set(compact('questionList'));

        if ($this->request->is('post')) {
        $data = $this->request->getData();
        Log::debug('フォーム入力値: ' . print_r($data, true));

            $user_id = trim($data['user_id'] ?? '');
            $question = trim($data['secret_question'] ?? '');
            $answer = trim($data['answer'] ?? '');


        //ユーザー取得
        $user = $this->MUser->find()
            ->where([
                'user_id' => $user_id,
                
            ])
            ->first();

        // 施設番号存在チェック    
        if (!$user) {
            $this->Flash->error('施設番号が存在しません。');
            Log::debug('ユーザー未検出: ' . $user_id);
            return;
        }

        // 質問未設定チェック
        if (empty($user->question) || empty($user->answer)) {
            $this->Flash->error('秘密の質問を設定していません。再発行は管理者にお問い合わせください。');
            Log::debug('質問未設定ユーザー: ' . $user->user_id);
            return;
        }

        // 入力された質問・答えの照合（答えはハッシュ化されているので password_verify を使用）
        if ($question != $user->question || !password_verify($answer, $user->answer)) {
            $this->Flash->error('秘密の質問または答えが不正です。');
            Log::debug('認証失敗: user_id=' . $user->user_id . ' / 入力質問=' . $question);
            return;
        }

            // 本人確認成功 → セッションに保存して reset ページへ
            $session = $this->getRequest()->getSession();
            $session->write('verified_user_id', $user->user_id);

            Log::debug('認証成功: ' . $user->user_id);
            return $this->redirect(['action' => 'reset', '?' => ['user_id' => $user->user_id]]);
        }
}
//パスワード再設定ページ    
public function reset()
{
    $session = $this->getRequest()->getSession();
    $identity = $this->Authentication->getIdentity();

    // request() で本人確認済みユーザーがいればそれを優先、なければログインユーザーを利用
    $userId = $session->read('verified_user_id') ?? ($identity ? $identity->get('user_id') : null);

        if (!$userId) {
            $this->Flash->error('本人確認が行われていません。');
            return $this->redirect(['action' => 'login']);
        }

    // ユーザー取得
    $user = $this->MUser->get($userId);

        if (!$user) {
            $this->Flash->error('ユーザーが存在しません。');
            return $this->redirect(['action' => 'login']);
        }

    //秘密の質問リスト
    $MQuestion = $this->fetchTable('MKubun');
    $questionList = $MQuestion->find('list', 
        keyField : 'kubun_value',
        valueField : 'kubun_name'
    )
        ->where(['kubun_cd' => 'MU', 'del_flg' => 0])
        ->order(['disp_no' => 'ASC'])
        ->toArray();

        $this->set(compact('user', 'questionList'));

        

    if ($this->request->is('post')) {
        $data = $this->request->getData();
        
        Log::debug('フォーム入力値（reset）: ' . print_r($data, true));

        $newPass = $data['loginpass'] ?? '';
        $confirmPass = $data['confirmloginpass'] ?? '';
        $question = $data['secret_question'] ?? null;
        $answer = $data['passanswer'] ?? null;

        $hasDigit  = preg_match('/\d/', $newPass);// 半角数字
        $hasUpper  = preg_match('/[A-Z]/', $newPass);// 半角英語大文字
        $hasLower  = preg_match('/[a-z]/', $newPass);// 半角英語小文字
        $hasSymbol = preg_match('/[^A-Za-z0-9]/', $newPass);// 記号

        // パスワード必須＆確認用一致チェック
        if (!$newPass) {
            $this->Flash->error('パスワードを入力してください。');
            return;
        }

        if (strlen($newPass) > 100) {
            $error['loginpass'][] = '入力可能桁数を超えています。';
        }

        if (!empty($errors)) {
            $this->set(compact('errors', 'user', 'questionList'));
            return;
        }

        if ($newPass !== $confirmPass) {
            $this->Flash->error('パスワードが不正です。');
            return;
        }

        // 施設番号と同一のパスワードは禁止
        if ($user->user_id === $newPass) {
            $this->Flash->error('パスワードが不正です。');
            return;
        }

        // 数字は必須 ＋ (大文字 or 小文字 or 記号) も必須
        if (!$hasUpper || !$hasLower || !($hasDigit || $hasSymbol)) {
            $this->Flash->error('パスワードが不正です。');
            return;
        }

        // 10文字以下の場合
        if (strlen($newPass) < 10) {
            $this->Flash->error('パスワードが不正です。');
            return;
        }

        // 禁止文字が含まれている場合
        if (preg_match('/[\\\&<>"\'\s]/', $newPass)) {
            $this->Flash->error('パスワードが不正です。');
            return;
        }
        
        // 質問入力がある場合は答えも必須
        if ($question && !$answer) {
            $this->Flash->error('秘密の質問に対する、質問の答えが未入力です。');
            return;
        }

        // 任意で質問と答えが入力されている場合のみ保存
        if ($question) {
            if (!$answer) {
                $this->Flash->error('質問に答える場合は答えも必須です。');
                return;
            }
            $user->question = $question;
            $user->answer = password_hash($answer, PASSWORD_DEFAULT); // ハッシュ化して保存
        }

            Log::debug('ハッシュ化前: ' . $newPass);
            // パスワードは _setPassword() で自動ハッシュ化
            $user->password = $newPass; // ←これで _setPassword() が内部で1回だけ実行される
            $user->update_user = $identity ? $identity->get('user_id') : 'system';
            Log::debug('ハッシュ化後: ' . $user->password);


            if ($this->MUser->save($user)) {
                $session->delete('verified_user_id'); // セッションクリア
                $this->Authentication->logout();
                $this->Flash->success('パスワードを更新しました。');
                Log::debug('保存成功');
                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error('更新に失敗しました。');
                Log::debug('保存失敗: ' . print_r($user->getErrors(), true));
            }
        }
}
/**
 * 管理者がユーザーの秘密の質問・答えをリセット
 */
public function ajaxResetSecretQuestions($userId = null)
{
    $this->request->allowMethod(['post']);
    $this->autoRender = false;
    $mUserTable = $this->fetchTable('MUser');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'ユーザーIDが指定されていません。']);
        return;
    }

    try {
        $user = $mUserTable->get($userId);
        $user->question = null;
        $user->answer = null;

        if ($mUserTable->save($user)) {
            echo json_encode(['success' => true, 'message' => '秘密の質問と回答をリセットしました。']);
        } else {
            echo json_encode(['success' => false, 'message' => 'リセットに失敗しました。']);
        }
    } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
        echo json_encode(['success' => false, 'message' => '指定されたユーザーが存在しません。']);
    }
}
/**
 * 指定された施設グループIDに基づき、次の施設番号(user_id)を生成
 *
 * @param string $userGroupId
 * @return string $newUserId
 */
private function createUserId($groupId)
{
    $lastUser = $this->MUser->find()
        ->select(['user_id'])
        ->where(function ($exp) use ($groupId) {
            // user_idの先頭がグループIDにマッチするものを検索
            return $exp->like('user_id', $groupId . '%');
        })
        ->orderDesc('user_id')
        ->first();

    if ($lastUser) {
        // 末尾3桁をインクリメント（例: 001 → 002）
        $lastNumber = (int)substr($lastUser->user_id, strlen($groupId));
        $newNumber = str_pad((string)($lastNumber + 1), 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }

    return $groupId . $newNumber;
}

//子テーブル差分保存処理用
protected function saveDispUser($userId, array $selectedDispUserIds)
{
    $mDispUserTable = $this->fetchTable('MDispUser');
    $loginUserId = $this->request->getAttribute('identity')->get('user_id');

    //既存の関連データをすべて取得し、disp_user_idをキーにした配列に変換
    $existingDispUsers = $mDispUserTable->find()
        ->where(['user_id' => $userId])
        ->all()
        ->indexBy('disp_user_id')
        ->toArray();

    $newEntities = [];

    //送信されたdisp_user_idをループして新規追加 or スキップ
    foreach ($selectedDispUserIds as $dispUserId) {
        if (isset($existingDispUsers[$dispUserId])) {
            //既に存在 → 差分計算のために除外
            unset($existingDispUsers[$dispUserId]);
        } else {
            //新規作成
            $entity = $mDispUserTable->newEntity([
                'user_id' => $userId,
                'disp_user_id' => $dispUserId,
                'create_user' => $loginUserId,
                'update_user' => $loginUserId,
            ]);
            $newEntities[] = $entity;
        }
    }

    // 新規保存（失敗したら例外）
    foreach ($newEntities as $entity) {
        $mDispUserTable->saveOrFail($entity);
    }

    // 削除対象（差分で残ったもの）を削除（失敗したら例外）
    foreach ($existingDispUsers as $entity) {
        $mDispUserTable->deleteOrFail($entity);
    }
}
public function beforeFilter(\Cake\Event\EventInterface $event)
{
    parent::beforeFilter($event);

    // 認証なしでアクセスできるアクションを指定
    $this->Authentication->addUnauthenticatedActions(['login', 'request', 'reset']);
}
public function login()
{
    $mUserTable = $this->fetchTable('MUser');
    // まずログイン者IDを確定
    $identity     = $this->Authentication->getIdentity();
    $loginUserId  = (string)($identity?->get('user_id') ?? '');


    Log::debug('[LOGIN] 入力値: ' . json_encode($this->request->getData(), JSON_UNESCAPED_UNICODE));

        // ★ SQL実行＆ログ確認のための強制クエリ
        $userId = $this->request->getData('user_id');
    if ($userId !== null) {
        $mUserTable->find()->where(['user_id' => $userId])->first();
    }


    $this->request->allowMethod(['get', 'post']);

    // ★診断ログ（認証直前。ロジックは一切変えない）
    if ($this->request->is('post')) {
        $u = (string)($this->request->getData('user_id') ?? '');
        $p = (string)($this->request->getData('password') ?? '');
        if ($u !== '' && $p !== '') {
            $row = $this->fetchTable('MUser')->find()
                ->select(['user_id','password'])
                ->where(['user_id' => $u])
                ->enableHydration(false)->first();
            if ($row) {
                $stored = (string)($row['password'] ?? '');
                $ok = (new \Authentication\PasswordHasher\DefaultPasswordHasher())->check($p, $stored);
                Log::debug(sprintf('[LOGIN][diag] len=%d head=%s check=%s',
                    strlen($stored), substr($stored,0,4), $ok?'OK':'NG'));
            }
        }
        $result = $this->Authentication->getResult();

        // ▼ 履歴保存
        $historyTable = $this->fetchTable('TLoginInfo');

        // PK1: 入力されたログインID（成功/失敗どちらでも必ず入れる）
        $attemptedId = (string)($this->request->getData('user_id') ?? '');
        // 型が数値PKなら必要に応じてキャスト：$attemptedId = (int)($this->request->getData('user_id') ?? 0);

        if ($attemptedId !== '') {
            $entity = $historyTable->newEmptyEntity();

            // ★ 複合PKは set() で明示代入（mass-assignの影響を受けない）
            $entity->set('user_id', $attemptedId);          // PK1
            $entity->set('login_date', \Cake\I18n\DateTime::now()); // PK2

            // 任意列（スキーマに合わせて）
            $entity->set('login_result', $result->isValid() ? 1 : 9);
            // 認証後のidentityを取り直す（成功時のみ入る）
            $identityAfter = $this->Authentication->getIdentity();
            // 優先順: 認証後の user_id ＞ 入力されたID（$attemptedId）＞ "system"
            $actor = (string)($identityAfter?->get('user_id') ?? ($attemptedId !== '' ? $attemptedId : 'system'));

            $entity->set('create_user', $actor);
            $entity->set('update_user', $actor);

            try {
                $historyTable->saveOrFail($entity);
            } catch (\Throwable $e) {
                \Cake\Log\Log::warning('[LoginHistory] save failed: ' . $e->getMessage());
            }
        } else {
            \Cake\Log\Log::warning('[LoginHistory] skipped: empty user_id on POST');
        }

        // 【2】認証結果ログ
        \Cake\Log\Log::debug('[LOGIN] 認証結果: ' . ($result->isValid() ? '✅成功' : '❌失敗'));

        // 成功/失敗の分岐（POST内なので is('post') 条件は不要）
        if ($result->isValid()) {

            // サービス5（閲覧者）は閲覧先があるかを確認。無ければメッセージ→即ログアウト
            $identity = $this->Authentication->getIdentity();
            if (!$identity) {
                $this->Flash->error('セッションが無効です。再度ログインしてください。');
                return $this->redirect(['controller' => 'MUser', 'action' => 'login']);
            }

            if ((int)$identity->get('use_service_id') === 5) {
                $viewerId = (string)$identity->get('user_id');

                // ✅ 向きを修正：自分(user_id)が参照する先(disp_user_id)が1件でもあるか
                $hasTarget = $this->fetchTable('MDispUser')->exists(['user_id' => $viewerId]);

                if (!$hasTarget) {
                    $this->Authentication->logout();
                    $this->Flash->error('閲覧先がありません。管理者に確認してください。');
                    return $this->redirect(['controller' => 'MUser', 'action' => 'login']);
                }
            }
            $target = $this->request->getQuery('redirect') ?? ['controller' => 'Mmenus', 'action' => 'index'];
            return $this->redirect($target);
        }

        $this->Flash->error('ユーザーIDまたはパスワードが正しくありません');
        $identity = $this->Authentication->getIdentity();
        \Cake\Log\Log::debug('[LOGIN] 認証後ユーザー: ' . print_r($identity?->toArray(), true));
        \Cake\Log\Log::debug('[LOGIN] 認証失敗の詳細: ' . print_r($result->getErrors(), true));
            
    }
}
public function logout()
{
    // --- 直前情報を確保（クリア前に取らないと消える） ---
    $req   = $this->getRequest();
    $rid   = (string)($req->getAttribute('reqId') ?? '-');
    $ip    = (string)($req->clientIp() ?? '-');
    $ua    = (string)($req->getHeaderLine('User-Agent') ?: '-');
    $sid   = (string)($req->getSession()->id() ?? '-');

    $ident   = $this->Authentication->getIdentity();
    $userId  = (string)($ident?->get('user_id') ?? '');
    $userNm  = (string)($ident?->get('user_name') ?? '');

    Log::debug(sprintf('[logout][req:%s] START user_id=%s user_name=%s sid=%s ip=%s ua=%s',
        $rid, $userId, $userNm, $sid, $ip, $ua
    ));

    // --- 認証情報クリア ---
    $service = $this->Authentication->getAuthenticationService();
    $service->clearIdentity($this->request, $this->response);
    Log::debug(sprintf('[logout][req:%s] Cleared identity (user_id=%s)', $rid, $userId));

    // --- セッション破棄 ---
    $this->request->getSession()->destroy();
    Log::debug(sprintf('[logout][req:%s] Session destroyed (old sid=%s)', $rid, $sid));

    // --- リダイレクト ---
    $target = ['controller' => 'MUser', 'action' => 'login'];
    Log::debug(sprintf('[logout][req:%s] REDIRECT -> %s/%s', $rid, $target['controller'], $target['action']));
    return $this->redirect($target);
}
}