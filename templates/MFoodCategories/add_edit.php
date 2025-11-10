<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MFoodCategory $mFoodCategory
 */
?>
<?= $this->Form->create($mFoodCategory, ['type' => 'file']) ?>
<?= $this->element('MFoodCategories/form', compact('mode')) ?>
<?= $this->Form->end() ?>
