<div class="mfoods index content">
<?= $this->Form->create($mfood, ['type' => 'post']) ?>

    <div class="titlebox">
        <p1><?= $mode === 'edit' ? '食材商品編集' : '食材商品登録' ?></p1>
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
            <div class="label-stack">
            <span>施設グループ</span>
            </div>
            <?= $this->Form->control('user_group_id', [
                'label' => false,
                'type' => 'select',
                'id' => 'user_group_id',
                //'required' => true,
                'options' => $mUserGroup,
                'empty' => '選択してください',
                'value' => $selectedGroupId,
                'style' => 'max-width: 500px;',
                
            ]) ?>
            <!-- グループ選択情報をサーバーへ送るための hidden -->
            <?= $this->Form->hidden('selected_group_id', ['id' => 'selected-group-id']) ?>

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
                                <?php $userGroupPrefix = substr((string)$userId, 0, 5);?>
                                <?php if (empty($selectedGroupId) || (string)$userGroupPrefix === (string)$selectedGroupId): ?>
                                    <tr>
                                        <td>
                                            <?= $this->Form->control("selected_users[]", [
                                                'type' => 'checkbox',
                                                'value' => '1',
                                                'checked' => in_array((string)$id,
                                        array_map('strval', $selectedFoodUserIds ?? []), true),
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
</style>

<script>
    $(function () {
        var groupId = $('#user_group_id').val();
        var foodId = <?= json_encode($mfood->food_id ?? null) ?>;
        var mode = <?=json_encode($mode ?? 'add') ?>;

    //呼び出し先を add/edit で切り替え
        var ajaxUrl = '';
        if (mode === 'edit') {
            ajaxUrl = '<?= $this->Url->build(["controller" => "Mfoods", "action" => "ajaxUsersByGroup"]) ?>';
        } else {
            ajaxUrl = '<?= $this->Url->build(["controller" => "Mfoods", "action" => "ajaxUsersByGroupAdd"]) ?>';
        }

        // 選択されているチェックボックスを収集（add用）
        function getSelectedUsers() {
            var selected = {};
            $('input[name^="selected_users"]:checked').each(function () {
                var name = $(this).attr('name'); // e.g., selected_users[40000001]
                var userId = name.match(/\[(\d+)\]/)?.[1];
                if (userId) {
                    selected[userId] = '1';
                }
            });
            return selected;
        }

        // 共通関数：グループIDを指定して施設リストを取得
        function fetchUserList(groupId) {
            // if (!groupId) {
            //     $('#user-list').html('');
            //     return;
            // }

            var requestData = (mode === 'edit') ? {
                user_group_id: groupId,
                food_id: foodId
            } : {
                user_group_id: groupId,
                selected_users: getSelectedUsers()
            };

            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: requestData,
                headers: {
                    'X-CSRF-Token': $('meta[name="csrfToken"]').attr('content')
                },
                success: function (response) {
                    $('#user-list').html(response);
                    $('#selected-group-id').val(groupId); // hidden項目に保持
                },
                error: function () {
                    alert('施設リストの取得に失敗しました。');
                }
            });
        }

        // 初期表示時:セレクトボックスに設定されている値で呼び出し
        // if (groupId) {
            fetchUserList(groupId);
        // }

        // セレクトボックス変更時に再取得
        $('#user_group_id').on('change', function () {
            var groupId = $(this).val();
            fetchUserList(groupId);
        });
    });

</script>
<script>
  const input = document.getElementById('disp_no');

  input.addEventListener('input', () => {
    // 数値を文字列として扱う
    let val = input.value;

    // マイナスや小数点は除外する場合はここで処理
    val = val.replace(/[^0-9]/g, '');

    // 18桁を超えたら切り捨て
    if (val.length > 18) {
      val = val.slice(0, 18);
    }

    input.value = val;
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const input   = document.getElementById('var1');
    const counter = document.getElementById('counter');
    const maxUnits = Number(input.dataset.maxUnits) || 20;

    // 半角=1, 全角=2 としてカウント
    // 半角は ASCII と 半角カナ(FF61-FF9F) を1、それ以外は2
    const unitOf = ch =>
      (/^[\x00-\x7F]$/.test(ch) || (/^[\uFF61-\uFF9F]$/.test(ch))) ? 1 : 2;

    let composing = false; // IME変換中フラグ

    function clampValue(str) {
      let used = 0;
      let out = '';
      for (const ch of str) {               // code point単位で走査
        const u = unitOf(ch);
        if (used + u > maxUnits) break;
        out += ch;
        used += u;
      }
      return { out, used };
    }

    function update() {
      const { out, used } = clampValue(input.value);
      if (out !== input.value) input.value = out;
        // ここはユーザーに残りを見せるだけの表示なので不要ならコメントアウト可
        // if (counter) counter.textContent = `合算: ${used}/${maxUnits}（半角=1, 全角=2）`;
    }

    input.addEventListener('compositionstart', () => { composing = true; });
    input.addEventListener('compositionend',   () => { composing = false; update(); });

    input.addEventListener('input', () => {
      if (composing) return; // IME中は確定後に制御
      update();
    });

    // 初期表示時
    update();
  });
</script>

