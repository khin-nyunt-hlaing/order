<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MAnnounceDiv $mAnnounceDiv
 */
?>
<?= $this->Form->create($mAnnounceDiv, ['type' => 'file']) ?>
<?= $this->element('mAnnounceDiv/form', compact('mode')) ?>
<?= $this->Form->end() ?>
