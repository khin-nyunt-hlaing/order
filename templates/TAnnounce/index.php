<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface $tAnnounce
 */
?>
<div class="tAnnounce index content">

    <h3 class="title" style="margin: 0;">お知らせ一覧</h3>

    <div class="search-box-wrapper">
    <?= $this->Form->create(null, [
        'type' => 'get',
        'url'  => ['action' => 'index']
    ]) ?>

    <?php
    // ★ これがないとラベルは絶対に横にならない
    $this->Form->setTemplates([
        'inputContainer' => '{{content}}'
    ]);
    ?>

    <div class="announce-search-grid">

        <!-- 1行目：掲載日付 + タイトル検索 -->
        <div class="lbl">掲載日付</div>
        <div class="date-range">
            <?= $this->Form->control('start_from', ['type'=>'date','label'=>false]) ?>
            <span class="tilde">～</span>
            <?= $this->Form->control('start_to', ['type'=>'date','label'=>false]) ?>
        </div>
        <div class="field-right">
            <label class="sr-label">タイトル検索</label>
            <?= $this->Form->control('title', [
                'type' => 'text',
                'label' => false
            ]) ?>
        </div>

        <!-- 2行目：掲載データ + 区分 -->
        <div class="lbl">掲載データ</div>
        <div class="field">
            <label class="checkline">
                <?= $this->Form->checkbox('include_end', ['value'=>1]) ?>
                掲載終了を含める
            </label>
        </div>
        <div class="field-right">
            <label class="sr-label">区分</label>
            <?= $this->Form->control('announce_div', [
                'type'    => 'select',
                'options' => $announceDivList ?? [],
                'empty'   => 'すべて',
                'label'   => false,
                'value'   => $this->request->getQuery('announce_div')
            ]) ?>
        </div>

        <!-- 3行目：施設グループ -->
        <div class="lbl">施設グループ</div>
        <div class="field">
            <?= $this->Form->control('facility_group', [
                'type'  => 'text',
                'label' => false,
                'value' => $this->request->getQuery('facility_group')
            ]) ?>
        </div>
        <div></div>

        <!-- 4行目：発注サービス + 検索 -->
        <div class="lbl">発注サービス</div>
        <div class="field">
            <?= $this->Form->control('use_service_id', [
                'type'    => 'select',
                'options' => $MServiceList,
                'empty'   => 'すべて',
                'label'   => false,
                'value'   => $this->request->getQuery('use_service_id')
            ]) ?>
        </div>
        <div class="search-field search-btn-wrap">
            <?= $this->Form->submit('検索', ['class' => 'search-btn']) ?>
        </div>

    </div>

    <?= $this->Form->end() ?>
    </div>



    <p class="countstatus" style="text-align:right;">件数 <?= $totalCount ?> 件</p>

