<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\App\Model\Entity\MFoodCategory[] $mFoodCategories
 */
?>
<div class="mFoodCategories index content">
        <div class="title_box">
            <h2 class="title">食材分類一覧</h2>
            <?= $this->element('show_deleted_filter') ?>
            <p class="title2">件数 <?= h($count) ?> 件</p>
        </div>


    <?= $this->Form->create(null, ['type' => 'post']) ?>

    <div class="table-wrapper" style="height:400px; overflow-y:scroll; border:1px solid #000;">
        <table class="styled-table">
            <thead>
                <tr>
                    <th class="col-1">選択</th>
                    <th class="col-2">分類ID</th>
                    <th class="col-3">分類名称</th>
                    <th class="col-4">表示順</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mFoodCategories as $category): ?>
                <tr>
                    <td class="col-1">
                        <?= $this->Form->checkbox("select.{$category->category_id}", [
                            'label' => false,
                        ]) ?></td>
                    <td class="col-2"><?= h($category->category_id) ?></td>
                    <td class="col-3"><?= h($category->category_name) ?></td>
                    <td class="col-4"><?= h($category->disp_no) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    <!-- 左側の操作ボタン -->
    <div>
        <?= $this->Form->button('追加', ['name' => 'action', 'value' => 'add']) ?>
        <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?>
        <?= $this->Form->button('削除', [
            'name' => 'action',
            'value' => 'delete',
            'onclick' => 'return checkBeforeDelete();'
        ]) ?>
    </div>
   
    <?= $this->Form->end() ?>

    <div style="margin-top: 20px;">
        <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
    </div>
</div>

<?= $this->Form->end() ?>

<script>
function checkBeforeDelete() {
    const checked = document.querySelectorAll('input[name^="select["]:checked');
    const count = checked.length;

    if (count === 0) {
        // alert("削除するお知らせを選択してください。");
        // return false;
        return true; // フォームは送信する
    }

    return confirm(`${count}件選択されています。\n本当に削除しますか？`);
}
</script>
<style>
    .col-1{
        max-width: 80px;          /* ①狭め固定 */
        text-align: center;    /* センター寄せ */
    }
    .col-2{
        max-width: 100px;          /* ①狭め固定 */
        word-break: break-word;    /* 単語途中でも折返し */
        white-space: normal;       /* 通常改行を許可 */
    }
    .col-3{
        max-width: 500px;          /* ①狭め固定 */
        white-space: normal;       /* 通常改行を許可 */
        word-break: break-word;    /* 単語途中でも折返し */
        text-align: center;    /* センター寄せ */
    }
    .col-4{
        max-width: 80px;          /* ①狭め固定 */
        white-space: normal;       /* 通常改行を許可 */
        word-break: break-word;    /* 単語途中でも折返し */
        text-align: center;    /* センター寄せ */
    }
</style>