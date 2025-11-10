<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\mDeliveryPattern $mDeliveryPattern
 */
?>
<?= $this->Form->create($mDeliveryPattern, ['type' => 'file']) ?>
<?= $this->element('mDeliveryPattern/form', compact('mode')) ?>
<?= $this->Form->end() ?>
