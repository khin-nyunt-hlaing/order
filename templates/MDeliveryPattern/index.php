<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MDeliveryPattern> $mDeliveryPattern
 */
?>
<div class="MDeliveryPattern index content">
    <div class="title_box">
    <h2 class="title">配食商品パターン一覧</h2>
    <?= $this->element('show_deleted_filter') ?>
    <p class="title2">件数 <?= h($count) ?> 件</p>
</div>

    <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="scrollbox">
            <table class="styled-table">
            <thead>
                <tr>
                    <th class="col-1"><?= $this->Paginator->sort('選択') ?></th>
                    <th class="col-2"><?= $this->Paginator->sort('delivery_pattern_name', '商品名称') ?></th>
                    <th class="col-3"><?= $this->Paginator->sort('disp_no', '表示順') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mDeliveryPattern as $pattern): ?>
                    <tr>
                        <td class="col-1"><?= $this->Form->checkbox("select[{$pattern->use_pattern_id}]", ['class' => 'row-check'])  ?></td>
                        <td class="col-2"><?= h($pattern->delivery_pattern_name) ?></td>
                        <td class="col-3"><?= $pattern->disp_no === null ? '' : h((string)$pattern->disp_no) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
        </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                     <!-- 🔽 操作ボタン -->
                        <div class="leftbox">
                        <?= $this->Form->button('追加', ['name' => 'action', 'value' => 'add']) ?>
                        <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?>
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
</script>
<style>
    .col-1{
        max-width: 80px;          /* ①狭め固定 */
        text-align: center;    /* センター寄せ */
    }
    .col-2{
        max-width: 500px;          /* ①狭め固定 */
        word-break: break-word;    /* 単語途中でも折返し */
        white-space: normal;       /* 通常改行を許可 */
    }
    .col-3{
        max-width: 80px;          /* ①狭め固定 */
        white-space: normal;       /* 通常改行を許可 */
        word-break: break-word;    /* 単語途中でも折返し */
        text-align: center;    /* センター寄せ */
    }
</style>