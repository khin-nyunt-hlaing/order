<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TAnnounce $TAnnounce
 */
?>
<?php
use Cake\Utility\Text;
?>
<div class="TAnnounce index content" style="padding:10px;
    height: 900px;">
<?= $this->Form->create($TAnnounce, ['type' => 'file']) ?>

<div class="titlebox" style="height:30px;">
    <p1><?= $mode === 'edit' ? 'お知らせ編集' : 'お知らせ登録' ?></p1>
</div>

<div class="TAbox">
    <div class='TAminibox'>
    <p style='width:12rem; margin-bottom:0;'>お知らせ区分</p>
    <?= $this->Form->control('announce_div', [
    'type' => 'select',
    'label' => false,
    'options' => $announceDivList,
    'empty' => '選択してください'
    ]) ?>
    </div>
    <?php if (!empty($TAnnounce->announce_id)): ?>
    <?= $this->Form->hidden('announce_id', [
        'value' => $TAnnounce->announce_id,
        'id' => 'announceId' // ← JSで参照するためのID
    ]) ?>
    <?php endif; ?>

      <!-- お知らせ開始日・終了日 -->
    <div class="TAminibox">
        <p style="width: 12rem; margin-bottom: 0;">掲載期間</p>
            <div class="date-range" style="display: flex; align-items: center; gap: 1rem;">
                <?= $this->Form->control('announce_start_date', [
                    'type' => 'date',
                    'label' => false,
                    'class' => 'start-date'
                ]) ?>

                <span style="font-weight: bold;">～</span>

                <?= $this->Form->control('announce_end_date', [
                    'type' => 'date',
                    'label' => false,
                    'class' => 'end-date'
                ]) ?>
                </div>

            <p class="ezbox">状態</p>
            <?= $this->Form->control('visibility', [
                'type' => 'select',
                'label' => false,
                'class' => 'ezbox',
                'options' => [1 => '公開', 2 => '非公開'],
                'empty' => false
            ]) ?>
    </div>

    <div class="TAminibox2" style='padding-bottom:10px;'>
        <div class="TAminibox2_1">
            <p style='width:12rem; margin-bottom:0;'>配信先</p>

            <?php if ($mode === 'add'): ?>
            <div style="max-width:200px;">
            <label for="groupList" style='margin-bottom:0;'>施設グループ</label>
            <?= $this->Form->control('groupList', [
                'type' => 'select',
                'label' => false,
                'id' => 'groupList',
                'class' => 'ezbox',
                'empty' => '選択してください',
                'options' => $groupList
            ]) ?>
            </div>
            <div style="max-width:200px;">
            <label for="sService" style='margin-bottom:0;'>発注サービス</label>
            <?= $this->Form->control('ServiceSelect', [
                'type' => 'select',
                'label' => false,
                'id' => 'sService',
                'class' => 'ezbox',
                'empty' => '選択してください',
                'options' => $MServiceList]) ?>
            </div>
            <?php endif; ?>

            <?php if ($mode === 'edit'): ?>
                <div style="max-width:200px;">
                    <label for="groupList" style='margin-bottom:0;'>施設グループ</label>
                    <?= $this->Form->control('user_group_id', [
                        'type' => 'select',
                        'label' => false,
                        'id' => 'groupList',
                        'class' => 'ezbox',
                        'empty' => '選択してください',
                        'options' => $groupList,
                    ]) ?>
                </div>

                <div style="max-width:200px;">
                    <label for="sService" style='margin-bottom:0;'>発注サービス</label>
                    <?= $this->Form->control('ServiceSelect', [
                        'type' => 'select',
                        'label' => false,
                        'id' => 'sService',
                        'class' => 'ezbox',
                        'empty' => '選択してください',
                        'options' => $MServiceList,
                        ]) ?>
                </div>
            <?php endif; ?>

            <!-- ✅ ボタンは別ブロックに下寄せ -->
            <div style="margin-top: auto; padding-top: 1rem;">
            <button id="toggleAll" type="button" style="margin-top:auto;">全選択</button>
            </div>
        </div>

        <div class="TAminibox2_2" style="align-items: center;">
            <p style=" margin: 0; display: flex; align-items: center; height: 100%;  ">施設</p>
            <table class="announceTable">
                <thead><tr style="background-color: #CCE5FF">
                    <th>選択</th>
                    <th>施設番号</th>
                    <th class="namebox">施設名称</th>
                </tr></thead>
                <tbody id="RecipientView">
                    <!-- Ajaxで<tr>が差し込まれる -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="TAminibox" style="height:5rem;">
        <p>タイトル</p>
        <?= $this->Form->control('announce_title', [
                'type' => 'textarea',
                'label' => false,
                'class' => 'textbox',
                'style' => 'min-height: 4rem  !important; width: 500px;'
            ]) ?>
    </div>

    <div class="TAminibox3" style="margin-bottom:10px;">
        <p>お知らせ</p>
        <?= $this->Form->control('announce_text', [
                'type' => 'textarea',
                'label' => false,
                'class' => 'textbox',
                'maxlength' => 2000
            ]) ?>
    </div>

    <div class="TAminibox" style='align-items: flex-start; height:100px; gap:0 !important; margin-bottom:10px;'>
        <p style=' margin-right:3rem;'>添付ファイル</p>
             <!-- 左カラム（1〜3） -->
                <div style="display: flex; flex-direction: column; gap: 0.5rem; padding-right:10px; align-items: flex-start;">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div style="    height: 30px;
                                        display: flex;
                                        justify-content: center;
                                        gap:1rem;">
                            <label for="attachment<?= $i ?>" class="custom-file-label" style="
                                height: 30px; line-height: 3rem; padding: 0 1rem; display: inline-block;
                                vertical-align: middle; text-align: center; gap:1rem;">参照</label>

                            <input type="file" name="attachment<?= $i ?>" id="attachment<?= $i ?>" class="custom-file-input">

                            <span id="file-name-label-<?= $i ?>">
                                <?php if (!empty($fileLinks[$i])): ?>
                                    <?= $this->Html->link(
                                        h(Text::truncate($fileLinks[$i], 10)), // 表示は20文字制限
                                            ['controller' => 'TAnnounce', 'action' => 'viewFile', $fileLinks[$i]],
                                        ['target' => '_blank']
                                    ) ?>
                                <?php else: ?>
                                    選択されていません
                                <?php endif; ?>
                            </span>
                             <!-- 削除ボタン追加 -->
            <?php if ($this->request->getParam('action') === 'edit'): ?>
                <button type="button"
                    id="resetAttachmentBtn<?= $i ?>"
                    class="btn btn-danger btn-sm"
                    data-index="<?= $i ?>"
                    data-name="<?= !empty($fileLinks[$i]) ? basename($fileLinks[$i]) : 'ファイル' ?>"
                    data-id="<?= $announce->id ?? '' ?>"
                    style="height: 30px; line-height: 3rem; padding: 0 1rem; display: inline-block;
                                vertical-align: middle; text-align: center; gap:1rem;
                                font-size:1.6rem; font-weight:700;">
                    削除
                </button>
                <script>
                    $('#resetAttachmentBtn<?= $i ?>').click(function() {
                    if (!confirm('添付ファイルを削除します。よろしいですか？')) return;

                    const announceId = $('#announceId').val();

                    $.ajax({
                        url: '<?= $this->Url->build(["controller" => "TAnnounce", "action" => "ajaxResetAttachment", $i]) ?>',
                        method: 'POST',
                        dataType: 'json',
                        data: {announceId: announceId},
                        headers: {
                            'X-CSRF-Token': $('meta[name="csrfToken"]').attr('content')
                        },
                        success: function(res) {
                            alert(res.message);
                            $('#file-name-label-<?= $i ?>').text('選択されていません');
                        },
                        error: function(xhr) {
                            alert('通信エラーです: ' + xhr.status);
                        }
                    });
                    });
                </script>
            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- 右カラム（4〜5） -->
                <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-start;">
                    <?php for ($i = 4; $i <= 5; $i++): ?>
                        <div style="    height: 30px;
                                        display: flex;
                                        justify-content: center;
                                        gap:1rem;">
                            <label for="attachment<?= $i ?>" class="custom-file-label" style="
                                height: 3rem; line-height: 3rem; padding: 0 1rem; display: inline-block;
                                vertical-align: middle; text-align: center;">参照</label>

                            <input type="file" name="attachment<?= $i ?>" id="attachment<?= $i ?>" class="custom-file-input">

                            <span id="file-name-label-<?= $i ?>">
                                <?php if (!empty($fileLinks[$i])): ?>
                                    <?= $this->Html->link(h($fileLinks[$i]), '/uploads/' . h($fileLinks[$i]), ['target' => '_blank']) ?>
                                <?php else: ?>
                                    選択されていません
                                <?php endif; ?>
                            </span>
                            <!-- 削除ボタン追加 -->
                <?php if ($this->request->getParam('action') === 'edit'): ?>
                    <button type="button"
                        id="resetAttachmentBtn<?= $i ?>"
                        class="btn btn-danger btn-sm"
                        data-index="<?= $i ?>"
                        data-name="<?= !empty($fileLinks[$i]) ? basename($fileLinks[$i]) : 'ファイル' ?>"
                        data-id="<?= $announce->id ?? '' ?>"
                        style="height: 30px; line-height: 3rem; padding: 0 1rem; display: inline-block;
                                vertical-align: middle; text-align: center; gap:1rem;
                                font-size:1.6rem; font-weight:700;">
                        削除
                    </button>
                    <script>
                        $('#resetAttachmentBtn<?= $i ?>').click(function() {
                        if (!confirm('添付ファイルを削除します。よろしいですか？')) return;

                        const announceId = $('#announceId').val();

                        $.ajax({
                            url: '<?= $this->Url->build(["controller" => "TAnnounce", "action" => "ajaxResetAttachment", $i]) ?>',
                            method: 'POST',
                            dataType: 'json',
                            data: {announceId: announceId},
                            headers: {
                                'X-CSRF-Token': $('meta[name="csrfToken"]').attr('content')
                            },
                            success: function(res) {
                                alert(res.message);
                                $('#file-name-label-<?= $i ?>').text('選択されていません');
                            },
                            error: function(xhr) {
                                alert('通信エラーです: ' + xhr.status);
                            }
                        });
                        });
                    </script>
                <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>

        <?php $errors = $TAnnounce->getError('attachment'); ?>
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?= h(is_array($errors) ? ($errors[0] ?? reset($errors)) : $errors) ?>
                </div>
            <?php endif; ?>
        </div>
        </div>
        <div class="TAnnounceEd5">
            <?= $this->Form->button($mode === 'edit' ? '編集' : '登録', ['id' => 'ANNOUNCEreg', 'class' => 'akabtn-like']) ?>
            <a href="<?= $this->Url->build(['action' => 'index']) ?>"
             class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
        
        </div>
    </div>
