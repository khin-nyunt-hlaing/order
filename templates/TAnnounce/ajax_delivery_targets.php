<?php if ($deliveryTargets->isEmpty()): ?>
    <tr><td colspan="3">該当する施設はありません</td></tr>
<?php else: ?>
    <?php foreach ($deliveryTargets as $deliveryT): ?>
        <tr>
            <td>
                <?= $this->Form->checkbox('selected_user_ids[]', [
                    'value' => $deliveryT->user_id,
                    'class' => 'toggle-color',
                    'hiddenField' => false,
                    'checked' => !empty($selectedUserIds) && in_array($deliveryT->user_id, $selectedUserIds)
                ]) ?>
            </td>
            <td><?= h($deliveryT->user_id) ?></td>
            <td><?= h($deliveryT->user_name) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>