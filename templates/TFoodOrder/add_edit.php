<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TFoodOrder $TFoodOrder
 */
?>
<?= $this->Form->create($TFoodOrder, ['type' => 'file']) ?>
<?= $this->element('TFoodOrder/form', compact('mode')) ?>
<?= $this->Form->end() ?>
