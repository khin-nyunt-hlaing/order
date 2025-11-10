<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MDelivery> $mDelivery
 */
?>
<div class="mDelivery index content">
    <div class="title_box">
        <h2 class="title">配食商品一覧</h2>
           <?= $this->element('show_deleted_filter') ?>
        <p class="title2">件数 <?= h($count) ?> 件</p>
    </div>
        <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="scrollbox">
            <table class="styled-table">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('選択') ?></th>
                    <th><?= $this->Paginator->sort('delivery_id', '配食商品ID') ?></th>
                    <th><?= $this->Paginator->sort('delivery_name', '商品名称') ?></th>
                    <th><?= $this->Paginator->sort('disp_no', '表示順') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mDelivery as $mDelivery): ?>
                <tr>
                    <td><?= $this->Form->checkbox("select[{$mDelivery->delivery_id}]", ['class' => 'row-check'])  ?>
                    </td>
                    <td><?= h($mDelivery->delivery_id) ?></td>
                    <td><?= h($mDelivery->delivery_name) ?></td>
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
</script>
