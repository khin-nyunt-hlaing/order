<div class="mfoods index content">
<?= $this->Form->create($mfood, ['type' => 'post']) ?>

    <div class="titlebox">
        <p1><?= $mode === 'edit' ? '単品食材商品編集' : '単品食材商品登録' ?></p1>
    </div>
    
<div class="flex-vertical" style="padding-top: 3% !important;gap:1rem; padding-bottom:10px;">
            <div class="input-range">
            <div class="label-stack">
            <span>コード番号</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('food_id', [
                'label' => false,
                'id' => 'food_id',
                'type' => 'text',
                'required' => true,
                'readonly' => $mode === 'edit',
                'value' => $mfood->food_id, // ←addは＋1済、editは既存値
            ]) ?>


            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>商品名</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('food_name', [
                    'label' => false,
                    'id' => 'var1',   // ← JSで扱いやすいようにidは英数字にするのがおすすめ
                    'name' => 'food_name',
                    'required' => true,
                    'data-max-units' => 200,
                ]) ?>
                <div id="counter"></div>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>食材分類</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('category_id', [
                'label' => false,
                'id' => 'category_id',
                'required' => true,
                'options' => $mFoodCategories, // ← これを追加
                'value' => $mfood->category_id,
                'empty' => '選択してください',
                'style' => 'max-width: 500px;',
            ]) ?>
            </div> 
            
            <div class="input-range">
            <div class="label-stack">
            <span>規格</span>
            </div>
            <?= $this->Form->control('food_specification', [
                'label' => false,
                'id' => 'food_specification',
                'name' => 'food_specification',
            ]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>表示順</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('disp_no', [
                    'label' => false,
                    'id' => 'disp_no',
                    'name' => 'disp_no',
                    'min' => 0,
                ]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack"><span>施設グループ</span></div>
            
            <div class="group-search-row">
                <input type="text"
                       id="user-group-name"
                       class="readonly-like"
                       value="<?= h($mUserGroup[$selectedGroupId] ?? '') ?>"
                       readonly
                       required>

                <?php if ($mode !== 'edit'): ?>
                    <button type="button" id="openGroupSearch">検索</button>
                <?php endif; ?>
            </div>
            <!-- グループ選択情報をサーバーへ送るための hidden -->
            <?= $this->Form->hidden('user_group_id', [
                'id'    => 'user-group-id',
                'value' => $selectedGroupId
            ]) ?>

            </div>
            <div class="select-btn-area">
                <button type="button" id="check-all" class="aobtn-like">全選択</button>
                <button type="button" id="uncheck-all" class="aobtn-like">全解除</button>
            </div>
            <div class="input-range">
            <div class="label-stack">
                <span>施設</span>
                <span style="font-size: 1.5rem;">（必須：複数選択可）</span>
            </div>

            <div class="intable-wrapper">
                <table class="intable" style="max-height: 20%;">
                    <thead>
                        <tr style="background-color: #CCE5FF;">
                            <th>選択</th>
                            <th>施設番号</th>
                            <th>施設名称</th>
                        </tr>
                    </thead>
                    
                    <tbody id="user-list">
                    <?php if ($mode === 'add'): ?>
                        <?php foreach ($mUser as $userId => $userName): ?>
                            <?php
                                $userGroupPrefix = substr((string)$userId, 0, 5);
                            ?>
                            <?php if (empty($selectedGroupId) || (string)$userGroupPrefix === (string)$selectedGroupId): ?>
                                <tr>
                                    <td>
                                        <?= $this->Form->control("selected_users[{$userId}]", [
                                            'type' => 'checkbox',
                                            'value' => '1',
                                            'checked' => in_array($userId, $selectedUserIds ?? []),
                                            'label' => false
                                        ]) ?>
                                    </td>
                                    <td><?= h($userId) ?></td>
                                    <td><?= h($userName) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                        <?php if ($mode === 'edit'): ?>
                            <?php foreach ($mUser as $userId => $userName): ?>
                                <?php $userGroupPrefix = substr((string)$userId, 0, 5); ?>
                                <?php if (empty($selectedGroupId) || (string)$userGroupPrefix === (string)$selectedGroupId): ?>
                                    <tr>
                                        <td>
                                            <?= $this->Form->control("selected_users[{$userId}]", [
                                                'type' => 'checkbox',
                                                'value' => '1',
                                                'checked' => in_array(
                                                    (string)$userId,
                                                    array_map('strval', $selectedUserIds ?? []),
                                                    true
                                                ),
                                                'label' => false
                                            ]) ?>
                                        </td>
                                        <td><?= h($userId) ?></td>
                                        <td><?= h($userName) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </tbody>
                    
                </table>
                
            </div>
        </div>

        <?php if ($mode === 'edit'): ?>
                <div class="input-range">
                    <div class="label-stack">
                        <span style="padding-bottom: 15px;">削除</span>
                    </div>
                    <div class="del-flg-checkbox">
                        <?= $this->Form->control('del_flg', [
                            'type' => 'checkbox',
                            'label' => '削除状態にする',
                            'hiddenField' => true,
                            'value' => '1',
                            'checked' => $mfood->del_flg === '1',
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>

</div>

<div class="mfoodsBox">
     <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['id' => '', 'class' => 'akabtn-like']) ?>
     <a id="" href="<?= $this->Url->build(['action' => 'index']) ?>"
      class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
     
</div>

<?= $this->Form->end() ?>
</div>

<style>
    .mfoodsBox{
        display: flex;
        gap: 20px;
        padding-right:5%;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
    }
    .intable-wrapper {
    max-height: 200px;
    height: auto;
    overflow-y: auto;
    display: block;
    border: 1px solid #333;
    margin-top: 1rem;
    width: 50%;
}
.select-btn-area {
    display: flex;
    gap: 8px;
    margin-bottom: 6px;
    justify-content: flex-start;
    margin-top: 1rem;
    margin-left: 185px;
}
.readonly-like {
    background-color: #f5f5f5;
    color: #888;
}
.group-search-row {
    display: flex;
    align-items: center;
    gap: 5px;
}

#openGroupSearch {
    margin-top: -3px; /* ← 微調整（0〜4pxで好み調整） */
}
</style>

<script>
/* ================================
 * グローバル変数
 * ================================ */
var MFoods = {
    foodId: <?= json_encode($mfood->food_id ?? null) ?>,
    mode: <?= json_encode($mode ?? 'add') ?>,
    ajaxUrl: ''
};

/* ================================
 * 初期化
 * ================================ */
$(function () {

    // 呼び出し先を add/edit で切り替え
    MFoods.ajaxUrl = (MFoods.mode === 'edit')
        ? '<?= $this->Url->build(["controller" => "Mfoods", "action" => "ajaxUsersByGroup"]) ?>'
        : '<?= $this->Url->build(["controller" => "Mfoods", "action" => "ajaxUsersByGroupAdd"]) ?>';

    // edit は初期表示で取得
    if (MFoods.mode === 'edit') {
        fetchUserList($('#user-group-id').val());
    }

    // 施設グループ検索
    $('#openGroupSearch').on('click', function () {
        window.open(
            '<?= $this->Url->build(["controller" => "MUserGroup", "action" => "search"]) ?>',
            'groupSearch',
            'width=900,height=600'
        );
    });

    // 全選択
    $('#check-all').on('click', function () {
        $('#user-list input[type="checkbox"]').prop('checked', true);
    });

    // 全解除
    $('#uncheck-all').on('click', function () {
        $('#user-list input[type="checkbox"]').prop('checked', false);
    });
});

/* ================================
 * 選択済み施設取得（add用）
 * ================================ */
function getSelectedUsers() {
    var selected = {};
    $('input[name^="selected_users"]:checked').each(function () {
        var match = $(this).attr('name').match(/\[(\d+)\]/);
        if (match) {
            selected[match[1]] = '1';
        }
    });
    return selected;
}

/* ================================
 * 施設一覧取得（★グローバル）
 * ================================ */
function fetchUserList(groupId) {
    if (!groupId) return;

    $.ajax({
        url: MFoods.ajaxUrl,
        method: 'POST',
        data: (MFoods.mode === 'edit')
            ? { user_group_id: groupId, food_id: MFoods.foodId }
            : { user_group_id: groupId, selected_users: getSelectedUsers() },
        headers: { 'X-CSRF-Token': $('meta[name="csrfToken"]').attr('content') },
        success: function (html) {
            $('#user-list').html(html);
        }
    });
}

/* ================================
 * search.php から呼ばれる
 * ================================ */
function setUserGroup(groupId, groupName) {

    // hidden
    $('#user-group-id').val(groupId);

    // 表示名
    $('#user-group-name').val(groupName);

    // 施設一覧更新
    fetchUserList(groupId);
}
</script>





