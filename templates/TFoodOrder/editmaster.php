<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TFoodOrder $TFoodOrder
 */
?>


<div class="TFoodOrder index content">

    <?= $this->Form->create($TFoodOrder,['type' => 'post']) ?>

    <div class="titlebox">
        <p1>食材発注編集(管理者用)</p1>
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

        <!-- 納品希望日-->
            <div class="input-range">
                <div class="label-stack"><span>納品希望日</span></div>
                <?= $this->Form->control('deli_req_date', [
                    'type'     => 'date',
                    'label'    => false,
                    'id'       => 'deli_req_date',
                    'readonly' => true,
                    'class'    => 'readonly-gray',
                ]) ?>
            </div>

        <!-- 納品予定日-->
            <div class="input-range">
            <div class="label-stack"><span>納品予定日</span></div>

            <?= $this->Form->control('deli_shedule_date', [   // ← フィールド名は予定日専用に
                'type'   => 'date',
                'label'  => false,
                'empty'  => true,
                'error' => false,
                'id'       => 'deli_shedule_date',
                'min'   => $TFoodOrder->deli_req_date->i18nFormat('yyyy-MM-dd')
            ]) ?>
            </div>

        <!-- 食材分類 -->
            <div class="input-range">
            <div class="label-stack">
            <span>食材分類</span>
            </div>
            <?= $this->Form->control('category_id', [
                'type' => 'select',
                'options' => $categoryOptions,
                'empty' => '選択してください',
                'id' => 'category-select',
                'value' => $categoryId,
                'label' => false,
            ]) ?>
            <?= $this->Form->error('category_id') ?>
            </div>

        <!-- 食材商品 / 規格（分類に応じてJSで差し替え） -->
            <div class="input-range">
                <div class="label-stack"><span>食材商品 / 規格</span></div>
                <?= $this->Form->control('food_id', [
                'type' => 'select',
                'options' => [],
                'empty' => '分類を先に選択してください',
                'id' => 'food-select',
                'class' => 'no-shared-style',
                'label' => false,
                'value' => $TFoodOrder->food_id,
            ]) ?>
            <?= $this->Form->error('food_id') ?>
            </div>

        <!-- 発注数 -->
            <div class="input-range">
                <div class="label-stack"><span>発注数</span></div>
                <?= $this->Form->control('order_quantity', [
                    'label' => false,
                    'id'    => 'order_quantity',
                    'type'  => 'number',
                    'min'   => 0,
                    'max' => 999
                ]) ?>
            </div>

        <!-- 発注状態（表示用のみ） -->
            <div class="input-range">
                <div class="label-stack"><span>発注状態</span></div>
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

    </div><!-- /.flex-vertical -->

    <div class="TFoodOrderBox">
        <?= $this->Form->button('更新', [
            'class'   => 'akabtn-like',
            'onclick' => 'return checkBeforeSave();',
        ]) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>"
         class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
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