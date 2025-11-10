<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MUserGroup $mUserGroup
 */
?>
<?= $this->Form->create($mUserGroup, ['type' => 'file']) ?>
<?= $this->element('mUserGroup/form', compact('mode')) ?>
<?= $this->Form->end() ?>
