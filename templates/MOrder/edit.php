<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MOrder $mOrder
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $mOrder->deli_order_id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $mOrder->deli_order_id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List M Order'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="mOrder form content">
            <?= $this->Form->create($mOrder) ?>
            <fieldset>
                <legend><?= __('Edit M Order') ?></legend>
                <?php
                    echo $this->Form->control('user_id');
                    echo $this->Form->control('term_id');
                    echo $this->Form->control('order_status');
                    echo $this->Form->control('del_flg');
                    echo $this->Form->control('create_user');
                    echo $this->Form->control('create_date', ['empty' => true]);
                    echo $this->Form->control('update_user');
                    echo $this->Form->control('update_date', ['empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
