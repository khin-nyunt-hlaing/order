<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Log\Log; 

/**
 * Mmenus Controller
 *
 * @property \App\Model\Table\MmenusTable $Mmenus
 */
class MmenusController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */




public function index()
{
    // ===== ログインユーザー =====
    $identity = $this->request->getAttribute('identity');
    $useServiceId = (string)($identity?->get('use_service_id') ?? '');

    // ===== メニュー取得 =====
    $menus = $this->fetchTable('MMenu')
        ->find()
        ->innerJoin(
            ['Auth' => 'M_AUTH'],
            [
                'Auth.menu_id = MMenu.menu_id',
                'Auth.use_service_id' => $useServiceId,
                'Auth.use_div' => 1,
            ]
        )
        ->where(['MMenu.del_flg' => 0])
        ->order(['MMenu.disp_no' => 'ASC'])
        ->all();

    $menuTree = [];
    foreach ($menus as $m) {
        $menuTree[$m->parent_menu_name][$m->sub_menu_name][] = $m;
    }

    // ==================================================
    // ✅ 区分マスタ取得（お知らせマスタより）
    // ==================================================
    $announceDivList = $this->fetchTable('MAnnounceDiv')
        ->find('list', [
            'keyField'   => 'announce_div',
            'valueField' => 'announce_div_name'
        ])
        ->where(['del_flg' => 0])
        ->order(['announce_div' => 'ASC'])
        ->toArray();

    // ==================================================
    // ✅ 区分（抽出条件）取得
    // ==================================================
    $selectedDiv = $this->request->getQuery('announce_div');

    // ==================================================
    // ✅ お知らせ一覧（抽出条件反映）
    // ==================================================
    $announceQuery = $this->fetchTable('TAnnounce')
        ->find()
        ->where(['del_flg' => 0]);

    // 区分指定あり（空＝すべて は除外）
    if (!empty($selectedDiv)) {
        $announceQuery->where(['announce_div' => $selectedDiv]);
    }

    $announces = $announceQuery
        ->order(['announce_start_date' => 'DESC'])
        ->all();

    // ===== 添付ファイル有無判定 =====
    foreach ($announces as $a) {
        $a->has_file = false;
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($a->{"temp_filename{$i}"})) {
                $a->has_file = true;
                break;
            }
        }
    }

    // ==================================================
    // ✅ 次回締切日・該当献立週（DB正）
    // ==================================================
    $conn = ConnectionManager::get('default');

    $term = $conn->execute(
        "
        SELECT TOP 1
            start_date,
            end_date,
            add_deadline_date
        FROM dbo.M_TERM
        WHERE add_deadline_date >= CAST(GETDATE() AS date)
          AND del_flg = 0
        ORDER BY add_deadline_date ASC
        "
    )->fetch('assoc');

    $nextDeadline = '-';
    $menuWeek     = '-';

    if ($term) {
        if (!empty($term['add_deadline_date'])) {
            $nextDeadline = (new \DateTimeImmutable($term['add_deadline_date']))
                ->format('Y/m/d');
        }

        $menuWeek =
            (new \DateTimeImmutable($term['start_date']))->format('Y/m/d')
            . ' ～ ' .
            (new \DateTimeImmutable($term['end_date']))->format('Y/m/d');
    }

    // ===== View =====
    $this->set([
        'menuTree'        => $menuTree,
        'announces'       => $announces,
        'count'           => $announces->count(),
        'announceDivList' => $announceDivList, // ← セレクト用
        'selectedDiv'     => $selectedDiv,     // ← 選択保持
        'nextDeadline'    => $nextDeadline,
        'menuWeek'        => $menuWeek,
    ]);
}



