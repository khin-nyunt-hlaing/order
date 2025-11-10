<?php
/**
 * 共通の編集ビュー（全エンティティ対応版）
 *
 * @var \App\View\AppView $this
 * @var string $mode
 */

use App\Model\Entity\MService;

// 使用できるエンティティを定義
$knownEntities = [
    'mfood'         => 'Mfoods',
    'mFoodCategory' => 'MFoodCategories',
    'announce'     => 'TAnnounces',
    'mservice'=> 'MService',
    'mterm' => 'MTerm',
     // 追加する場合はここに追加
];

// エンティティを特定
$entity = null;
$entityName = null;
$entityId = null;

foreach ($knownEntities as $var => $folder) {
    if (isset($$var)) {
        $entity = $$var;
        $entityName = $var;

        // 自動的に *_id フィールドを探す (最初のIDフィールドを取得)
        foreach ($$var as $field => $value) {
            if (preg_match('/_id$/', $field)) {
                $entityId = $value;
                break;
            }
        }

        break;
    }
}

// 削除時のURL設定
$targetAction = ($mode === 'delete' && $entityId !== null)
    ? ['action' => 'delete', $entityId]
    : null;

// フォーム開始
echo $this->Form->create($entity, [
    'url' => $targetAction,
    'type' => 'post'
]);

// element呼び出し
$params = [
    $entityName => $entity,
    'mode' => $mode,
    'mFoodCategories' => $mFoodCategories ?? [],
    'distributions' => $distributions ?? [],
    'facilities' => $facilities ?? [],
];

if ($entityName && isset($knownEntities[$entityName])) {
    echo $this->element($knownEntities[$entityName] . '/form', $params);
} else {
    echo "<p style='color:red;'>フォームが読み込めません。変数が正しく渡されていない可能性があります。</p>";
}
?>

<br>

<div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 20px; gap: 10px;">

    <div>
    <?php if ($mode === 'add'): ?>
    <?= $this->Form->button('追加') ?>
<?php elseif ($mode === 'edit'): ?>
    <?= $this->Form->button('更新') ?>
<?php elseif ($mode === 'delete'): ?>
    <?= $this->Form->button('削除', ['confirm' => '本当に削除しますか？']) ?>
<?php endif; ?>
    </div>

    <div>
        <?= $this->Html->link('戻る', ['action' => 'index'], ['class' => 'button']) ?>
    </div>
</div>
