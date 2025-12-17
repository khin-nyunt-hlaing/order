<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MUser> $mUser
 */
?>
<div class="MUser index content">

<div class="title_box">
    <h2 class="title">施設一覧</h2>
</div>

<div class="search-box-wrapper">
<?= $this->Form->create(null, ['type' => 'post']) ?>
<?php $this->Form->setTemplates(['inputContainer' => '{{content}}']); ?>

<div class="search-box">

    <!-- 上段チェックボックス -->
    <div class="check-top">
        <label>
            <?= $this->Form->checkbox('include_menu_service', [
                'checked' => !empty($this->request->getData('include_menu_service'))
            ]) ?>
            献立サービスを含む
        </label>

        <label>
            <?= $this->Form->checkbox('include_food_service', [
                'checked' => !empty($this->request->getData('include_food_service'))
            ]) ?>
            単品食材サービスを含む
        </label>
    </div>

    <!-- 1段目 -->
    <div class="row-line">
        <div class="left-col">
            <label class="form-label"><span>施設グループ</span></label>
            <?= $this->Form->select('user_group_id', $groupList, [
                'empty' => '選択してください',
                'value' => $userGroupId
            ]) ?>
        </div>

        <div class="right-col">
            <label class="form-label"><span>施設番号</span></label>
            <?= $this->Form->text('user_id', ['value' => $userId]) ?>
            <span class="note">(完全一致)</span>
        </div>
    </div>

    <!-- 2段目 -->
    <div class="row-line">
        <div class="left-col">
            <label class="form-label"><span>施設名称</span></label>
            <?= $this->Form->text('user_name', ['value' => $userName]) ?>
            <span class="note">(部分一致)</span>
        </div>

        <div class="right-col state-area">
            <label>
                <?= $this->Form->checkbox('status[]', [
                    'value'   => '1',
                    'hiddenField' => false,
                    'checked' => in_array('1', $status, true)
                ]) ?>
                利用中
            </label>

            <label>
                <?= $this->Form->checkbox('status[]', [
                    'value'   => '0',
                    'hiddenField' => false,
                    'checked' => in_array('0', $status, true)
                ]) ?>
                準備中
            </label>

            <label>
                <?= $this->Form->checkbox('status[]', [
                    'value'   => '2',
                    'hiddenField' => false,
                    'checked' => in_array('2', $status, true)
                ]) ?>
                取引停止
            </label>

            <?= $this->Form->submit('検索', ['class' => 'search-btn']) ?>
        </div>
    </div>

</div>

