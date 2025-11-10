<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MDelivery $mDelivery
 */
?>
<?= $this->Form->create($mDelivery, ['type' => 'file']) ?>
<?= $this->element('MDelivery/form', compact('mode')) ?>
<?= $this->Form->end() ?>
