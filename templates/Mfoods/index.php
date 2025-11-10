<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Mfood> $mfoods
 */
?>
<div class="mfoods index content">

    
    <div class="title_box">
    <h2 class="title">食材商品一覧</h2>
        
<?= $this->Form->create(null, ['type' => 'post']) ?>
<div class="deleted-filter">
    <span class="filter-label">削除データ</span>
    <?= $this->Form->checkbox('del_flg', [
        'id' => 'del_flg',
        'value' => '1',
        'hiddenField' => true,
        'checked' => $showDeleted,
        'onchange' => 'this.form.submit();'
    ]) ?>
    <label for="del_flg" class="filter-text">削除データを含める</label>
</div>
    
    </div>

    <div class="search-box-wrapper-food">
    <div class="search-box-food">
     
    <div class="search-col">
      <div class="search-field"><?= $this->Form->control('food_id', [
        'label' => 'コード番号(完全一致)', 
        'type' => 'text',
        'value' => $foodId]) ?>
        </div> 
      <div class="search-field"><?= $this->Form->control('food_name', [
        'label' => '商品名(部分一致)',
        'type' => 'text',
        'value' => $foodName]) ?>
        </div> 
    </div>
    
    <div class="search-col">
      <div class="search-field"><?= $this->Form->control('user_group_id', [
          'class' => 'ugiinput',
          'label' => '施設グループ',
          'type' => 'select',
          'options' => $groupList ?? [],
          'empty' => 'すべて',
          'value' => $userGroupId ?? '', //これで抽出後も値を残す
      ]) ?></div> 
      
      <div class="search-field-food"><?= $this->Form->submit('抽出') ?></div> <!-- 抽出 -->
    </div>
    <?= $this->Form->end() ?>
    </div> 
   
    </div>
    
    
    <!--フォーム開始：ボタンとチェックボックス送信用 -->
    <?= $this->Form->create(null, ['type' => 'post']) ?>
    <p class="title2" style="text-align:right">件数 <?= h($count) ?> 件</p>


    <div class="Extractscrollbox">
        <table class="styled-table">
            <thead th>
                <tr>
                    <th class="col-1">選択</th> <!--チェックボックス列 -->
                    <th class="col-2">コード番号</th>
                    <th class="col-3">商品名</th>
                    <th class="col-4">規格</th>
                    <th class="col-5">分類名称</th>
                    <th class="col-6">表示順</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mfoods as $mfood): ?>
                <tr>
                    <!--チェックボックス（複数選択可能） -->
                    <td class="col-1">
                       <?= $this->Form->control("select[{$mfood->food_id}]", [
                       'type' => 'checkbox',
                      'label' => false,
                      'class' => 'toggle-color',
                        ]) ?>
                     </td>

                    <!--各データ列 -->
                    <td class="col-2"><?= h($mfood->food_id) ?></td>
                    <td class="col-3"><?= h($mfood->food_name) ?></td>
                    <td class="col-4"><?= h($mfood->food_specification) ?></td>
                    <td class="col-5"><?= h($mfood->m_food_category->category_name ?? $mfood->category_id) ?></td>
                    <td class="col-6"><?= h($mfood->disp_no) ?></td>
             </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    
    <!--操作ボタン -->
    <div class="leftbox">
    <?= $this->Form->button('追加', ['name' => 'action', 'value' => 'add']) ?>
    <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?>
    <?= $this->Form->button('削除', [
    'name' => 'action',
    'value' => 'delete',
    'onclick' => 'return checkBeforeDelete();'
    ]) ?>
</div>

<!--戻るリンク -->
<div class="rightbox">
    <?= $this->Html->link('戻る', ['controller' => 'Mmenus','action' => 'index'],
     ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
</div>
<!--フォーム終了 -->
<?= $this->Form->end() ?>
</div>

<style>
    .leftbox{
        margin-right: auto; /* これで右寄せになる */
        padding:5px;
    }
    .rightbox{
        margin-left: auto; /* これで右寄せになる */
        padding:5px;
    }
    .highlight-row {
        background-color: #D0EBFF; /* 濃いめの青背景に変更 */
    }

    .search-box-wrapper-food {
    display: flex;
    justify-content: center;
    
    box-sizing: border-box;
    padding:  1rem; /* 少し余白確保（任意） */
    }

    .search-box-food {
    display: flex;
    gap: 4rem;
    align-items: flex-end;
    flex-wrap: nowrap;
    width: 100%;
    justify-content: center; /* ← ここが効く */

    padding: 2rem;
    margin-bottom: 1.5rem;
    box-sizing: border-box;
    border: 1.5px solid #ccc;
    border-radius: 0.4rem;
    background: #fff;
    }

    .search-field-food {
    display: flex;
    flex-direction: column;
    align-items: center;  /* ← これで中の label/input も中央揃え */
    text-align: center;
    min-width: 200px;
    margin-top: 10px;
    }
    .ugiinput{
        width: 80%; !important
    }
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
    .col-5, .col-6{
        max-width: 80px;          /* ①狭め固定 */
        text-align: center;         /* センター寄せ */
        white-space: normal;       /* 通常改行を許可 */
        word-break: break-word;    /* 単語途中でも折返し */
    }
</style>

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

<script>
$(document).ready(function() {
    $('input.toggle-color[type="checkbox"]').on('change', function() {
        let row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            row.addClass('highlight-row');
        } else {
            row.removeClass('highlight-row');
        }
    });
});
</script>

