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
        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">配食 最低発注食数</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('deli_min_chk', [
                'label' => false,
                'id' => 'deli_min_chk',
                'name' => 'deli_min_chk',
                'min' => 0,
                'value' => $MSystemSetting->deli_min_chk,
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">配食 変更可能増減数</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('deli_chg_chk', [
                'label' => false,
                'id' => 'deli_chg_chk',
                'name' => 'deli_chg_chk',
                'min' => 0,
                'value' => $MSystemSetting->deli_chg_chk,
            ]) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span style="font-size: 1.8rem;">食材 納品可能日</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('deli_req_chk', [
                'label' => false,
                'id' => 'deli_req_chk',
                'name' => 'deli_req_chk',
                'min' => 0,
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
</style>