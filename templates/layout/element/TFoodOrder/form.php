<div class="TFoodOrder index content">
    <?php if ($mode === 'add'): ?>
    <?= $this->element('delivery_deadline_notice') ?>
<?php endif; ?>

<?= $this->Form->create($TFoodOrder, ['type' => 'file']) ?>

    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '食材発注 編集' : '食材発注 登録' ?></p1>
            <?= $this->Flash->render() ?>   
    </div>
    
<div class="flex-vertical">
            <div class="input-range">
            <div class="label-stack">
            <span>施設名</span>
            </div>
            <?= $this->Form->control('user_name', [
                    'value' => $userName,
                    'label' => false,
                    'id' => 'user_name',
                    'type' => 'text',
                    'required' => true,
                    'readonly' => true,
                ]) ?>



            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>発注日</span>
            </div>
            <?= $this->Form->control('order_date', [
                    'label' => false,
                    'id' => 'order_date',
                    'readonly' => true,
                ]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
                <span>納品希望日</span>
            </div>
            <?= $this->Form->control('deli_req_date', [
                'type' => 'date',
                'label' => false,
                'id' => 'deli_req_date',
                'min' => $minDate,
                'value' => $minDate,
                'readonly' => ($mode === 'edit'),
                'class' => ($mode === 'edit') ? 'readonly-gray' : ''
            ]) ?>

            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>食材分類</span>
            </div>
            <?= $this->Form->select('category_id', $categoryOptions, [
                'empty' => '選択してください',
                'id' => 'category-select',
                'value' => $categoryId,
                'data-addmode' => ($mode === 'add') ? '1' : null // ← これでadd時だけ属性が付く
            ]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>食材商品 / 規格</span>
            </div>
            <?= $this->Form->select('food_id', [], [
                'empty' => '分類を先に選択してください',
                'id' => 'food-select',
                'class' => 'no-shared-style'
            ]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>発注数</span>
            </div>
            <?= $this->Form->control('order_quantity', ['label' => false,'id' => 'order_quantity','type' => 'number', // 数値と仮定
                    'min' => 0]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>発注状態</span>
            </div>
            <?php
                $statusLabels = [
                    '0' => '未確定',
                    '1' => '確定',
                    '2' => 'キャンセル'
                ];

                $displayText = $statusLabels[$TFoodOrder->order_status ?? ''] ?? '';
                ?>

                <?= $this->Form->control('order_status_display', [
                    'label' => false,
                    'type' => 'text',
                    'value' => $displayText,
                    'readonly' => true
                ]) ?>
            </div>

        </div>
<div class="TFoodOrderBox">
     <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['id' => '', 'class' => 'akabtn-like','onclick' => 'return checkBeforeSave();']) ?>
     <a href="<?= $this->Url->build(['action' => 'index']) ?>" 
     class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
     
</div>

<?= $this->Form->end() ?>
</div>

<style>
    .TFoodOrderBox{
        display: flex;
        gap: 20px;
        padding-right:5%;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
    }
    #category-select,
    #food-select {
    width: auto !important;
    margin-bottom:0 !important
    }
    input[readonly],
textarea[readonly],
select[readonly] {
    background-color: #eee !important;
    color: #555 !important;
    cursor: not-allowed;
}
</style>
<?= $this->Html->script('ui_action') ?>
<script>
    const groupedFoods = <?= json_encode($groupedFoods, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script>
    window.groupedFoods = <?= json_encode($groupedFoods, JSON_UNESCAPED_UNICODE) ?>;
    window.initialFoodId = <?= json_encode($TFoodOrder->food_id) ?>;
</script>
<!-- 最短納品を手打変更して送信した場合止める -->
<script>
    window.minDeliReqDate = <?= json_encode($minDate) ?>; // 例: '2025-08-05'
</script>