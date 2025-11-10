<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Log\Log;
use Cake\Event\EventInterface;

use Cake\I18n\DateTime;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;

use Cake\Error\ExceptionTrap;                
use Psr\Http\Message\ServerRequestInterface; 
use Throwable;                               

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        /* $this->loadComponent('RequestHandler');*/
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');

        /*
            * Enable the following component for recommended CakePHP form protection settings.
            * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
            */
        //$this->loadComponent('FormProtection');
    }
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $req = $this->request;
            $ctrl   = $req->getParam('controller');
            $action = $req->getParam('action');
            $prefix = $req->getParam('prefix');
            $plugin = $req->getParam('plugin');

            // Log::warning(sprintf(
            //     'ROUTE: prefix=%s plugin=%s %s::%s',
            //     (string)$prefix,
            //     (string)$plugin,
            //     (string)$ctrl,
            //     (string)$action
            // ));
            
        // 親クラスの beforeFilter 呼び出し→ CakePHP の基本動作を確保（親が持つ初期化処理を実行） Cake\Controller\Controller クラス のこと
        parent::beforeFilter($event);

        // ★到達確認（出なければ子で parent 呼んでない）
        Log::debug('[clock-probe] entered AppController::beforeFilter');

        $ctrl = (string)$this->request->getParam('controller');
        $act  = (string)$this->request->getParam('action');
        $loginFree = ($ctrl === 'MUser' && in_array($act, ['login','request','reset'], true));

        if (!$loginFree) {
            if ($resp = $this->guardAccessOrRedirect()) {
                $event->setResult($resp);
                return;
            }
        }

        // ★ここで必ず呼ぶ（早期returnの前）
        $this->logServerClocks();

        // AppController::beforeFilter() の冒頭（parent::beforeFilter($event); の直後）
        $rid = bin2hex(random_bytes(4));                  // リクエスト識別子
        $this->setRequest(
            $this->getRequest()
                ->withAttribute('reqId', $rid)           // 後でログ相関に使う
                ->withAttribute('t0', microtime(true))   // 経過時間計測の起点
        );

        // 🔐 認証済みユーザー情報（identity）を取得して View に渡す
        // ① 未ログインを早期判定
        $identity = $this->Authentication->getIdentity();
        $this->set('identity', $identity);// 画面用
        $this->setRequest($this->getRequest()->withAttribute('identity', $identity)); // 取得口を統一

        // 🛑 アカウント無かったらログインに戻る
        $ctrl = (string)$this->request->getParam('controller');
        $act  = (string)$this->request->getParam('action');

        // ← ログイン系は未認証でも通す
        $loginFree = ($ctrl === 'MUser' && in_array($act, ['login','request','reset'], true));

        if ($identity) {
            $userId = $identity->get('user_id');
            $userName = $identity->get('user_name');

            $user = $this->fetchTable('MUser')->get($userId);
            $useServiceId = $user->use_service_id ?? null;

            $serviceName = null;
            if ($useServiceId !== null) {
                $service = $this->fetchTable('MService')->find()
                    ->where(['use_service_id' => $useServiceId])
                    ->first();
                $serviceName = $service->service_name ?? null;
            }

            Log::debug("[📍セッション] user_id => {$userId}");
            Log::debug("[📍セッション] user_name => {$userName}");
            Log::debug("[📌セッション] serviceName => {$serviceName}");

            $this->set(compact('userId', 'userName', 'useServiceId', 'serviceName'));
        }

        // 1) まず identity から一次値を取り出す（※ここで初めて変数を定義）
        $userId        = (string)($identity?->get('user_id') ?? '');
        $userName      = (string)($identity?->get('user_name') ?? '');
        $useServiceId  = $identity?->get('use_service_id');    // null OK
        $usePatternId  = $identity?->get('use_pattern_id');    // null OK

        // ログインユーザー情報を取得し、足りない場合はM_USERから補完
        // Authentication のログイン情報（identity）には use_service_id や use_pattern_id が入っていない
        // 後続の処理やビュー表示でそれらが必要になるのならM_USER テーブルから再取得して埋める
        // 2) 不足があれば M_USER で補完
        if ($userId !== '' && ($useServiceId === null || $usePatternId === null || $userName === '')) {
            $muser = $this->fetchTable('MUser')->find()
                ->select(['user_id', 'user_name', 'use_service_id', 'use_pattern_id'])
                ->where(['user_id' => $userId, 'del_flg' => 0, 'status' => 1])
                ->first();
            if ($muser) {
                if ($userName === '')     { $userName     = (string)($muser->user_name ?? ''); }
                if ($useServiceId === null){ $useServiceId = $muser->use_service_id ?? null; }
                if ($usePatternId === null){ $usePatternId = $muser->use_pattern_id ?? null; }
            }
        }

        // 3) 補完“後”の確定値で currentUser を作る
        $ctx = [
            'user_id'        => $userId,
            'user_name'      => $userName,
            'use_service_id' => $useServiceId,
            'use_pattern_id' => $usePatternId,
        ];
        
        // View へ渡す
        // 下流の Controller/Component でも使えるように request 属性へ
        $this->set('currentUser', $ctx);
        $this->setRequest($this->getRequest()
                ->withAttribute('currentUser', $ctx)
            ->withAttribute('identity', $identity)); // フォールバック用にも残す

        Log::debug("[currentUser] user_id={$ctx['user_id']}, user_name={$ctx['user_name']}, "
            . 'use_service_id=' . var_export($ctx['use_service_id'], true)
            . ', use_pattern_id=' . var_export($ctx['use_pattern_id'], true));

            //画面切替フラグ routeUseDiv を“手動で”注入してログるテスト用コード
            $controller = (string)$this->request->getParam('controller');
            $action     = (string)$this->request->getParam('action');

            // ★ いまは手動で 1/2/0 を入れる（後で resolveUseDivForRoute() に差し替え）
            $useDiv = (int)($useServiceId ?? 0); // 1=マスター, 2/3/4=本人系, 5=閲覧者, null→0

            // ★ ここで一元チェック（リダイレクト指示が返ったら停止）
            if ($resp = $this->guardAccessOrRedirect()) {
                $event->setResult($resp);
                return;
            }

            // View変数 & Request属性に載せる
            $this->set('routeUseDiv', $useDiv);
            $this->setRequest($this->getRequest()->withAttribute('route_use_div', $useDiv));

            // ★ ここを置換（reqId を付ける）
            $rid = (string)($this->getRequest()->getAttribute('reqId') ?? '-');
            // Log::debug(sprintf(
            //     '[route_use_div][req:%s] user=%s ctrl=%s action=%s use_div=%d (src=manual)',
            //     $rid, $userId, $controller, $action, $useDiv
            // ));
    }
    // AppController クラス内
    public function beforeRender(\Cake\Event\EventInterface $event): void
    {
        parent::beforeRender($event);

        $req  = $this->getRequest();
        $rid  = (string)($req->getAttribute('reqId') ?? '-');
        $t0   = (float)($req->getAttribute('t0') ?? microtime(true));
        $ru   = (int)($req->getAttribute('route_use_div') ?? 1);
        $ctrl = (string)$req->getParam('controller');
        $act  = (string)$req->getParam('action');
        $msec = (microtime(true) - $t0) * 1000;

        $current = (array)($req->getAttribute('currentUser') ?? []);
        $userId  = (string)($current['user_id'] ?? '');

        // ▼ 代わりにこの1本のログに置換（level を出さない版）
        // Log::debug(sprintf(
        //     '[route_use_div][req:%s] user=%s ctrl=%s action=%s use_div=%d',
        //     $rid, $userId, $ctrl, $act, $ru
        // ));

    }
    public function bootstrap(): void
    {
        parent::bootstrap();

        // Log::warning('[boot] setExceptionRenderer called'); // 起動確認ログ

        ExceptionTrap::setExceptionRenderer(
            function (Throwable $e, ?ServerRequestInterface $request = null) {
                return new \App\Error\Renderer\AppExceptionRenderer($e, $request);
            }
        );
    }

    // =========================
    // 認証・認可 共通ヘルパ
    // =========================

    // MAuth　m_disp_userから持ってきて処理　MENU画面に表示される
    protected function fetchMenusForCurrentUser(): array
    {
        $svc = (int)($this->getLoginUserInfo()['use_service_id'] ?? 0);

        // use_service_id=5 は配信先のサービス(2/3/4…)で集約
        $svcList = [$svc];
        if ($svc === 5) {
            $targets = $this->getDispTargetsIfViewer5();
            // getLoginUserInfo　5専用メソッド appControllerにあり
            $svcList = array_values(array_unique(array_map(
                fn($u) => (int)$u->use_service_id,
                $targets
            )));
            // ★ 4(AB) が含まれるなら 4 のみ採用（A/ B が一緒に来ても AB に寄せる）
            if (in_array(4, $svcList, true)) {
                $svcList = [4];
            } elseif (in_array(2, $svcList, true) && in_array(3, $svcList, true)) {
                // 念のため：A と B が両方あれば AB に寄せる
                $svcList = [4];
            }
            if (!$svcList) $svcList = [0]; // 空防止
        }

        $q = $this->getTableLocator()->get('MAuth')->find()
            ->join([
                'Menus' => [
                    'table' => 'm_menu',
                    'type'  => 'INNER',
                    'conditions' => [
                        'Menus.menu_id = MAuth.menu_id',
                        'Menus.del_flg' => '0',
                    ],
                ],
            ])
            ->select([
                'menu_id'    => 'MAuth.menu_id',
                'use_div'    => 'MAuth.use_div',   // 0=非表示 1=表示 2=表示(閲覧条件フラグ)
                'menu_name'  => 'Menus.menu_name',
                'controller' => 'Menus.controller',
                'action'     => 'Menus.action',
                'disp_no'    => 'Menus.disp_no',
            ])
            ->where(['MAuth.use_service_id IN' => $svcList])
            ->orderBy(['Menus.disp_no' => 'ASC']);

        // 0=非表示 を除外。1/2はそのまま返す（リンクも触らない）
        $rows = $q->enableHydration(true)->all()->toList();
        return array_values(array_filter($rows, fn($m) => (int)$m->use_div !== 0));
    }
    /** 食材発注画面に使用
     * 現在ログイン中のユーザー情報（identity）を配列形式で取得する。
     * ビューやコントローラーから共通的に利用できるようにするためのヘルパーメソッド。
     */
        public function getLoginUserInfo(): array
        {
            // beforeFilter で確定済みの currentUser を最優先
            $ctx = (array)($this->getRequest()->getAttribute('currentUser') ?? []);
            if (!empty($ctx)) return $ctx;
            // フォールバック：identity（属性→Authentication）
            $identity = $this->getRequest()->getAttribute('identity');

            return [
                'user_id'           => $identity?->get('user_id'),
                'user_name'         => $identity?->get('user_name'),
                'use_service_id'    => $identity?->get('use_service_id'),
                'use_pattern_id'    => $identity?->get('use_pattern_id'),
                'use_div'        => (int)($this->getRequest()->getAttribute('route_use_div') ?? 0), // ★追加
            ];
        }
        // getLoginUserInfo　5専用メソッド
        protected function getDispTargetsIfViewer5(): array
        {
            $svc = (int)($this->getLoginUserInfo()['use_service_id'] ?? 0);
            if ($svc !== 5) return [];

            $viewerId = (string)($this->getLoginUserInfo()['user_id'] ?? '');
            if ($viewerId === '') return [];

            // （差し替え）M_DISP_USER の参照方向を正す：左=user_id(閲覧者5) → 右=disp_user_id(対象)
            $Disp = $this->getTableLocator()->get('MDispUser');

            $ids  = $Disp->find()
                ->select(['disp_user_id'])            // 取得するのは “対象側” のID
                ->where(['user_id' => $viewerId])     // 条件は “閲覧者=左側”
                ->enableHydration(false)
                ->all()
                ->extract('disp_user_id')             // 抽出キーも合わせて変更
                ->toList();

            if (!$ids) return [];

            return $this->getTableLocator()->get('MUser')->find()
                ->select(['user_id','user_name','use_service_id'])
                ->where(['user_id IN' => $ids])
                ->order(['user_id' => 'ASC'])
                ->enableHydration(true)->all()->toList();
        }
    /**
     * 画面権限レベルを決定する　食材発注　配食発注
     * @param string   $controller  コントローラ名
     * @param ?int     $targetUserId 対象ユーザー（将来の本人/他人分岐用・未使用ならnull）
     * @return int  level（例：1=管理, 2=更新, 0=閲覧, -1=利用不可）
     */
        // そのコントローラに対して利用可能か
        public function decidePermission(string $controller): int
        {
            // 1) ログインユーザーの検証
            $info   = (array)$this->getLoginUserInfo();
            $userId = (string)($info['user_id'] ?? '');
            $MUser  = $this->fetchTable('MUser');
            $user   = $MUser->find()->where(['user_id' => $userId, 'del_flg' => '0'])->first();

            if (!$user) {
                Log::warning("[decidePermission] 利用不可: user_id={$userId}");
                return -1;
            }

            $svcId = (int)$user->use_service_id;

            // 2) controller → menu_id 解決
            $Mmenu  = $this->fetchTable('Mmenu');
            $menu   = $Mmenu->find()->select(['menu_id'])
                ->where(['controller' => $controller])
                ->enableHydration(false)->first();

            if (!$menu) {
                Log::warning(sprintf('[decidePermission] m_menu 未登録 ctrl=%s', $controller));
                return -1;
            }
            $menuId = (int)$menu['menu_id'];

            // 3) m_auth の AUTH（use_div）判定
            $MAuth = $this->fetchTable('MAuth');

            // サービス1〜4：AUTH1 必須（use_div='1'）
            if (in_array($svcId, [1, 2, 3, 4], true)) {
                $row = $MAuth->find()->select(['use_div'])
                    ->where([
                        'menu_id' => $menuId,
                        'use_service_id IN' => [$svcId, 0], // 0 = ワイルドカード
                    ])
                    ->order(['use_service_id' => 'DESC'])
                    ->enableHydration(false)
                    ->first();

                if ($row && (string)$row['use_div'] === '1') {
                    Log::debug(sprintf('[decidePermission] 成立: サービス%d-AUTH1 → 使用許可=%d (menu_id=%d, user=%s)',
                        $svcId, $svcId, $menuId, $userId
                    ));
                    return $svcId; // 1..4
                }

                Log::debug(sprintf('[decidePermission] 不成立: サービス%d-AUTH1 (menu_id=%d, user=%s)',
                    $svcId, $menuId, $userId
                ));
                return -1;
            }

            // サービス5：AUTH2 必須（use_div='2'）＋ DISP_USER を配列で返す（Request Attribute）
            if ($svcId === 5) {
                $row = $MAuth->find()->select(['use_div'])
                    ->where([
                        'menu_id' => $menuId,
                        'use_service_id IN' => [5, 0],
                    ])
                    ->order(['use_service_id' => 'DESC'])
                    ->enableHydration(false)
                    ->first();

                if ($row && (string)$row['use_div'] === '2') {
                    $MDispUser = $this->fetchTable('MDispUser');
                    $disp = $MDispUser->find()->select(['disp_user_id'])
                        ->where(['user_id' => $userId])
                        ->enableHydration(false)->all()->toList();

                    $dispIds = array_column($disp, 'disp_user_id');
                    $this->setRequest(
                        $this->getRequest()->withAttribute('disp_user_ids', $dispIds)
                    );

                    Log::debug(sprintf('[decidePermission] 成立: サービス5-AUTH2 → disp_user_ids=%s → 使用許可=5 (menu_id=%d, user=%s)',
                        json_encode($dispIds, JSON_UNESCAPED_UNICODE), $menuId, $userId
                    ));
                    return 5;
                }

                Log::debug(sprintf('[decidePermission] 不成立: サービス5-AUTH2 (menu_id=%d, user=%s)',
                    $menuId, $userId
                ));
                return -1;
            }

            // 未対応のサービスID
            Log::debug(sprintf('[decidePermission] 不成立: 未対応 svc=%d (menu_id=%d, user=%s)', $svcId, $menuId, $userId));
            return -1;
        }
        // 使用不可のユーザー　「アクセス可否とリダイレクト先を判断」する共通ゲート
        protected function guardAccessOrRedirect(): ?Response
        {
            $req  = $this->getRequest();
            $rid  = (string)($req->getAttribute('reqId') ?? '-');
            $ctrl = (string)$req->getParam('controller');
            $act  = (string)$req->getParam('action');
            $uri  = $req->getMethod() . ' ' . $req->getRequestTarget();

            // ★ 最優先許可：大小文字を正しく 'MUser'
            if ($ctrl === 'MUser' && $act === 'logout') {
                Log::debug(sprintf('[guard][req:%s] ALLOW (logout) %s %s/%s', $rid, $uri, $ctrl, $act));
                return null;
            }

            // アローリスト（Allow list）も 'MUser' に統一
            $publicRoutes = [
                'Mmenus' => ['index'],
                'MUser'  => ['login', 'request', 'reset', 'logout'],
            ];

            // （任意・確認用に一時ログ）
            Log::debug(sprintf('[guard][req:%s] ctrl=%s act=%s', $rid, $ctrl, $act));

            // ② 素通し判定（ワイルドカード対応）
            $isPublic = false;
            if (isset($publicRoutes[$ctrl])) {
                $actions = $publicRoutes[$ctrl];
                $isPublic = in_array('*', $actions, true) || in_array($act, $actions, true);
            }

            if ($isPublic) {
                Log::debug(sprintf('[guard][req:%s] ALLOW (public) %s %s/%s', $rid, $uri, $ctrl, $act));
                return null;
            }

            $perm = (int)$this->decidePermission($ctrl);
            $this->set('usePermission', $perm);

            if ($perm < 0) {
                // ここはあなたの既存の戻し先に合わせてください
                $resp = $this->redirect(['controller' => 'MUser', 'action' => 'login']);
                $loc  = $resp->getHeaderLine('Location') ?: '(no Location)';
                Log::debug(sprintf(
                    '[guard][req:%s] REDIRECT %s %s/%s -> %s (status=%d)',
                    $rid, $uri, $ctrl, $act, $loc, $resp->getStatusCode()
                ));
                return $resp;
            }

            Log::debug(sprintf('[guard][req:%s] ALLOW (perm=%d) %s %s/%s', $rid, $perm, $uri, $ctrl, $act));
            return null;
        }

        protected function json($payload, int $status = 200)
        {
            $this->autoRender = false; // ★テンプレ自動描画を止める
            $this->response = $this->response
                ->withType('application/json')
                ->withStatus($status)
                ->withStringBody(json_encode($payload, JSON_UNESCAPED_UNICODE));
            return $this->response;
        }
        /**
         * PHP時刻 / SQLサーバー時刻（ローカル・UTC・JST変換）を日本語で1行ログ出力
         */
        protected function logServerClocks(): void
        {
            // PHP（Cake/PHP）の現在時刻：date_default_timezone_get() に依存
            $phpNow = \Cake\I18n\DateTime::now();
            $tz     = date_default_timezone_get();

            // SQL Server 側：ローカル時刻（オフセット付き）・UTC・JST 変換を同時取得
            $sqlLocal   = '取得エラー';
            $sqlUtc     = '取得エラー';
            $sqlJst1    = '取得エラー'; // SWITCHOFFSET 版（+09:00に変換）
            $sqlJst2    = '取得エラー'; // AT TIME ZONE 版（Tokyo Standard Timeに変換）

            try {
                $conn = \Cake\Datasource\ConnectionManager::get('default');

                // ※ AT TIME ZONE は SQL Server 2016+ で利用可
                $row = $conn->execute("
                    SELECT
                        SYSDATETIMEOFFSET()                                   AS server_local,       -- サーバーOSのタイムゾーンでの現在時刻
                        SYSUTCDATETIME()                                      AS utc_now,            -- UTC現在
                        SWITCHOFFSET(SYSDATETIMEOFFSET(), '+09:00')           AS jst_via_switch,     -- 絶対時刻を保ったまま +09:00 に変換
                        (SYSUTCDATETIME() AT TIME ZONE 'UTC')
                            AT TIME ZONE 'Tokyo Standard Time'                 AS jst_via_timezone   -- UTC→JST変換（Windows名）
                ")->fetch('assoc');

                if ($row) {
                    $sqlLocal = (string)($row['server_local'] ?? 'N/A');      // 例: 2025-06-15 07:48:50.3247615 +09:00
                    $sqlUtc   = (string)($row['utc_now'] ?? 'N/A');           // 例: 2025-06-14 22:48:50.3247615
                    $sqlJst1  = (string)($row['jst_via_switch'] ?? 'N/A');    // 例: 2025-06-15 07:48:50.3247615 +09:00
                    $sqlJst2  = (string)($row['jst_via_timezone'] ?? 'N/A');  // 例: 2025-06-15 07:48:50.3247615 +09:00
                }
            } catch (\Throwable $e) {
                $sqlLocal = $sqlUtc = $sqlJst1 = $sqlJst2 = 'ERROR: ' . $e->getMessage();
            }

            Log::debug("【時刻確認】PHP: {$phpNow->format('Y-m-d H:i:s')} (TZ={$tz})");
            Log::debug("【時刻確認】SQL Local: {$sqlLocal}");
            Log::debug("【時刻確認】SQL UTC  : {$sqlUtc}");
            // Log::debug("【時刻確認】SQL JST1 : {$sqlJst1}");
            // Log::debug("【時刻確認】SQL JST2 : {$sqlJst2}");
        }

}
