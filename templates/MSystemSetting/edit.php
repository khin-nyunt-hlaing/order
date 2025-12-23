<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MSystemSetting $mSystemSetting
 */
?>
<div class="MSystemSetting index content">
        <?= $this->Form->create($MSystemSetting, [
        'type' => 'file'
    ]) ?>

    <div class="titlebox">
        <p1>システム設定</p1>
    </div>

    <div class="flex-vertical">

        <!-- 献立 最低発注食数 -->
        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">献立 最低発注食数</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('deli_min_chk', [
                'label' => false,
                'type'  => 'number',
                'min'   => 0,
                'required' => true,
                'value' => $MSystemSetting->deli_min_chk,
            ]) ?>
        </div>

        <!-- 変更可能増減数 -->
        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">変更可能増減数</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('deli_chg_chk', [
                'label' => false,
                'type'  => 'number',
                'min'   => 0,
                'required' => true,
                'value' => $MSystemSetting->deli_chg_chk,
            ]) ?>
        </div>

        <!-- 単品食材 納品可能日 -->
        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">単品食材 納品可能日</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('deli_req_chk', [
                'label' => false,
                'type'  => 'number',
                'min'   => 0,
                'required' => true,
                'value' => $MSystemSetting->deli_req_chk,
            ]) ?>
        </div>

    </div>

    <div class="MSystemSettingBox">
        <?= $this->Form->button('更新', ['class' => 'akabtn-like']) ?>
        <?= $this->Html->link(
        '戻る', 
        ['controller' => 'Mmenus', 'action' => 'index'],
        [
            'class' => 'aobtn-like',
            'onclick' => "return confirm('遷移すると入力内容が破棄されます。よろしいですか？');"
        ]) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
<style>
.MSystemSettingBox {
    display: flex;
    gap: 20px;
    padding-right:5%;
    justify-content: flex-end;
    margin-left: auto;
}
.label-stack {
    text-align: right;
    min-width: 220px;
    font-size: 1.6rem;
    font-weight: 700;
}
</style>