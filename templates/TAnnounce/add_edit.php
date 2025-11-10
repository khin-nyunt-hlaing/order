<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TAnnounce $announce
 */
?>
<?= $this->Form->create($TAnnounce, ['type' => 'file']) ?>
<?= $this->element('TAnnounce/form', [compact('mode'),'isEdit' => true]) ?>
<?= $this->Form->end() ?>
