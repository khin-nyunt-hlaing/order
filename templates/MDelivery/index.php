<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MDelivery> $mDelivery
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MDelivery> $mDelivery
 * @var int $count
 * @var string|null $deliveryId
 * @var string|null $deliveryName
 * @var bool $includeDeleted
 */
?>
<div class="MDelivery index content">

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


    <div class="search-box-wrapper">
    <div class="search-box">

        <!-- 入力欄 -->
        <div class="search-inputs">
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

        <!-- 検索ボタン（下・右） -->
        <div class="search-button-area">
            <div class="search-field-food">
                    <?= $this->Form->submit('検索') ?>
                </div>
        </div>

    </div>
</div>

    <p class="count-right">件数 <?= h($count) ?> 件</p>
        <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="scrollbox">
            <table class="styled-table">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('選択') ?></th>
                    <th><?= $this->Paginator->sort('delivery_id', '配食商品ID') ?></th>
                    <th><?= $this->Paginator->sort('delivery_name', '商品名称') ?></th>
                    <th><?= $this->Paginator->sort('del_flg', '削除') ?></th>                    
                    <th><?= $this->Paginator->sort('disp_no', '表示順') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mDelivery as $mDelivery): ?>
                <tr>
                    <td><?= $this->Form->checkbox("select[{$mDelivery->delivery_id}]", ['class' => 'row-check'])  ?>
                    </td>
                    <td>
                        <?= $this->Html->link(
                           h($mDelivery->delivery_id),
                            ['action' => 'edit', $mDelivery->delivery_id],
                            ['class' => 'user-id-link']
                        ) ?>
                    </td>
                    
                    <td><?= h($mDelivery->delivery_name) ?></td>
                    <td><?= h($mDelivery->del_flg == 1 ? '✓' : '') ?></td>
                    <td><?= $mDelivery->disp_no === null ? '' : $this->Number->format($mDelivery->disp_no) ?></td>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
        </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                     <!-- 🔽 操作ボタン -->
                        <div class="leftbox">
                        <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
                        <!-- <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?> -->
                        <?= $this->Form->button('削除', [
                            'name' => 'action',
                            'value' => 'delete',
                            'onclick' => 'return checkBeforeDelete();'
                            ]) ?>
                        </div>
        <!-- 🔽 フォーム終了 -->
        <?= $this->Form->end() ?>
                        <!-- 🔽 戻るリンク -->
                    <div class="rightbox">
                        <?= $this->Html->link('戻る', ['controller' => 'Mmenus','action' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
                    </div>
            </div>
</div>
<style>
    .leftbox{
        margin-right: auto; /* これで右寄せになる */
        padding:5px;
    }
    .rightbox{
        margin-left: auto; /* これで右寄せになる */
        padding:5px;
    }

    .highlight-row {
        background-color: #d0ebff; /* 濃いめの青背景に変更 */
    }

/* 検索枠全体 */
.search-box {
    position: relative;          /* ★基準 */
    display: flex;
    flex-direction: column;      /* ★縦並び */
    align-items: center;
    width: 100%;
    gap: 2rem;
    padding: 2rem;
    border: 1.5px solid #ccc;
    border-radius: .4rem;
    background: #fff;
}

/* 入力欄（そのまま） */
.search-inputs {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* 検索ボタンを右下へ */
.search-button-area {
    position: absolute;
    right: 2rem;
    bottom: 0.5rem;
}

.user-id-link {
    color: #0000EE;          /* ブラウザ標準の青 */
    text-decoration: underline;
    cursor: pointer;
}

.user-id-link:visited {
    color: #551A8B;          /* 訪問済み（任意） */
}

.user-id-link:hover {
    text-decoration: underline;
}
</style>
<script>
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
    function toggleDeleted() {
    const checked = document.getElementById('include_deleted').checked ? 1 : 0;

    const params = new URLSearchParams(window.location.search);

    // 削除データ切替
    params.set('include_deleted', checked);

    // ページングを戻す（重要）
    params.delete('page');

    // GETで再遷移
    window.location.search = params.toString();
}
</script>