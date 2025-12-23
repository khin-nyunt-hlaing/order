<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MUser $mUser
 */
?>
<div class="mUser  index content">
<div class="titlebox">
<p1>パスワード再設定</p1>
</div>
 <?= $this->Form->create(null, ['url' => ['action' => 'request'], 'type' => 'post', 'id' => 'requestForm']) ?>
    <div class="Leftbox">
        <div class="Leftinbox">
            <table id="requestT">
                <tr>
                    <td><label for="user_id">施設番号</label></td>
                    <td><?= $this->Form->text('user_id', [
                        'id' => 'user_id',
                        'placeholder' => '半角数字で入力してください',
                        'required' => true,
                        'maxlength' => 15,
                        'pattern' => '[A-Za-z0-9]*',
                        'oninput' => "this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                    ]) ?></td>
                </tr>
                <tr>
                    <td><label for="secret_question">秘密の質問</label></td>
                    <td><?= $this->Form->control('secret_question', [
                        'type' => 'select',
                        'label' => false,
                        'options' => $questionList,
                        'empty' => '選んでください',
                        'default' => '',
                        'id' => 'secret_question',
                        'maxlength' => 60
                    ]) ?></td>
                </tr>
                <tr>
                    <td><label for="answer">質問の答え</label></td>
                    <td><?= $this->Form->text('answer', [
                        'label' => false,
                        'placeholder' => '答えを入力してください',
                        'id' => 'answer',
                        'maxlength' => 60,
                        'required' => false
                    ]) ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="Rightbox">
        <div class="Rightinbox">
            <?= $this->Form->button('パスワード再設定', ['class' => 'btn-like','style'=>'line-height:0 !important']) ?>
            <?= $this->Html->link('戻る', [
                'controller' => 'MUser', 
                'action' => 'login'
            ], [
                'class' => 'btn-like',
                'onclick' => "return confirm('遷移すると入力内容が破棄されます。よろしいですか？');"
            ]) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionSelect = document.getElementById('secret_question');
    const answerInput = document.getElementById('answer');
    const form = document.getElementById('requestForm');

    function toggleAnswerRequired() {
        if (questionSelect.value) {
            answerInput.required = true;
        } else {
            answerInput.required = false;
        }
    }

    questionSelect.addEventListener('change', toggleAnswerRequired);

    form.addEventListener('submit', function(e) {
        if (questionSelect.value && !answerInput.value.trim()) {
            e.preventDefault();
            alert('秘密の質問を選択した場合は、答えを入力してください。');
            answerInput.focus();
        }
    });
});
</script>