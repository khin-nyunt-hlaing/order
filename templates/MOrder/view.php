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
            <?= $this->Html->link(__('Edit M Order'), ['action' => 'edit', $mOrder->deli_order_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete M Order'), ['action' => 'delete', $mOrder->deli_order_id], ['confirm' => __('Are you sure you want to delete # {0}?', $mOrder->deli_order_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List M Order'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New M Order'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="mOrder view content">
            <h3><?= h($mOrder->deli_order_id) ?></h3>
            <table>
                <tr>
                    <th><?= __('User Id') ?></th>
                    <td><?= h($mOrder->user_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Order Status') ?></th>
                    <td><?= h($mOrder->order_status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Del Flg') ?></th>
                    <td><?= h($mOrder->del_flg) ?></td>
                </tr>
                <tr>
                    <th><?= __('Create User') ?></th>
                    <td><?= h($mOrder->create_user) ?></td>
                </tr>
                <tr>
                    <th><?= __('Update User') ?></th>
                    <td><?= h($mOrder->update_user) ?></td>
                </tr>
                <tr>
                    <th><?= __('Deli Order Id') ?></th>
                    <td><?= $this->Number->format($mOrder->deli_order_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Term Id') ?></th>
                    <td><?= $mOrder->term_id === null ? '' : $this->Number->format($mOrder->term_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Create Date') ?></th>
                    <td><?= h($mOrder->create_date) ?></td>
                </tr>
                <tr>
                    <th><?= __('Update Date') ?></th>
                    <td><?= h($mOrder->update_date) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>