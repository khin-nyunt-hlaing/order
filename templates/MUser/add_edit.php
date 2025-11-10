<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MUser $mUser
 */
?>
<?= $this->Form->create($mUser, ['type' => 'file']) ?>
<?= $this->element('MUser/form', compact('mode')) ?>
<?= $this->Form->end() ?>
