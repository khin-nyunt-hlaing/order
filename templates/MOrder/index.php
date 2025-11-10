<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MOrder> $mOrder
 */
?>
<div class="mOrder index content">
    <?= $this->Html->link(__('New M Order'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('M Order') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('deli_order_id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('term_id') ?></th>
                    <th><?= $this->Paginator->sort('order_status') ?></th>
                    <th><?= $this->Paginator->sort('del_flg') ?></th>
                    <th><?= $this->Paginator->sort('create_user') ?></th>
                    <th><?= $this->Paginator->sort('create_date') ?></th>
                    <th><?= $this->Paginator->sort('update_user') ?></th>
                    <th><?= $this->Paginator->sort('update_date') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mOrder as $mOrder): ?>
                <tr>
                    <td><?= $this->Number->format($mOrder->deli_order_id) ?></td>
                    <td><?= h($mOrder->user_id) ?></td>
                    <td><?= $mOrder->term_id === null ? '' : $this->Number->format($mOrder->term_id) ?></td>
                    <td><?= h($mOrder->order_status) ?></td>
                    <td><?= h($mOrder->del_flg) ?></td>
                    <td><?= h($mOrder->create_user) ?></td>
                    <td><?= h($mOrder->create_date) ?></td>
                    <td><?= h($mOrder->update_user) ?></td>
                    <td><?= h($mOrder->update_date) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $mOrder->deli_order_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $mOrder->deli_order_id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $mOrder->deli_order_id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $mOrder->deli_order_id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>