<style>
    .TAbox {
        padding-left: 20px;
        padding-top: 10px;
    }
    .TAminibox{
        display:flex;
        align-items: center; /* 縦方向の中央揃え */
        height: 50px; /* 必要に応じて高さを設定 */
        gap:60px;
    }
    .ezbox{
        margin:0px;
    }
    .TAminibox2_1{
        display:flex;
        margin-top:10px;
        gap:60px;
    }
    .TAminibox2_2{
        display:flex;
        margin-top:10px;
        margin-left:18rem;
        gap:10px;
    }
    .announceTable {
    width: 50%;
    border-collapse: collapse; /* セルのボーダーをまとめる */
    border: none; /* テーブル自体の枠線を消す */
    margin-left:20px;
    border:1px solid #333;
    font-size:1.2em;

    max-height:200px;
    overflow-y: auto;
    display: block; /* ブラウザによっては必要 */
    margin-bottom:0;
    }   
    .announceTable th,
    .announceTable td {
        height: 50px;           /* 高さを統一 */
        vertical-align: middle; /* 縦方向中央寄せ */
        padding: 0 10px;        /* 横の余白は適宜調整 */
        border:1px solid #333;
    }
    .namebox{
        width: 300px;
    }
    .TAminibox3{
        margin-top:10px;
        display:flex;
        gap:30px;
    }
    .TAminibox3 textarea{
        margin-left:30px;
        width: 880px;
        height:150px;
    }
    .TAnnounceEd5{
        display: flex;
        gap: 20px;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
        padding-right:20px;
    }
    .error-message {
    color: red;
    font-weight: bold;
    }
