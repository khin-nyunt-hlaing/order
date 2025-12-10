<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MUser> $mUser
 */
?>


<div class="mUser index content">
    <div class="titlebox">
        <p1>ログイン認証</p1>
    </div>
    <?= $this->Form->create() ?>
        <div class="Leftbox">
                 <div class="Leftinbox">
                 <table id="logT">
                    <tr>
                        <td><label for="logname">施設番号</label></td>
                        <td>
                            <?= $this->Form->control('user_id', [
                                'type' => 'text', // ← これを指定することで、<input type="text"> に固定される
                                'label' => false,
                                'id' => 'logname',
                                'required' => true,
                                'placeholder' => '半角数字で入力してください'
                            ]) ?>

                        </td>
                    </tr>
                    <tr>
                        <td><label for="logpass">パスワード</label></td>
                        <td>
                            <?= $this->Form->control('password', ['label' => false, 'id' => 'logpass',
                             'required' => true, 'placeholder' => '半角英数字で入力してください']) ?>
                        </td>
                    </tr>
                </table>
            </div>
                <a href="<?= $this->Url->build(['controller' => 'MUser', 'action' => 'request']) ?>">パスワードをお忘れの方はこちら</a>
        </div>
        <div class="Rightbox">
            <div class="Rightinbox">
                <!-- <button type="button" onclick="clearForm()" class="loginclear">クリア</button> -->
                <!-- <input type="submit" value="ログイン" class="loginpost"> -->
                <!-- <a href="<?= $this->Url->build(['controller' => 'Mmenus', 'action' => 'index']) ?>" class="btn-like">ログイン</a> -->
                 <?= $this->Form->button('ログイン', ['class' => 'loginpost']) ?>

            </div>
        </div>
    <?= $this->Form->end() ?>
</div>

<script>

function clearForm() {
  document.getElementById("logname").value = "";
  document.getElementById("logpass").value = "";
}

</script>
