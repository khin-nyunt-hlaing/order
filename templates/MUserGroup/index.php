<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MUserGroup> $mUserGroup
 */
?>
<div class="mUserGroup index content">
    <div class="title_box">
        <h2 class="title">施設グループマスタ</h2>
        <div class="show-deleted-area">
            <span class="deleted-btn">削除データ</span>

            <label class="deleted-check">
                <input type="checkbox"
                    id="del_flg"
                    <?= $this->request->getQuery('del_flg') === '1' ? 'checked' : '' ?>>
                <span>削除データを含める</span>
            </label>
        </div>
        <p class="title2">件数 <?= h($count) ?> 件</p>
    </div>
    <!-- ===== 検索条件（GET） ===== -->
    <div class="search-box-wrapper">
        <?= $this->Form->create(null, ['type' => 'get', 'id' => 'searchForm']) ?>
        <?= $this->Form->hidden('del_flg', [
            'id' => 'search_del_flg',
            'value' => $this->request->getQuery('del_flg') === '1' ? '1' : ''
        ]) ?>
        <?php $this->Form->setTemplates(['inputContainer' => '{{content}}']); ?>

        <div class="search-box">

            <div class="group-search two-col">

                <!-- 左列 -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">
                            施設グループコード（完全一致）
                        </div>
                        <?= $this->Form->text('user_group_id', [
                            'value' => $this->request->getQuery('user_group_id')
                        ]) ?>
                    </div>           
                </div>

                <!-- 右列 -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">
                            施設グループ名（部分一致）
                        </div>
                        <?= $this->Form->text('user_group_name', [
                            'value' => $this->request->getQuery('user_group_name')
                        ]) ?>
                    </div>

                    
                </div>
                <div class="group-item btn-row">
                    <?= $this->Form->submit('検索', ['class' => 'search-btn']) ?>
                </div>


            </div>
        </div>

        <?= $this->Form->end() ?>
    </div>
    <?= $this->Form->create(null, ['type' => 'file']) ?>
        <div class="scrollbox">
            <table class="styled-table">
            <thead>
                <tr>
                    <th>選択</th>
                    <th>施設グループ番号</th>
                    <th>施設グループ名称</th>
                    <th>削除</th>
                    <th>表示順</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mUserGroup as $mUserGroup): ?>
                <tr>
                <!-- :小さいひし形_オレンジ: チェックボックス（複数選択可能） -->
                    <td>
                       <?= $this->Form->control("select[{$mUserGroup->user_group_id}]", [
                       'type' => 'checkbox',
                      'label' => false,
                          ]) ?>
                     </td>
                    <!-- :小さいひし形_オレンジ: 各データ列 -->
                    <td>
                        <?= $this->Html->link(
                            h($mUserGroup->user_group_id),
                            ['action' => 'edit', $mUserGroup->user_group_id],
                            ['class' => 'link-edit']
                        ) ?>
                    </td>
                    <td><?= h($mUserGroup->user_group_name) ?></td>
                    <td style="text-align:center;"><?= $mUserGroup->del_flg == 1 ? '✓' : '' ?></td>
                    <td><?= h($mUserGroup->disp_no) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
        </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                     <!-- :下向き三角矢印: 操作ボタン -->
                        <div class="leftbox">
                        <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
                        <!-- <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?> -->
                        <?= $this->Form->button('削除', [
                            'name' => 'action',
                            'value' => 'delete',
                            'onclick' => 'return checkBeforeDelete();
                            ']) ?>
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
    margin-right: auto;
    padding:5px;
}
.rightbox{
    margin-left: auto;
    padding:5px;
}

.highlight-row {
    background-color: #D0EBFF;
}

.group-search {
    width: 40%;
    max-width: 1400px;
    min-width: 1000px;
    margin: 0 auto;
    padding: 17px 35px;
    display: flex;
    font-size: 1.6rem;
    font-weight: 700;
}
.group-search.two-col {
    flex-direction: row;
}
.group-label-top {
    text-align: center;
}
.col {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.group-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.group-item input[type="text"] {
    height: 40px;
    width: 100%;
    max-width: 60%;
}
.note {
    margin-left: 4px;
    white-space: nowrap;
}
.btn-row {
    align-items: flex-end;
    padding-top: 30px;
}

.show-deleted-area {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    line-height: normal;
}

.deleted-btn {
    background-color: #49c5b6;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.9rem;
    display: inline-block;
    line-height: normal;
    white-space: nowrap;
}

.deleted-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    cursor: pointer;
}

.deleted-check input[type="checkbox"] {
    margin: 0;
    vertical-align: middle;
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
    document.addEventListener('DOMContentLoaded', function () {
        const check  = document.getElementById('del_flg');       // title側チェック
        const hidden = document.getElementById('search_del_flg'); // searchForm内
        const form   = document.getElementById('searchForm');

        if (!check || !hidden || !form) return;

        // 初期同期
        hidden.value = check.checked ? '1' : '';

        check.addEventListener('change', function () {
            hidden.value = this.checked ? '1' : '';
            form.submit();
        });
    });
</script>