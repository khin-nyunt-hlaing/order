<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MAnnounceDiv> $mAnnounceDiv
 */
?>
<div class="mAnnounceDiv index content">
    <div class="title_box">
        <h2 class="title">お知らせ区分一覧</h2>
           <?= $this->element('show_deleted_filter') ?>
           
        <p class="title2">件数 <?= h($count) ?> 件</p>
    </div>
    <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="scrollbox">
            <table class="styled-table">
            <thead>
                <tr>
                    <th>選択</th>
                    <th>お知らせ区分</th>
                    <th>お知らせ区分名称</th>
                    <th>表示順</th>
                    <th>削除</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mAnnounceDiv as $mAnnounceDiv): ?>
                <tr>
                <!-- チェックボックス（複数選択可能） -->
                    <td>
                       <?= $this->Form->control("select[{$mAnnounceDiv->announce_div}]", [
                       'type' => 'checkbox',
                      'label' => false,
                          ]) ?>
                     </td>
                    <!-- 各データ列 -->
                     <td>
                        <?= $this->Html->link(
                            h($mAnnounceDiv->announce_div),
                            ['action' => 'edit', $mAnnounceDiv->announce_div],
                            ['class' => 'link-edit']
                        ) ?>
                    </td>
                    
                    <td><?= h($mAnnounceDiv->announce_div_name) ?></td>
                    <td><?= h($mAnnounceDiv->disp_no) ?></td>
                    <td style="text-align:center;"><?= $mAnnounceDiv->del_flg == 1 ? '✓' : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
        </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                     <!-- :下向き三角矢印: 操作ボタン -->
                        <div class="leftbox">
                        <?= $this->Form->button('登録', ['name' => 'action', 'value' => 'add']) ?>
                        <!-- <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?> -->
                        <?= $this->Form->button('削除', [
                            'name' => 'action',
                            'value' => 'delete',
                            'onclick' => 'return checkBeforeDelete();'
                            ]) ?>
                        </div>
        <!-- :下向き三角矢印: フォーム終了 -->
        <?= $this->Form->end() ?>
                        <!-- :下向き三角矢印: 戻るリンク -->
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
        background-color: #D0EBFF; /* 濃いめの青背景に変更 */
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