<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MDelivery> $mDelivery
 * @var int $count
 * @var string|null $deliveryId
 * @var string|null $deliveryName
 * @var bool $includeDeleted
 */

/* ▼ ソート矢印テンプレート */
$this->Paginator->setTemplates([
    'sort'     => '<a href="{{url}}">{{text}}</a>',
    'sortAsc'  => '<a href="{{url}}">{{text}} <span class="sort-arrow">↑</span></a>',
    'sortDesc' => '<a href="{{url}}">{{text}} <span class="sort-arrow">↓</span></a>',
]);

$this->Form->setTemplates([
    'inputContainer' => '{{content}}',
]);
?>

<div class="MDelivery index content">

    <!-- ⭐ GETフォーム（検索 + 削除データ切替） -->
    <?= $this->Form->create(null, ['type' => 'get']) ?>

    <div class="title_box">
        <h2 class="title">献立商品マスタ</h2>

        <!-- 削除データ切替 -->
        <div class="deleted-filter">
            <span class="filter-label">削除データ</span>

            <?= $this->Form->checkbox('include_deleted', [
                'value'       => '1',
                'hiddenField' => '0',
                'checked'     => $includeDeleted,
                'id'          => 'include_deleted',
                'onchange'    => 'this.form.submit();'
            ]) ?>

            <label for="include_deleted" class="filter-text">削除データを含める</label>
        </div>
    </div>

    <!-- ⭐ 検索枠 -->
    <div class="search-box-wrapper-food">
        <div class="search-box-food">
            <div class="search-col">
                <div class="search-field">
                    <?= $this->Form->control('delivery_id', [
                        'label' => '献立商品ID(完全一致)',
                        'type' => 'text',
                        'value' => $deliveryId
                    ]) ?>
                </div>

                <div class="search-field">
                    <?= $this->Form->control('delivery_name', [
                        'label' => '商品名称(部分一致)',
                        'type' => 'text',
                        'value' => $deliveryName
                    ]) ?>
                </div>
            </div>

            <div class="search-col">
                <div class="search-field-food">
                    <?= $this->Form->submit('検索') ?>
                </div>
            </div>
        </div>
    </div>

    <?= $this->Form->end() ?>

    <!-- 件数 -->
    <p class="title2" style="text-align:right">件数 <?= h($count) ?> 件</p>


    <!-- ⭐ POSTフォーム -->
    <?= $this->Form->create(null, ['type' => 'post']) ?>

    <div class="scrollbox">
        <table class="styled-table">
            <thead>
                <tr>
                    <th class="col-1">選択</th>
                    <th class="col-2"><?= $this->Paginator->sort('delivery_id', '献立商品ID') ?></th>
                    <th class="col-2"><?= $this->Paginator->sort('delivery_name', '商品名称') ?></th>
                    <th class="col-2"><?= $this->Paginator->sort('del_flg', '削除') ?></th>
                    <th class="col-3"><?= $this->Paginator->sort('disp_no', '表示順') ?></th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($mDelivery as $m): ?>
                <tr>
                    <td class="col-1">
                        <?= $this->Form->checkbox("select[{$m->delivery_id}]", ['class' => 'row-check']) ?>
                    </td>

                    <td class="col-2"><?= h($m->delivery_id) ?></td>
                    <td class="col-2"><?= h($m->delivery_name) ?></td>
                    <td class="col-2"><?= $m->del_flg == 1 ? '✓' : '' ?></td>
                    <td class="col-3"><?= $m->del_flg == 1 ? '999' : h($m->disp_no) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ⭐ 固定ボタン（スクロールしても常に下に表示） -->
    <div class="footer-buttons">
        <div class="footer-inner">

            <div class="leftbox">
                <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
                <?= $this->Form->button('編集', ['name' => 'action', 'value' => 'edit']) ?>
                <?= $this->Form->button('削除', [
                    'name' => 'action',
                    'value' => 'delete',
                    'onclick' => 'return checkBeforeDelete();'
                ]) ?>
            </div>

            <div class="rightbox">
                <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], [
                    'class' => 'button',
                ]) ?>
            </div>

        </div>
    </div>

    <?= $this->Form->end() ?>

</div>


<script>
function checkBeforeDelete() {
    const checked = document.querySelectorAll('input[name^="select["]:checked');
    if (checked.length === 0) return true;
    return confirm(`${checked.length}件選択されています。\n本当に削除しますか？`);
}

$(document).ready(function() {
    $('.row-check').on('change', function() {
        let row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            row.addClass('highlight-row');
        } else {
            row.removeClass('highlight-row');
        }
    });
});
</script>


<style>
/* ▼ テーブルヘッダー固定（右UI基準） */
.styled-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #FFE5E5 !important;
    color: #555;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid #ccc;
}

/* ▼ スクロール領域を調整（ボタンが隠れない） */
.scrollbox {
    max-height: calc(100vh - 330px);
    overflow-y: auto;
    padding-bottom: 10px;
}

/* ▼ フッターボタン固定 */
.footer-buttons {
    position: sticky;
    bottom: 20px;       /* ←⭐ ボタン位置を上に移動 */
    background: #fff;
    padding: 20px 0;    /* ← 余白を増やして見やすく */
    border-top: 1px solid #ccc;
    margin-top: 15px;
    z-index: 20;
}

.footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 5px;
}

/* ▼ 行選択ハイライト */
.highlight-row { background-color: #D0EBFF; }

/* ▼ 列幅 */
.col-1 { max-width: 80px; text-align: center; }
.col-2 { max-width: 500px; word-break: break-word; white-space: normal; }
.col-3 { max-width: 80px; text-align: center; }

/* ▼ 検索UI（右形式） */
.search-box-wrapper-food {
    display: flex;
    justify-content: center;
    padding: 1rem;
}
.search-box-food {
    display: flex;
    gap: 4rem;
    align-items: flex-end;
    width: 100%;
    justify-content: center;
    padding: 2rem;
    border: 1.5px solid #ccc;
    border-radius: .4rem;
    background: #fff;
}
.search-field-food {
    display: flex; flex-direction: column;
    min-width: 200px;
    text-align:center;
}
.button,
input[type="submit"],
button {
    background-color: #d9534f !important;
    color: #fff !important;

    border: none !important;
    border-radius: 6px !important;

    padding: 8px 20px !important;
    height: 38px !important;
    line-height: 1.3 !important;
    font-size: 14px !important;

    display: inline-flex !important;
    align-items: center;
    justify-content: center;

    text-decoration: none !important;
    cursor: pointer;
    box-sizing: border-box;
}
</style>
