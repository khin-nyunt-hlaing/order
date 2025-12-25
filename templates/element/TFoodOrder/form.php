<div class="TFoodOrder index content">
<?= $this->Form->create($TFoodOrder, ['type' => 'post']) ?>

    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '単品食材発注 編集' : '単品食材発注 登録' ?></p1>
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
               'value' => $deliReqDate ?? ($TFoodOrder->deli_req_date?->format('Y-m-d') ?? ''),
                'readonly' => ($mode === 'edit'),
                'class' => ($mode === 'edit') ? 'readonly-gray' : ''
            ]) ?>
            <?= $this->Form->error('deli_req_date') ?>
            </div>

        <?php if ($mode === 'edit' && $useSvc === 1): ?>
            <!-- 納品予定日 -->
           
            <div class="input-range">
            <div class="label-stack">
                <span>納品予定日</span>
            </div>

            <?= $this->Form->control('deli_shedule_date', [
                'type' => 'date',
                'label' => false,
                'min' => $minDate,
                'value' => $deliReqDate ?? ($TFoodOrder->deli_shedule_date?->format('Y-m-d') ?? ''),
                'readonly' => ($useSvc !== 1),  // ← 管理者以外は readonly
                'class' => ($useSvc !== 1) ? 'readonly-gray' : '',
            ]) ?>

            <?= $this->Form->error('deli_shedule_date') ?>
        </div>

            <!-- 書出確定日-->
            <div class="input-range">
            <div class="label-stack">
            <span>確定納品日</span>
            </div>
            <?= $this->Form->control('deli_confirm_date', [
                    'type' => 'date',
                    'label' => false,
                    'id' => 'deli_confirm_date',
                    'readonly' => true,
                    'value'    => $TFoodOrder->deli_confirm_date
                    ? $TFoodOrder->deli_confirm_date->format('Y-m-d'): '',
                    'class'    => 'readonly-gray'
                ]) ?>
            </div>

             <!-- 書出確定日-->
            <div class="input-range">
            <div class="label-stack">
            <span>書出確定日</span>
            </div>
            <?= $this->Form->control('export_confirm_date', [
                    'type' => 'date',
                    'label' => false,
                    'id' => 'export_confirm_date',
                    'readonly' => true,
                    'value'    => $TFoodOrder->export_confirm_date
                    ? $TFoodOrder->export_confirm_date->format('Y-m-d'): '',
                    'class'    => 'readonly-gray'
                ]) ?>
            </div>
        <?php endif; ?>

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
                'data-addmode' => ($mode === 'add') ? '1' : null // ← これでadd時だけ属性が付く
            ]) ?>
            <?= $this->Form->error('category_id') ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>食材商品 / 規格</span>
            </div>
            <?= $this->Form->control('food_id', [
                'type' => 'select',
                'options' => [],
                'empty' => '分類を先に選択してください',
                'id' => 'food-select',
                'class' => 'no-shared-style',
                'label' => false,
                'data-selected' => $selectedFoodId ?? '',
            ]) ?>
            <?= $this->Html->scriptBlock(<<<'JS'
                    document.addEventListener('DOMContentLoaded', function () {
                    console.log('[BOOT][inline] DOM ready');
                    const cat  = document.getElementById('category-select');
                    const food = document.getElementById('food-select');
                    // console.log('[CHK][inline] has category?', !!cat, 'has food?', !!food, 'data-selected=', food?.dataset?.selected);
                    if (!food) return;

                    const sel = food.dataset?.selected;
                    if (!sel) return;

                    if ([...food.options].some(o => String(o.value) === sel)) {
                        food.value = sel;
                        return;
                    }

                    if (typeof window.groupedFoods !== 'undefined' && cat) {
                        const map = {};
                        Object.entries(window.groupedFoods || {}).forEach(([cId, arr]) => {
                        (arr || []).forEach(it => { map[String(it.id)] = cId; });
                        });
                        const expectedCat = map[sel];
                        if (expectedCat) {
                        cat.value = expectedCat;
                        const foods = window.groupedFoods[expectedCat] || [];
                        food.innerHTML = '<option value="">分類を先に選択してください</option>';
                        foods.forEach(it => {
                            const op = document.createElement('option');
                            op.value = String(it.id);
                            op.textContent = it.label;
                            food.appendChild(op);
                        });
                        food.value = sel;
                        if (food.value !== sel) {
                            const noLeading = sel.replace(/^0+/, '');
                            if (noLeading) food.value = noLeading;
                        }
                        }
                    }
                    });
                JS) ?>

            <?= $this->Form->error('food_id') ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>発注数</span>
            </div>
            <?= $this->Form->control('order_quantity', [
                'label' => false,
                'id' => 'order_quantity',
                'type' => 'number', // 数値と仮定
                'min' => 0,
                'max'=> 999
            ]) ?>
            <?= $this->Form->error('order_quantity') ?>
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
     <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['class' => 'akabtn-like']) ?>
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
#food-select {
    inline-size: 300px !important;   /* ← 今の元サイズ */
    max-inline-size: 300px !important;
    min-inline-size: 300px !important;

    width: 300px !important;         /* 念のため */
    box-sizing: border-box;
}
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