//お知らせ切替処理
private function loadAnnounce(?string $selectedDiv = null): array
{
    $start = microtime(true);

    $TAnnounce = $this->fetchTable('TAnnounce');

    // ★ ログインユーザー情報
    $identity = $this->request->getAttribute('identity');
    $viewerId = (string)($identity?->get('user_id') ?? '');
    $useSvc   = (string)($identity?->get('use_service_id') ?? '');

    $orderBy = [
        'TAnnounce.announce_start_date' => 'DESC',
        'TAnnounce.announce_id'         => 'DESC', // 同日対策のセカンダリ
    ];

    if ($viewerId === '' && $useSvc !== '1') {
        Log::warning('[loadAnnounce] user_id が空のため 0件を返します');
        $tAnnounce = $TAnnounce->find()->where(['1 = 0'])->order($orderBy)->all();
        $count = 0;
        return compact('tAnnounce', 'count');
    }

    // ▼ 閲覧者(=サービス5)だけ「閲覧先ID一覧」を取得（ORMで取得／Connectionは使わない）
    $viewIds = null;
    if ($useSvc === '5') {
        $DispUser = $this->getTableLocator()->get('MDispUser');

        // 自分が閲覧者（disp_user_id = 自分）の行を取り、閲覧先（user_id）一覧を配列に
        $viewIds = $DispUser->find()
            ->select(['disp_user_id'])           // 取得するのは対象側
            ->where(['user_id' => $viewerId])    // 閲覧者=左
            ->enableHydration(false)
            ->all()
            ->extract('disp_user_id')            // 抽出キーも対象側に
            ->toList();

        // 重複除去
        $viewIds = array_values(array_unique($viewIds));

        if (empty($viewIds)) {
            Log::debug("[loadAnnounce] viewer={$viewerId} の閲覧先が0件");
            $tAnnounce = $TAnnounce->find()->where(['1 = 0'])
            ->order($orderBy)->all();
            $count = 0;
            return compact('tAnnounce', 'count');
        }
    }

    $baseWhere = [
        'TAnnounce.del_flg'    => 0,
        'TAnnounce.visibility' => 1,
    ];

    // DB側の「今日（DATE）」を式で作る
    $todayExpr = new FunctionExpression('CONVERT', [
        new IdentifierExpression('date'),     // ← 型キーワードは Identifier として渡す（クォートさせない）
        new FunctionExpression('GETDATE'),    // ← 関数呼び出しは FunctionExpression で
    ]);

    $baseWhere['TAnnounce.announce_start_date <='] = $todayExpr;
    $baseWhere['TAnnounce.announce_end_date >=']   = $todayExpr;

    if ($selectedDiv !== null && $selectedDiv !== '') {
        $baseWhere['TAnnounce.announce_div'] = $selectedDiv;
    }

    // ▼ 件数／明細の構築（★サービス別分岐）  
    if ($useSvc === '1') {
        // マスター＝全件表示（TAnnounceUserに依存しない）
        $count = (int)$TAnnounce->find()
            ->select(['cnt' => $TAnnounce->find()->func()->count('DISTINCT TAnnounce.announce_id')])
            ->where($baseWhere)
            ->enableHydration(false)
            ->first()['cnt'];

        $tAnnounce = $TAnnounce->find()
            ->select($TAnnounce)
            ->enableAutoFields(false)
            ->where($baseWhere)
            ->distinct(true) 
            ->order($orderBy)
            ->all();


    } else {
        // 閲覧者(5)＝IN($viewIds)／通常(2〜4)＝自分宛
        $userFilter = ($useSvc === '5')
            ? ['TAnnounceUser.user_id IN' => $viewIds]
            : ['TAnnounceUser.user_id' => $viewerId];

        $count = (int)$TAnnounce->find()
            ->select(['cnt' => $TAnnounce->find()->func()->count('DISTINCT TAnnounce.announce_id')])
            ->innerJoin(['TAnnounceUser' => 't_announce_user'], ['TAnnounceUser.announce_id = TAnnounce.announce_id'])
            ->where($baseWhere + $userFilter)
            ->enableHydration(false)
            ->first()['cnt'];

        $tAnnounce = $TAnnounce->find()
            ->select($TAnnounce)
            ->enableAutoFields(false)
            ->innerJoin(['TAnnounceUser' => 't_announce_user'], ['TAnnounceUser.announce_id = TAnnounce.announce_id'])
            ->where($baseWhere + $userFilter)
            ->distinct(true)
            ->order($orderBy)
            ->all();
    }

    Log::debug("[loadAnnounce] viewer={$viewerId} useSvc={$useSvc} rows=" . $tAnnounce->count());
    return compact('tAnnounce', 'count');
}
public function download(string $fname)
{
    try {
        $this->request->allowMethod(['post']);

        // 入力ログ
        \Cake\Log\Log::debug('[download] ENTER fname(raw)=' . $fname
            . ' method=' . $this->request->getMethod());

        // ファイル名バリデーション（日本語/空白/()はOK、.. と / \ はNG）
        $fname = trim($fname);
        if ($fname === '' || str_contains($fname, '..') || str_contains($fname, '/') || str_contains($fname, '\\')) {
            \Cake\Log\Log::error('[download] BadRequest: invalid fname=' . $fname);
            throw new \Cake\Http\Exception\BadRequestException('不正なファイル名です。');
        }

        $dir  = WWW_ROOT . 'uploads' . DS . 'announce' . DS;
        $path = $dir . $fname;

        // 事前診断ログ
        $exists = is_file($path);
        $size   = $exists ? @filesize($path) : 0;
        \Cake\Log\Log::debug(sprintf('[download] path=%s exists=%s size=%s',
            $path, $exists ? '1' : '0', (string)$size));

        if (!$exists) {
            \Cake\Log\Log::error('[download] NotFound: ' . $path);
            throw new \Cake\Http\Exception\NotFoundException('ファイルが見つかりません。');
        }
        // MIME を拡張子で指定（PDF/Excel）
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'  => 'application/pdf',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => null,
        };

        $options = ['download' => true, 'name' => $fname];
        if ($mime) $options['contentType'] = $mime;

        // 余計な出力の可能性を潰す
        while (ob_get_level() > 0) { @ob_end_clean(); }

        // MIME 確定（既に $mime 生成済みならそれを使う）
        $mime = $mime ?? 'application/octet-stream';
        $disposition = "attachment; filename*=UTF-8''" . rawurlencode($fname);

        // 手動で読んで返す（Windows + 日本語名でも安定）
        $data = @file_get_contents($path);
        if ($data === false) {
            \Cake\Log\Log::error('[download] file_get_contents failed for ' . $path);
            throw new \RuntimeException('read failed');
        }

        return $this->response
            ->withType($mime)
            ->withHeader('Content-Disposition', $disposition)
            ->withHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withStringBody($data);
    } catch (\Throwable $e) {
        \Cake\Log\Log::error('[download FATAL] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        throw $e;
    }
   

}

}

