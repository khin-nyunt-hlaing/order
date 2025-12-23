<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MUser $mUser
 * @var string $mode
 * @var \Cake\Collection\CollectionInterface|string[] $serviceList
 * @var \Cake\Collection\CollectionInterface|string[] $questionList
 * @var \Cake\Collection\CollectionInterface|string[] $patternList
 * @var \Cake\Collection\CollectionInterface|string[] $mUserGroups
 * @var int|string $selectedGroupId
 * @var array|string[] $statusList
 * @var int $minLeadTime
 * @var int $defaultLeadTime
 * @var array|string[] $viewedUsers
 * @var array|string[] $selectedDispUserIds
 */
?>

<?php
// POSTされた値を優先的にフォームに反映させる
$this->Form->setValueSources(['data', 'context']);
?>

<div class="mUsers index content"><!--common-uiのcssをわざと外してます-->
    <?= $this->Form->create($mUser, ['type' => 'post']) ?>

    <div class="titlebox">
        <h2><?= $mode === 'edit' ? '施設編集' : '施設登録' ?></h2>
    </div>

    <div class="flex-vertical" style="padding-top:1%; padding-left:3%; gap:1.5rem">

        <!-- ===============================
             施設グループ（検索＋readonly）
        ================================ -->
        <div class="input-range">
            <div class="label-stack">
                <span>施設グループ</span>
                <span style="font-size:1.5rem;">(必須)</span>
            </div>

            <div class="group-search-row">
                <input type="text"
                       id="user-group-name"
                       class="readonly-like"
                       value="<?= h($mUserGroups[$selectedGroupId] ?? '') ?>"
                       readonly
                       required>

                <?php if ($mode !== 'edit'): ?>
                    <button type="button" id="openGroupSearch">検索</button>
                <?php endif; ?>
            </div>

            <?= $this->Form->hidden('user_group_id', [
                'id'    => 'user-group-id',
                'value' => $selectedGroupId
            ]) ?>
        </div>


        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">発注サービス</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('use_service_id', [
                'type' => 'select',
                'id' => 'use-service-id',
                'options' => $serviceList,
                'label' => false,
                'required' => true,
                'empty' => '選択してください',
                'disabled' => $mode === 'edit' ? true : false
            ]) ?>
            <?php if ($mode === 'edit'): ?>
                <?= $this->Form->hidden('use_service_id', ['value' => $mUser->use_service_id]) ?>
            <?php endif; ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">施設番号</span>
            </div>
            <?php if ($mode === 'edit'): ?>
                <?= $this->Form->control('user_id', [
                    'type' => 'text',
                    'readonly' => true,
                    'label' => false,
                ]) ?>
            <?php else: ?>
                <?= $this->Form->control('user_id', [
                    'type' => 'hidden',
                    'value' => '',
                ]) ?>
            <?php endif; ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">施設名称</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('user_name', [
                'label' => false,
                'type' => 'text',
                'id' => 'user_name',
                'required' => true,
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">パスワード</span>
            </div>
            <?= $this->Form->create($mUser, ['id' => 'MUserForm']) ?>
                <?= $this->Form->control('password', [
                    'label' => false,
                    'type' => 'password',
                    'id' => 'password',
                    'readonly' => true,
                    'value' => '',
                    'autocomplete' => 'new-password',
                    'style' => 'width:200px;'
                ]) ?>
            <button type="button" onclick="generatePassword()" style="margin-bottom: 0;">
                <?= $mode === 'edit' ? '生成' : '生成' ?>
            </button>
            <span id="plainPassword" style="font-weight:bold;"></span>
        </div>

        <?php if (($mode ?? '') === 'edit' && !empty($mUser->user_id)): ?>
            <div class="input-range">
                <div class="label-stack">
                    <span style="font-size: 1.8rem;">秘密の質問</span>
                </div>
                <button type="button" id="resetSecretBtn">
                    質問と回答の削除
                </button>
            </div>

            <?= $this->Form->hidden('_csrfToken', [
                'value' => $this->request->getAttribute('csrfToken'),
                'id' => 'csrfToken'
            ]) ?>
        <?php endif; ?>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">配食商品パターン</span>
            </div>
            <?= $this->Form->control('use_pattern_id', [
                'type' => 'select',
                'id' => 'use-pattern-id',
                'options' => $patternList,
                'label' => false,
                'required' => ($serviceList === '2.4'),
                'disabled' => true,
                'empty' => '選択してください',
                'value' => $mode === 'edit'
                    ? ($mUser->use_pattern_id ?? null)
                    : null,
                'style' => 'max-width: 400px;'
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">リードタイム(日)</span>
            </div>
            <?= $this->Form->control('read_time', [
                'label' => false,
                'type' => 'number',
                'id' => 'read-time',
                'min' => $minLeadTime,
                'required' => false,
                'disabled' => true,
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>利用状態</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('status', [
                'label' => false,
                'type' => 'select',
                'options' => $statusList,
                'empty' => '選択してください',
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
                'required' => true,
            ]) ?>
        </div>

        <?php if ($mode === 'edit'): ?>
            <div class="input-range">
                <div class="label-stack">
                    <span style="padding-bottom: 16px;">削除</span>
                </div>
                <div class="del-flg-checkbox">
                    <?= $this->Form->control('del_flg', [
                        'type' => 'checkbox',
                        'label' => '削除状態にする',
                        'hiddenField' => true,
                        'value' => '1',
                        'checked' => $mUser->del_flg === '1',
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="input-range">
            <div class="label-stack">
                <span>閲覧施設</span>
            </div>
            <div class="intable-wrapper">
                <table class="intable">
                    <thead>
                        <tr style="background-color: #CCE5FF">
                            <th>選択</th>
                            <th>施設番号</th>
                            <th>施設名称</th>
                        </tr>
                    </thead>
                    <tbody id="disp-user">
                        <?php foreach ($viewedUsers as $id => $name): ?>
                            <tr>
                                <td>
                                    <?= $this->Form->control("disp_user_ids[{$id}]", [
                                        'type' => 'checkbox',
                                        'value' => '1',
                                        'label' => false,
                                        'class' => 'toggle-color',
                                        'checked' => in_array((string)$id, array_map('strval', $selectedDispUserIds ?? []), true),
                                        'hiddenField' => false
                                    ]) ?>
                                </td>
                                <td><?= h($id) ?></td>
                                <td><?= h($name) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="mUserBox">
        <?= $this->Form->button($mode === 'edit' ? '更新' : '追加', ['class' => 'akabtn-like']) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>"
           class="aobtn-like"
           onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
    </div>

    <?= $this->Form->end() ?>
</div>

<style>
.mUserBox {
    display: flex;
    gap: 20px;
    padding-right: 5%;
    justify-content: flex-end;
    margin-left: auto;
}

.intable-wrapper {
    max-height: 300px;
    height: auto;
    overflow-y: auto;
    display: block;
    border: 1px solid #333;
    margin-top: 1rem;
    width: 50%;
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

function generatePassword() {
    const length = 10;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let password = "";
    for (let i = 0; i < length; ++i) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    const hiddenField = document.getElementById("password");
    if (hiddenField) hiddenField.value = password;//hiddenに変更byなかむら
    document.getElementById("plainPassword").textContent = "生成パスワード: " + password;
}

//秘密の質問と回答リセット用
$('#resetSecretBtn').click(function() {
    if (!confirm('秘密の質問および回答をリセットします。よろしいですか？')) return;

    $.ajax({
        url: '<?= $this->Url->build(["controller" => "MUser", "action" => "ajaxResetSecretQuestions", $mUser->user_id]) ?>',
        method: 'POST',
        data: { _csrfToken: $('#csrfToken').val() },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
        },
        error: function(xhr) {
            alert('通信エラーです: ' + xhr.status);
        }
    });
});



//各サービスごとの表示切替
document.addEventListener('DOMContentLoaded', function () {
    const serviceSelect = document.getElementById('use-service-id');
    const dispUser = document.getElementById('disp-user');
    const patternSelect = document.getElementById('use-pattern-id');
    const leadTimeInput = document.getElementById('read-time');

    function toggleFields() {
        const val = serviceSelect.value;

    // ===== 閲覧施設：表示は常にする =====
    dispUser.style.display = 'table-row-group';

    // チェック可否制御
    const checkboxes = dispUser.querySelectorAll('input[type="checkbox"]');

    if (val === '5') {
        // 閲覧サービス → 設定可能
        checkboxes.forEach(cb => {
            cb.disabled = false;
        });
    } else {
        // それ以外 → 設定不可（チェックも外す）
        checkboxes.forEach(cb => {
            cb.checked = false;
            cb.disabled = true;
        });
    }

    // 配食商品パターンはID2,4のみ有効
        if (val === '2' || val === '4') {
            patternSelect.disabled = false;
            patternSelect.required = true;
        } else {
            patternSelect.disabled = true;
            patternSelect.required = false;
            patternSelect.value = ''; // クリア
        }

    // リードタイムはID3,4のみ有効
        if (val === '3' || val === '4') {
            leadTimeInput.disabled = false;
            leadTimeInput.required = true;
            if (!leadTimeInput.value) {
                leadTimeInput.value = <?= json_encode($defaultLeadTime) ?>;
            }
        } else {
            leadTimeInput.disabled = true;
            leadTimeInput.required = false;
            leadTimeInput.value = '';
        }
    }

      // invalid時の文言を①②に合わせる
  patternSelect.addEventListener('invalid', function (e) {
    if (patternSelect.required && !patternSelect.value) {
      e.target.setCustomValidity('このフィールドを選択してください。'); // ①②と同じ文言
    } else {
      e.target.setCustomValidity('');
    }
  });

    // 初期化時
    toggleFields();
    
    // addモードのみイベント登録
    if (!serviceSelect.disabled) {
        serviceSelect.addEventListener('change', toggleFields);
    }
});

document.getElementById('openGroupSearch').addEventListener('click', function () {
    window.open(
        '<?= $this->Url->build(["controller" => "MUserGroup", "action" => "search"]) ?>',
        'groupSearch',
        'width=900,height=600,scrollbars=yes'
    );
});

// 検索画面から呼ばれる
function setUserGroup(id, name) {
    document.getElementById('user-group-id').value = id;
    document.getElementById('user-group-name').value = name;
}

</script>