<?= $this->Form->create(null, ['type' => 'post']) ?>
<div class="scrollbox">
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>選択</th>
            <th>日付</th>
            <th>区分</th>
            <th>お知らせ</th>
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
                            'url'  => $this->Url->assetUrl(
                                'uploads/announce/' . rawurlencode($fname),
                            ),
                            'name' => $fname, 
                        ];
                    }
                }
            ?>
                <tr>
                    <td><?= $this->Form->checkbox("select[{$announce->announce_id}]", ['class' => 'toggle-color']) ?></td>
                    <td>
                        <?= $this->Html->link(
                            h($announce->announce_start_date->format('Y-m-d')),
                            ['action' => 'edit', $announce->announce_id],
                            ['class' => 'link-edit']
                        ) ?>
                    </td>

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
                            'attachedFiles' => $attachedFilesMap[$announce->announce_id] ?? []
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    
    <!-- 🔽 操作ボタン -->
    <div>
    <?= $this->Form->button('新規', ['type' => 'submit','name' => 'action', 'value' => 'add']) ?>
    <!-- <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?> -->
    <?= $this->Form->button('削除', [
    'name' => 'action',
    'value' => 'delete',
    'onclick' => 'return checkBeforeDelete();'
    ]) ?>
    </div>

    <!-- 🔽 戻るリンク -->
    <div style="margin-top: 20px;">
        <?= $this->Html->link('戻る', ['controller' => 'Mmenus','action' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
</div>
 <!-- 🔽 フォーム終了 -->
<?= $this->Form->end() ?>

<style>
    .scrollbox {
    overflow-y: auto;
    max-height: 70vh;
    height: 60vh;
    border: 1.5px solid #ccc;
    }

    .countstatus{
        margin:0;
    }
    /* ① 表ヘッダーの装飾 */
    table thead th {
    background-color:#FDEAEA; /* 任意の色：薄い青系 */
    }
    td, th {
    border-bottom: 0.1rem solid #e1e1e1;
    padding: 1.5rem 2rem;  /* 上下:1.5rem, 左右:2rem に拡張 */
    }
    td:first-child, th:first-child {
    padding-left: 15px;
    }
    td:last-child, th:last-child {
    padding-right: 15px;
    }
    blockquote, dl, figure, form, ol, p, pre, table, ul {
    margin-bottom: 1.5rem;
    }
    .highlight-row {
    background-color: #d0ebff;
    }
    .deleted-filter {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 1rem;
    }

    .filter-label {
    background-color: #49c5b6;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.9rem;
    display: inline-block;
    }

    .filter-text {
    font-size: 0.9rem;
    cursor: pointer;
    }
.search-box-wrapper{
    width:100%;
    padding:2rem;
    margin-bottom:1.5rem;
    box-sizing:border-box;
    border:1.5px solid #ccc;
    border-radius:0.4rem;
    background:#fff;
}

.announce-search-grid{
    display:grid;
    grid-template-columns:110px 360px 1fr;

    column-gap:10px;
    align-items:center;
}

.lbl,
.sr-label{
    white-space:nowrap;
    color:#555;
    font-size:1.5rem;
    font-weight:600;
    text-align:right;
    justify-self:end;
    align-self:center;
    transform:translateY(-3px);
}

.date-range{
    display:flex;
    align-items:center;
    gap:10px;
}

.date-range input{
    width:160px;
    height:3rem;
    font-size:1.5rem;
}

.tilde{
    white-space:nowrap;
    font-size:1.5rem;
}

.field-right{
    display:grid;
    grid-template-columns:100px 1fr;
    align-items:center;
    column-gap:10px;
}

.field-right input,
.field-right select{
    width:100%;
    height:3rem;
    font-size:1.5rem;
}

.checkline{
    display:inline-flex;
    align-items:center;
    gap:8px;
    white-space:nowrap;
    font-size:1.5rem;
    font-weight:400;
    color:#555;
}

.checkline input{
    margin:0;
}

.btn-cell{
    display:flex;
    justify-content:flex-end;
    align-items:center;
}

/* ▼ ここが追加：下2つを掲載日付と同サイズにする */
.announce-search-grid .field input[type="text"],
.announce-search-grid .field select{
    width:160px;
    height:3rem;
    font-size:1.5rem;
}
.link-edit {
    color: #0000ee;
    text-decoration: underline;
    cursor: pointer;
}

.link-edit:hover {
    color: #551a8b;
}

</style>

<script>
    $(document).ready(function() {
        $('input.toggle-color[type="checkbox"]').on('change', function() {
            let row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.addClass('highlight-row');
            } else {
                row.removeClass('highlight-row');
            }
        });
    });
</script>
<script>
    function checkBeforeDelete() {
        const checked = document.querySelectorAll('input[name^="select["]:checked');
        const count = checked.length;

        if (count === 0) {
            // alert("削除するお知らせを選択してください。");
            // return false;
            return true; // フォームは送信する
        }

        return confirm(`${count}件選択されています。\n本当に削除しますか？`);
    }
</script>