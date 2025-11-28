<div class="mDelivery index content">
<?= $this->Form->create($mDelivery, ['type' => 'file']) ?>

    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '献立商品編集' : '献立商品登録' ?></p1>
    </div>
    
<div class="flex-vertical">
            <div class="input-range">
            <div class="label-stack">
            <span>献立商品ID</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('delivery_id', [
                'label' => false,
                'id' => 'delivery_id',
                'type' => 'text',
                'required' => true,
                'readonly' => true, // ←常にグレーアウト
                'value' => $mDelivery->delivery_id, // ←addは＋1済、editは既存値
                'style' => 'background-color: #eee;' // グレー背景
            ]) ?>


            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>商品名称</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('delivery_name', [
                    'label' => false,
                    'id' => 'delivery_name',
                ]) ?>
            </div>

            <div class="input-range">
            <div class="label-stack">
            <span>表示順</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('disp_no', [
                'label' => false,
                'id' => 'disp_no',
                'min' => 0,
                'required' => true
            ]) ?>
            </div>

                        <?php if ($mode === 'edit'): ?>
                <div class="input-range">
                    <div class="label-stack">
                        <span style="padding-bottom: 18px;">削除</span>
                    </div>
                    <div class="del-flg-checkbox">
                        <?= $this->Form->control('del_flg', [
                            'type' => 'checkbox',
                            'label' => '削除状態にする',
                            'hiddenField' => true,
                            'value' => '1',
                            'checked' => $mDelivery->del_flg === '1',
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
<div class="mDeliveryBox">
     <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['id' => '', 'class' => 'akabtn-like']) ?>
     <a id="" href="<?= $this->Url->build(['action' => 'index']) ?>"
      class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
     
</div>

<?= $this->Form->end() ?>
</div>

<style>
    .mDeliveryBox{
        display: flex;
        gap: 20px;
        padding-right:5%;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
    }
</style>