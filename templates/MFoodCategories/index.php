<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\App\Model\Entity\MFoodCategory[] $mFoodCategories
 */
?>
<div class="mFoodCategories index content">
        <div class="title_box">
            <h2 class="title">単品食材分類マスタ</h2>
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
                            分類ID(完全一致)
                        </div>
                        <?= $this->Form->text('category_id', [
                            'value' => $this->request->getQuery('category_id')
                        ]) ?>
                    </div>           
                </div>

                <!-- 右列 -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">
                            分類名称(部分一致)
                        </div>
                        <?= $this->Form->text('category_name', [
                            'value' => $this->request->getQuery('category_name')
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


    <?= $this->Form->create(null, ['type' => 'post']) ?>

    <div class="table-wrapper" style="height:400px; overflow-y:scroll; border:1.5px solid #ccc;">
        <table class="styled-table">
            <thead>
                <tr>
                    <th class="col-1">選択</th>
                    <th class="col-2">分類ID</th>
                    <th class="col-3">分類名称</th>
                    <th>削除</th>
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
                  
                    <td>
                        <?= $this->Html->link(
                            h($category->category_id),
                            ['action' => 'edit', $category->category_id],
                            ['class' => 'link-edit']
                        ) ?>
                    </td>
                    <td class="col-3"><?= h($category->category_name) ?></td>
                    <td style="text-align:center;"><?= $category->del_flg == 1 ? '✓' : '' ?></td>
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
        <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
        <!-- <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?> -->
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
/* 削除データON/OFF → GET検索に反映 */
document.getElementById('del_flg')?.addEventListener('change', function () {
    document.getElementById('search_del_flg').value = this.checked ? '1' : '';
    document.getElementById('searchForm').submit();
});
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