</style>
<style>
    .custom-file-input {
        display: none;
    }
    .custom-file-label {
        display: inline-block;
        padding: 0.5rem 1rem;
        background-color: #007bff;
        color: white;
        cursor: pointer;
        border-radius: 4px;
    }
</style>

<script>
    $(function () {
    const $svc  = $('#sService');       // セレクト1
    const $grp  = $('#groupList');      // セレクト2
    const $view = $('#RecipientView');  // tbody
    const announceId = $('#announceId').val(); 

    function runAjax() {
        $.ajax({
        url: '<?= $this->Url->build(["controller"=>"TAnnounce","action"=>"ajaxDeliveryTargets"]) ?>',
        method: 'POST',
        data: {
            serviceCode: $svc.val() ?? '',
            groupCode:   $grp.val() ?? '',
            announceId:  announceId
        },
        headers: { 'X-CSRF-Token': $('meta[name="csrfToken"]').attr('content') },
        success: function (html) {
            $view.html(html);
        },
        error: function (xhr) {
            console.error('Ajax失敗', xhr.status);
        }
        });
    }

    // ★ 初期表示：add でも edit でも一回だけ呼ぶ
    // edit なら 'value' で選択済みの値が入っている → 絞り込み、
    // add なら空 → 母数一覧が返る
    runAjax();

    // ★ セレクト変更時：毎回そのまま呼ぶ（未選択に戻したら母数に戻る）
    $svc.add($grp).on('change', runAjax);
    });
    </script>
    <!-- ajaxセット↑ -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
        // 戻るボタン確認
        const backBtn = document.getElementById('ANNOUNCEret');
        if (backBtn) {
            backBtn.addEventListener('click', function (e) {
                const confirmed = confirm('遷移すると入力内容が破棄されます。よろしいですか？');
                if (!confirmed) {
                    e.preventDefault();
                    backBtn.blur();
                }
            });
        }

        // チェックボックス色切替

        // 全選択・全解除ボタン
        const toggleBtn = document.getElementById('toggleAll');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (event) {
                event.preventDefault();
                const checkboxes = document.querySelectorAll('.toggle-color');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                    cb.dispatchEvent(new Event('change', { bubbles: true })); // ← ✅ここ修正
                });
                toggleBtn.textContent = allChecked ? '全選択' : '全解除';
            });
        }

        // ファイル名表示（1つ目のみ）
        for (let i = 1; i <= 5; i++) {
            const input = document.getElementById(`attachment${i}`);
            const label = document.getElementById(`file-name-label-${i}`);
        if (!input || !label) continue;
            input.addEventListener('change', function () {
                const fileName = input.files[0]?.name ?? '選択されていません';
                label.textContent = fileName;
                });
            }
        });
