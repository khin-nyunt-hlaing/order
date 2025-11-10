<div class="TFoodOrder index content">
    <?php if ($mode === 'finalize'): ?>
    <?= $this->element('delivery_deadline_notice') ?>
<?php endif; ?>

    <?= $this->Form->create($TFoodOrder, ['type' => 'file']) ?>

    <div class="titlebox">
        <p1><?= h($title) ?></p1>
        <?= $this->Flash->render() ?>
    </div>

    <div class="flex-vertical">

        <!-- 施設名（閲覧のみ） -->
        <div class="input-range">
            <div class="label-stack"><span>施設名</span></div>
            <?= $this->Form->control('user_name', [
                'value'    => $userName,
                'label'    => false,
                'id'       => 'user_name',
                'type'     => 'text',
                'required' => true,
                'readonly' => true,
            ]) ?>
        </div>

        <!-- 発注日（閲覧のみ） -->
        <div class="input-range">
            <div class="label-stack"><span>発注日</span></div>
            <?= $this->Form->control('order_date', [
                'label'    => false,
                'id'       => 'order_date',
                'readonly' => true,
            ]) ?>
        </div>

        <!-- 納品希望日（finalize では編集不可） -->
        <div class="input-range">
            <div class="label-stack"><span>納品希望日</span></div>
            <?= $this->Form->control('deli_req_date', [
                'type'     => 'date',
                'label'    => false,
                'id'       => 'deli_req_date',
                'value'    => $TFoodOrder->deli_req_date ?? null,
                'readonly' => true,
                'class'    => 'readonly-gray',
            ]) ?>
        </div>

        <!-- 食材分類 -->
        <div class="input-range">
            <div class="label-stack"><span>食材分類</span></div>
            <?= $this->Form->select('category_id', $categoryOptions, [
                'empty'        => '選択してください',
                'id'           => 'category-select',
                'value'        => $selectedCategoryId,
                'data-addmode' => null, // finalize では add 判定は不要
            ]) ?>
        </div>

        <!-- 食材商品 / 規格（分類に応じてJSで差し替え） -->
        <div class="input-range">
            <div class="label-stack"><span>食材商品 / 規格</span></div>
            <?= $this->Form->select('food_id', [], [
                'empty' => '分類を先に選択してください',
                'id'    => 'food-select',
                'class' => 'no-shared-style',
            ]) ?>
        </div>

        <!-- 発注数 -->
        <div class="input-range">
            <div class="label-stack"><span>発注数</span></div>
            <?= $this->Form->control('order_quantity', [
                'label' => false,
                'id'    => 'order_quantity',
                'type'  => 'number',
                'min'   => 0,
            ]) ?>
        </div>

        <!-- 発注状態（表示用のみ） -->
        <div class="input-range">
            <div class="label-stack"><span>発注状態</span></div>
            <?= $this->Form->control('order_status_display', [
                'label'    => false,
                'type'     => 'text',
                'value'    => $displayText,
                'readonly' => true,
            ]) ?>
        </div>

    </div><!-- /.flex-vertical -->

    <div class="TFoodOrderBox">
        <?= $this->Form->button('更新', [
            'class'   => 'akabtn-like',
            'onclick' => 'return checkBeforeSave();',
        ]) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="aobtn-like">戻る</a>
    </div>

    <?= $this->Form->end() ?>
</div>

<style>
    .TFoodOrderBox{
        display:flex;
        gap:20px;
        padding-right:5%;
        justify-content:flex-end;
        margin-left:auto;
    }
    #category-select,
    #food-select{
        width:auto !important;
        margin-bottom:0 !important;
    }
    input[readonly],
    textarea[readonly],
    select[readonly]{
        background-color:#eee !important;
        color:#555 !important;
        cursor:not-allowed;
    }
    .readonly-gray{ background-color:#eee !important; }
</style>

<?= $this->Html->script('ui_action') ?>

<script>
    // finalize でも商品選択のために必要
    window.groupedFoods   = <?= json_encode($groupedFoods, JSON_UNESCAPED_UNICODE) ?>;
    window.initialFoodId  = <?= json_encode($initialFoodId, JSON_UNESCAPED_UNICODE) ?>;
    // finalize では最短納品日の手入力チェックは不要のため埋め込まない
</script>