<?= $this->Form->end() ?>
</div>
<p class="count-right">件数 <?= h($count) ?> 件</p>

    <!--フォーム開始：ボタンとチェックボックス送信用-->
    <?= $this->Form->create(null, ['type' => 'post']) ?>

    <div class="Extractscrollbox">
        <table class="styled-table">
        <thead>
            <tr>
                <th>選択</th>
                <th>施設番号</th>
                <th>施設名称</th>
                <th>施設グループCD</th>
                <th>施設グループ名</th>
                <th>発注サービス</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($mUser as $user): ?>
            <?php if (!in_array($user->status, [0, 1, 2])) continue; ?>

            <tr>
                <td>
                    <?= $this->Form->control("select[{$user->user_id}]", [
                        'type' => 'checkbox',
                        'label' => false,
                        'class' => 'toggle-color',
                    ]) ?>
                </td>

                <!-- 施設番号 -->
                <td>
                    <?= $this->Html->link(
                        h($user->user_id),
                        ['action' => 'edit', $user->user_id],
                        ['class' => 'user-id-link']
                    ) ?>
                </td>

                <!-- 施設名称 -->
                <td><?= h($user->user_name) ?></td>

                <!-- 施設グループCD -->
                <td><?= h($user->user_group_id) ?></td>

                <!-- 施設グループ名 -->
                <td><?= h($user->user_group_name) ?></td>

                <!-- 発注サービス -->
                <td><?= h($user->service->service_name ?? '未設定') ?></td>

                <!-- 状態 -->
                <td>
                    <?php if ($user->status == 0): ?>準備中
                    <?php elseif ($user->status == 1): ?>利用中
                    <?php elseif ($user->status == 2): ?>取引停止
                    <?php endif; ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">

    <!-- 操作ボタン -->
    <div class="leftbox">
    <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
    <!-- <?= $this->Form->button('編集', ['name' => 'action', 'value' => 'edit']) ?> -->
    <!-- <?= $this->Form->button('削除', [
            'name' => 'action',
            'value' => 'delete',
            'onclick' => 'return checkBeforeDelete();'
        ]) ?>    -->
    </div>
    <!--戻るリンク -->
    <div class="rightbox">
        <?= $this->Html->link('戻る', ['controller' => 'Mmenus','Mmenu' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
    </div>
    <!--フォーム終了 -->
    <?= $this->Form->end() ?>
</div>


<style>
.MUser.index.content{
    max-height:100%;
}

.inbox{
    margin-bottom: 0;
}

.intable {
    width: 95%;
    border-collapse: collapse;
}

td, th {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
    height: 40px;
}

.intable th {
    background-color: #f2f2f2;
}
.search-box-wrapper {
    width: 100%;
    margin-bottom: 20px;
}


.MUser .search-box {
    width: 100% !important;
    max-width: 1400px !important;
    min-width: 1000px !important;

    margin: 0 auto !important;

    border: 1.5px solid #ccc !important;
    border-radius: 0.5rem !important;
    background: #fff !important;

    padding: 17px 35px !important;
    box-sizing: border-box !important;

    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
}


.MUser .check-top {
    flex: 0 0 auto !important;    /* ← 幅を自動にして "左から詰まる"  */
    width: auto !important;
    align-self: flex-start !important; /* ← 親が flex でも左端に寄せる */
    
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    gap: 30px !important;
    padding: 0 !important;
    margin: 0 !important;
}

/* ラベルの強制左寄せ */
.MUser .check-top label {
    display: flex !important;
    align-items: center !important;
    white-space: nowrap !important;
    margin: 0 !important;
    padding: 0 !important;
    gap: 10px !important;
}
.MUser .row-line {
    display: flex !important;
    justify-content: space-between !important;
    width: 100% !important;
    
}

.MUser .left-col,
.MUser .right-col {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
   
}



.MUser .form-label {
    display: inline-block !important;
    vertical-align: middle !important;
    margin: 0 !important;
    padding: 0 !important;
}

.MUser .form-label span {
    display: inline-block !important;
    height: 38px !important;
   
    text-align: right !important;
    white-space: nowrap !important;
    width: 110px !important;
}


.MUser select,
.MUser input[type="text"] {
    height: 38px;
}


.MUser .note {
    margin-left: 5px !important;
    white-space: nowrap !important;
    align-items: center !important;
    height: 38px !important;
}

.MUser .state-area {
    display: flex !important;
    align-items: center !important;   /* 縦中央を揃える */
    gap: 25px !important;
    height: 38px !important;             /* チェックボックス間の幅を調整 */
}
.MUser .state-area label {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;     /* ← チェックボックスと文字の距離を詰める */
    white-space: nowrap !important;
    height: 38px !important;
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
        $('input[type="checkbox"]').on('change', function() {
            let row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.addClass('highlight-row');
            } else {
                row.removeClass('highlight-row');
            }
        });
    });

//     function checkBeforeDelete() {
//     const checked = document.querySelectorAll('input[name^="select["]:checked');
//     const count = checked.length;

//     if (count === 0) {
        
//         return true; // フォームは送信する
//     }

//     return confirm(`${count}件選択されています。\n本当に削除しますか？`);
// }
function setUserGroup(groupId, groupName) {

    // ▼ 施設グループ select に値をセット
    const select = document.querySelector('select[name="user_group_id"]');
    if (select) {
        select.value = groupId;
    }

    // ▼ 検索条件を反映して再検索
    const form = document.getElementById('searchForm');
    if (form) {
        form.submit();
    }
}
</script>