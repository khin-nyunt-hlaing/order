<h3 class="cuttitlebox" style='font-size:1.5em;'>メインメニュー</h3>

<div class="menu content">
    <?php foreach ($menus as $menu): ?>
        <?php
            // 初期値はリンクなし
            $link = null;

            // 配列かオブジェクトかを吸収
            $controller = is_array($menu) ? ($menu['controller'] ?? null) : ($menu->controller ?? null);
            $action     = is_array($menu) ? ($menu['action'] ?? null)     : ($menu->action ?? null);
            $menuName   = is_array($menu) ? ($menu['menu_name'] ?? null)  : ($menu->menu_name ?? null);
            $useDiv     = is_array($menu) ? ($menu['use_div'] ?? null)    : ($menu->use_div ?? null);


            if ($menu->action === 'logout') {
                $link = ['controller' => 'MUser', 'action' => 'logout', 'plugin' => false];
            }

            // CONTROLLERとACTIONが設定されている場合、かつコントローラーが存在する場合のみリンクを作成
            if (!empty($menu->controller) && !empty($menu->action)) {
                $controllerClass = 'App\\Controller\\' . $menu->controller . 'Controller';
                

            // controller / action があればリンク作成（まずは存在チェックだけでOK）
            if (!$link && !empty($controller) && !empty($action)) {
                $link = ['controller' => $controller, 'action' => $action, 'plugin' => false];
            }
            }
            // use_div=1 だけ活性（それ以外は見せるけど無効）
            $disabled = ($useDiv !== 1);
        ?>

        <?php if (!empty($menu['menu_name'])): ?>
            <?php if (!empty($link)): ?>
                <!-- リンクがある場合 -->
                <?= $this->Html->link(
                    h($menu['menu_name']),
                    $link,
                    ['class' => 'menu-button']
                ) ?>
            <?php else: ?>
                <!-- リンクがない場合は文字だけ表示 -->
                <span class="menu-button"><?= h($menu['menu_name']) ?></span>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
           
<div style="display:flex; align-items: center; height:60px;">
<h3 class="cuttitlebox" style="margin-left:20px;">お知らせ</h3>
<p class="p1"style="margin-left:auto;">件数  <?= $count ?>件</P1>
</div>
<div class="Announcebox">
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th style="width:20%;">日付</th>
            <th style="display:flex; align-items: center;">
                <label style=" width:70%;">区分</label>
                <?= $this->Form->select('announce_div', $announceDivList, [
                    'empty' => 'すべて',               // ← 空値 ""
                    'value' => $selectedDiv ?? '',   // ← 現在値を保持
                    'style' => 'width:100%; margin-bottom:0 !important;',
                    'id'    => 'announceDiv'
                ]) ?>
            </th>
            <th style="width:60%;">お知らせ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tAnnounce as $announce): ?>
            <?php
                $modalId = 'modal-announce-' . $announce->announce_id;
                $title = $announce->announce_title ?? 'タイトル未設定';

                $attachedFiles = [];
                for ($i = 1; $i <= 5; $i++) {
                    $prop = "temp_filename{$i}";
                    $fname = $announce->$prop ?? null;
                    if (!empty($fname)) {
                        $attachedFiles[] = [
                            // 公開URLは announce 配下。ファイル名だけ URL エンコード
                            'url'  => $this->Url->assetUrl('uploads/announce/' . rawurlencode($fname)),
                            'name' => $fname, // 表示側で h() する
                        ];
                    }
                }
            ?>
    <tr>
        <td><?= h($announce->announce_start_date ?? '') ?></td>
        <td><?= h($announceDivList[$announce->announce_div] ?? '') ?></td>
        <td>
        <!-- 開くトリガー -->
        <button type="button"
                class="openModalBtn"
                data-target="<?= h($modalId) ?>"
                aria-controls="<?= h($modalId) ?>"
                aria-haspopup="dialog"
                style="cursor:pointer; color:blue; text-decoration:underline; background:none; border:none; padding:0;">
            <?= h($title) ?>
        </button>

        <!-- モーダル本体（element 呼び出し） -->
        <?= $this->element('modal_box', [
            'id'            => $modalId,
            'announceTitle' => $announce->announce_title,
            'announceText'  => $announce->announce_text,
            'attachedFiles' => $attachedFiles
        ]) ?>
        </td>
    </tr>
<?php endforeach; ?>

    </tbody>
</table>
        </div>
    
    
<style>
    .menu {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .menu-button {
        display: inline-block;
        padding: 15px 25px;
        background-color: #e6f7ff;
        border: 2px solid #aaa;
        text-decoration: none;
        color: #333;
        font-size: 18px;
        text-align: center;
    }

    .menu-button:hover {
        background-color: #b3e0ff;
    }
    .Announcebox{
        max-height: 300px; 
        overflow-y: auto; 
        border: 3px solid #ccc; 
        padding: 10px;
        background: white;
    }
    .p1{
        text-align: right;
        margin-bottom:0px;
    }
</style>
<script>
    <?php $this->Html->scriptStart(['block' => true]); ?>
    // 開く
    document.addEventListener('click', (e) => {
    const btn = e.target.closest('.openModalBtn');
    if (!btn) return;
    const id = btn.dataset.target;               // 例: "modal-announce-123"
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.hidden = false;

    // 初期フォーカス（任意）
    const focusable = modal.querySelector('[data-close]') || modal;
    focusable.focus();
    });

    // 閉じる（×ボタンや背景クリック）
    document.addEventListener('click', (e) => {
    const close = e.target.closest('[data-close]');
    if (!close) return;
    const modal = close.closest('.modal');
    if (modal) modal.hidden = true;
    });

    // Escで全部閉じる
    document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal:not([hidden])')
        .forEach(m => m.hidden = true);
    }
    });
    <?php $this->Html->scriptEnd(); ?>
</script>
<!-- お知らせ区分に従って表示 -->
<script>
    document.getElementById('announceDiv').addEventListener('change', function() {
        const selected = this.value;
        const baseUrl = window.location.pathname;
        const params = new URLSearchParams(window.location.search);
        if (selected) {
            params.set('announce_div', selected);
        } else {
            params.delete('announce_div');
        }
        window.location.href = `${baseUrl}?${params.toString()}`;
    });
</script>