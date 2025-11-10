<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MTerm $mTerm
 */
?>
<?= $this->Form->create($mTerm, ['type' => 'file']) ?>
<?= $this->element('MTerm/form', compact('mode')) ?>
<?= $this->Form->end() ?>
