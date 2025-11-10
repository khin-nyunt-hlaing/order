<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MService $mService
 */
?>
<div class="MService edit content">
    <?= $this->Form->create($MService, [
        'type' => 'file'
    ]) ?>

    <div class="titlebox">
        <p1>発注サービス編集</p1>
    </div>

    <div class="flex-vertical">
        <div class="input-range">
            <div class="label-stack">
                <span>サービスID</span>
                <span style="font-size: 1.5rem;">(変更不可)</span>
            </div>
            <?= $this->Form->control('use_service_id', [
                'label' => false,
                'id' => 'use_service_id',
                'readonly' => true,
                'type' => 'text',        // 明示的にtext指定（なくても可）
                'required' => true,       // 必須指定（任意）
                'disabled' => true,           // ← 非活性にする
                'class' => 'readonly-like'    // ← 背景グレー（既存CSS利用）
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>発注サービス名称</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('service_name', [
                'label' => false,
                'required' => true,       // 必須指定（任意）
                'id' => 'service_name',
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
            ]) ?>
        </div>
    </div>

    <div class="MServiceBox">
        <?= $this->Form->button('更新', ['class' => 'akabtn-like']) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" 
        class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
    </div>

    <?= $this->Form->end() ?>
</div>

<style>
.MServiceBox {
    display: flex;
    gap: 20px;
    padding-right:5%;
    justify-content: flex-end;
    margin-left: auto;
}
</style>
