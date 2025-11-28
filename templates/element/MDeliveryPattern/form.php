<?php
// ▼ 新規登録(add)では $selectedIds が無いので空配列に初期化（Warning防止）
$selectedIds = $selectedIds ?? [];
?>

<div class="mDeliveryPattern index content">
<?= $this->Form->create($mDeliveryPattern, ['type' => 'file']) ?>

    <div class="titlebox">
        <p1><?= $mode === 'edit' ? '献立商品パターン編集' : '献立商品パターン登録' ?></p1>
        <?= $this->Flash->render() ?>   
    </div>

<div class="flex-vertical" style="gap:2rem;">

    <?php if ($mode === 'edit'): ?>
        <div class="input-range">
            <div class="label-stack">
                <span>献立パターンID</span>
            </div>

            <?= $this->Form->control('use_pattern_id_view', [
                'label' => false,
                'id' => 'use_pattern_id_view',
                'type' => 'text',
                'readonly' => true,
                'value' => $mDeliveryPattern->use_pattern_id,
                'style' => 'background-color: #eee;'
            ]) ?>
        </div>
    <?php endif; ?>

    <!-- ▼ 献立商品パターン名称 -->
    <div class="input-range">
        <div class="label-stack">
            <span style="font-size:1.8rem; width:200px">献立商品パターン名称</span>
            <span style="font-size: 1.5rem;">(必須)</span>
        </div>
        <?= $this->Form->control('delivery_pattern_name', [
            'label' => false,
            'id' => 'delivery_pattern_name',
            'type' => 'text',
            'required' => true,
        ]) ?>
    </div>

    <!-- ▼ 表示順 -->
    <div class="input-range">
        <div class="label-stack">
            <span>表示順</span>
            <span style="font-size: 1.5rem;">(必須)</span>
        </div>
        <?= $this->Form->control('disp_no', [
            'label' => false,
            'id' => 'disp_no',
            'type' => 'number',
            'min' => 0,
        ]) ?>
    </div>

    <!-- ▼ 削除フラグ（編集時のみ） -->
    <?php if ($mode === 'edit'): ?>
        <div class="input-range">
            <div class="label-stack">
                <span style="padding-bottom: 16px;">削除</span>
            </div>
            <div class="del-flg-checkbox">
                <?= $this->Form->control('del_flg', [
                    'type' => 'checkbox',
                    'label' => '削除状態にする',
                    'hiddenField' => true,
                    'value' => '1',
                    'checked' => $mDeliveryPattern->del_flg === '1',
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ▼ 献立商品一覧 -->
    <div class="input-range">
        <div class="label-stack">
            <span>献立商品一覧</span>
            <span style="font-size: 1.5rem;">（複数選択可）</span>
        </div>

        <div class="intable-wrapper">
            <table class="intable">
                <thead>
                    <tr style="background-color: #CCE5FF;">
                        <th>選択</th>
                        <th>献立商品ID</th>
                        <th>献立商品名称</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($mDeliveries as $deliveryId => $deliveryName): ?>
                        <tr>
                            <td>
                                <?= $this->Form->checkbox("selected_deliveries[{$deliveryId}]", [
                                    'label'   => false,
                                    'hiddenField'  => false,
                                    'checked' => in_array((string)$deliveryId, (array)$selectedIds, true)
                                ]) ?>
                            </td>
                            <td><?= h($deliveryId) ?></td>
                            <td><?= h($deliveryName) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ▼ ボタン -->
<div class="mDeliveryPatternBox">
    <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['class' => 'akabtn-like']) ?>
    <a href="<?= $this->Url->build(['action' => 'index']) ?>"
       class="aobtn-like"
       onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
</div>

<?= $this->Form->end() ?>
</div>

<style>
.mDeliveryPatternBox {
    display: flex;
    gap: 20px;
    padding-right:5%;
    justify-content: flex-end;
    margin-left: auto;
}
.intable-wrapper {
    max-height: 300px;
    height: auto;
    overflow-y: auto;
    display: block;
    border: 1px solid #333;
    margin-top: 1rem;
    width: 50%;
}
.readonly-id-box {
    background: #f0f0f0;
    border: 1px solid #ccc;
}

</style>
