<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MFood $mFood
 * @var \Cake\Collection\CollectionInterface|string[] $mFoodCategories
 */
?>

<?= $this->Form->create($mfood, ['type' => 'file']) ?>
<?= $this->element('MFoods/form', compact('mode')) ?>
<?= $this->Form->end() ?>