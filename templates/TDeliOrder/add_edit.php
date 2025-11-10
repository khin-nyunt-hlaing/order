<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TDeliOrder $tDeliOrder
 */
?>
<?= $this->Form->create($tDeliOrder, ['type' => 'file']) ?>
<?= $this->element('TDeliOrder/form', compact('mode')) ?>
<?= $this->Form->end() ?>
