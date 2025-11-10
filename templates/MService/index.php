<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MService> $mservices
 */
?>
<div class="mservices index content">
    <h3>発注サービス一覧</h3>
    <p style="text-align:right">件数 <?= count($mservices) ?> 件</p>

    <!-- フォーム開始 -->
    <?= $this->Form->create(null, ['type' => 'post']) ?>
        <div class="tablebox" style="width: 100%; box-sizing: border-box;">
            <table class="styled-table" style="table-layout: fixed;">
                <thead>
                    <colgroup>
                        <col style="width: 200px;">
                        <col style="width: auto;">
                    </colgroup>

                    <tr>
                        <th style="width:15%">選択</th>
                        <th style="width:15%">サービスID</th>
                        <th style="width:60%">発注サービス名</th>
                        <th style="width:10%">表示順</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mservices as $mservice): ?>
                    <tr>
                        <td>
                            <?= $this->Form->control("select[{$mservice->use_service_id}]", [
                                'type' => 'checkbox',
                                'label' => false,
                            ]) ?>
                        </td>
                        <td><?= h($mservice->use_service_id) ?></td>
                        <td><?= h($mservice->service_name) ?></td>
                        <td><?= h($mservice->disp_no) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <div style="margin-left: 20px;">
            <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?>
        </div>
        <div>
            <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], 
                                            ['class' => 'button','style'=>'display: flex; align-items: center;']) ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>
<style>
    .mDeli_box1{
    display:flex;
    vertical-align: middle; /* 縦方向中央寄せ */
    height: 50px; /* 必要に応じて高さを設定 */
    gap:60px;
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