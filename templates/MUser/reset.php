<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MUser $mUser
 */
?>
<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="mUser index content">
    <div class="titlebox">
        <p1>パスワード再設定</p1>
    </div>
    <?= $this->Form->create(null, ['type' => 'post']) ?>
    <!-- 施設番号は自動でhiddenにセット -->
    <?= $this->Form->hidden('user_id', ['value' => $user->user_id ?? '']) ?>

    <div class="passLeft">
        <div class="Leftinbox">
            <div class="Leftminibox" style="display:flex;">
                <p style="width:250px;">施設番号</p>
                <div>
                    <!-- 表示用、編集不可 -->
                    <?= $this->Form->text('display_user_id', [
                        'value' => $user->user_id,
                        'readonly' => true,
                        'style' => 'width:30rem;',
                    ]) ?>
                </div>
            </div>
            <div class="Leftminibox" style="display:flex;">
                <p style="width:250px; padding-top: 5px;">新しいパスワード</p>
                <div>
                    <div class="password-wrap">
                        <?= $this->Form->control('loginpass', [
                            'placeholder' => 'パスワードを入力してください',
                            'type' => 'password',
                            'id' => 'logpass',
                            'maxlength' => 100,
                            'required' => true,
                            'label' => false,
                            'style' => 'width:30rem;',
                            'autocomplete' => 'new-password',
                            'value' => ''
                        ]) ?>
                        <span class="toggle-password" data-target="logpass">👁</span>
                    </div>
                    <p>（必須）</p>
                </div>
            </div>

            <div class="Leftminibox" style="display:flex;">
                <p style="width:250px; padding-top: 5px;">新しいパスワード（確認用）</p>
                <div>
                    <div class="password-wrap">
                        <?= $this->Form->control('confirmloginpass', [
                            'placeholder' => 'パスワードを入力してください',
                            'type' => 'password',
                            'id' => 'conlogpass',
                            'required' => true,
                            'label' => false,
                            'style' => 'width:30rem;',
                        ]) ?>
                        <span class="toggle-password" data-target="conlogpass">👁</span>
                    </div>
                    <p>（必須）</p>
                </div>
            </div>
        </div>
        <p>※アルファベットの大文字・小文字を必ず１文字以上使用し、<br>
        かつ数字か記号を組み合わせて10文字以上で入力してください。</p>
        <p>※全角英数字、半角記号「\, &, <, >, ", ', (半角スペース)」は使用できません。</p>
        <p>※施設番号と同じものは使用できません。</p>
        <div class="Leftinbox">
            <table id="logTreset">
               <tr>
                <td class="s_question">秘密の質問</td>
                <td>
                    <div class="question-row">
                        <?= $this->Form->control('secret_question', [
                            'label' => false,
                            'options' => $questionList,
                            'empty' => '設定推奨',
                            'id' => 'secret_question',
                            'style' => 'flex:1; min-width: 200px;'
                        ]) ?>
                        <button type="button" id="clearQuestionBtn" class="btn-clear-question">
                            質問と回答の削除
                        </button>
                    </div>
                    <p>（任意）</p>
                </td>
            </tr>
            <tr>
                <td class="question_answer">質問の答え</td>
                <td>
                    <?= $this->Form->text('passanswer', [
                        'placeholder' => '答えを入力してください',
                        'id' => 'passanswer',
                        'style' => 'width: 100%;',
                        'maxlength' => 1000,
                    ]) ?>
                </td>
            </tr>

            </table>
            <p>※秘密の質問が選択されている場合、質問の答えは必須です。</p>
        </div>
    </div>
    <div class="Rightbox">
        <div class="Rightinbox">
            <?= $this->Form->button('登録', ['class' => 'loginpost','style' => 'font-size:14px;']) ?>
            <?= $this->Html->link('戻る', ['action' => 'login'], ['class' => 'btn-like']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-password').forEach(function (icon) {
        icon.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁';
            }
        });
    });
});
</script>

<style>
.s_question{
    padding-bottom: 60px;
}
.question_answer{
    padding-bottom: 30px;
}
.question-row {
  display: flex;
  align-items: flex-start;
  gap: 12px; /* セレクトボックスとボタンの間のスペース */
}

.btn-clear-question {
  background-color: #d33c43;
  border: none;
  color: white;
  padding: 8px 16px;
  font-size: 1.4rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  white-space: nowrap; /* ボタンの文字折返し防止 */
}

.btn-clear-question:hover {
  background-color: #606c76;
}
.password-wrap {
    display: flex;
    align-items: center;      /* ★ 縦中央揃え */
    position: relative;
}

.password-wrap .input {
    flex: 1;
}

.toggle-password {
    margin-left: -36px;       /* ★ input 内に重ねる */
    cursor: pointer;
    font-size: 2.0rem;
    user-select: none;
    line-height: 1;
}
</style>
