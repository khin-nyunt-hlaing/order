<?php
// 念のため変数の初期化
$users = $users ?? [];
$selectedUserIds = $selectedUserIds ?? [];
?>

<?php foreach ($users as $userId => $userName): ?>
<tr>
    <td>
        <?= $this->Form->control("selected_users[{$userId}]", [
            'type' => 'checkbox',
            'value' => '1',
            'checked' => in_array($userId, $selectedUserIds ?? []),
            'label' => false
        ]) ?>
    </td>
    <td><?= h($userId) ?></td>
    <td><?= h($userName) ?></td>
</tr>
<?php endforeach; ?>