</script>
<!-- エクセル PDF以外受け取らないjs -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const allowedExtensions = ['pdf', 'xlsx', 'xls'];

    for (let i = 1; i <= 5; i++) {
        const input = document.getElementById('attachment' + i);
        const label = document.getElementById('file-name-label-' + i);
        if (!input || !label) continue;

        input.addEventListener('change', function () {
            const file = input.files[0];
            if (!file) return;

            const fileName = file.name;
            const ext = fileName.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(ext)) {
                alert('PDF または Excel ファイル（.xls, .xlsx）のみ添付できます。');
                input.value = '';
                label.innerHTML = '選択されていません'; // ← 修正箇所
            } else {
                label.innerHTML = fileName; // ← 修正箇所
            }
            });
        }
    });
</script>
<!-- 日付データの大なり関係 -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // ✅ 日付の大なり関係チェック（開始日 > 終了日にならないように制限）
    document.querySelectorAll('.date-range').forEach(range => {
      const start = range.querySelector('.start-date');
      const end = range.querySelector('.end-date');

      if (start && end) {
        start.addEventListener('change', () => {
          if (start.value) {
            end.min = start.value;
          } else {
            end.removeAttribute('min');
          }
        });

        end.addEventListener('change', () => {
          if (end.value) {
            start.max = end.value;
          } else {
            start.removeAttribute('max');
          }
        });
      }
    });
  });
</script>


