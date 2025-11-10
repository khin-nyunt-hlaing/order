<?php
/**
 * @var \App\Model\Entity\MFoodCategory $mFoodCategory
 * @var string $mode
 */

?>
<div class="mfood-categories index content">
    <?= $this->Form->create($mFoodCategory, [
        'type' => 'file'
    ]) ?>

    <h3><?= $mode === 'edit' ? '食材分類編集' : '食材分類追加' ?></h3>

    <div class="flex-vertical">
        <div class="input-range">
            <div class="label-stack">
                <span>分類ID</span>
                <span style="font-size: 1.5rem;">
                    <?= $mode === 'edit' ? '(必須)' : '(自動採番)' ?>
                </span>
            </div>
            <?= $this->Form->control('category_id', [
                'label' => false,
                'type' => 'text',
                'readonly' => true,
                'class' => 'readonly-like'
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>分類名称</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('category_name', ['label' => false]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>表示順</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('disp_no', [
                'label' => false,
                'id' => 'disp_no',
                'name' => 'disp_no',
                'min' => 0,
            ]) ?>
        </div>
        <?php if ($mode === 'edit'): ?>
                <div class="input-range">
                    <div class="label-stack">
                        <span>削除</span>
                        <span style="font-size: 1.5rem;">(必須)</span>
                    </div>
                    <div class="del-flg-checkbox">
                        <?= $this->Form->control('del_flg', [
                            'type' => 'checkbox',
                            'label' => '削除状態にする',
                            'hiddenField' => true,
                            'value' => '1',
                            'checked' => $mFoodCategory->del_flg === '1',
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>
    </div>
    

    <div class="mFoodCategoriesBox">
        <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['class' => 'akabtn-like']) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" 
        class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
        <!-- <a href="<?= $this->Url->build(['action' => 'index']) ?>"
            class="aobtn-like"
            onclick="return confirmDiscard();">戻る</a> -->
    </div>

    <?= $this->Form->end() ?>
</div>

<style>
    .input input,
    .input select,
    .input textarea {
        margin-bottom: 0 !important;
    }
    .flex-vertical {
        padding: 5% 0 5% 5%;
        display: flex;
        flex-direction: column;
        gap: 3rem;
        justify-content: center;
    }
    .input-range {
        align-items: center;
        display: flex;
        gap: 0.5rem;
    }
    .label-stack {
        display: flex;
        flex-direction: column;
        font-size: 2rem;
        align-items: center;
        width: 8em;
        padding-bottom: 15px;
    }
    .mFoodCategoriesBox {
        display: flex;
        gap: 20px;
        padding-right: 5%;
        justify-content: flex-end;
        margin-left: auto;
    }
    .readonly-like {
        background-color: #f5f5f5;
        color: #888;
    }
</style>
