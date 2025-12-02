<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\TFoodOrder> $tFoodOrder
 */
?>
<div class="TFoodOrder index content">

<div class="TFoodOrderbox1">
    <p class="cuttitlebox">単品食材発注一覧</p>

    <!-- ▼ 検索フォーム -->
    <div class="search-box-wrapper">
        <?= $this->Form->create(null, [
            'type' => 'post',
            'class' => 'search-form',
            'style' => 'width:1040px'
        ]) ?>

        <div class="search-box">
            <div class="search-row0">

                <!-- ▼ 1段目：発注日・納品希望日・書出確定期間 -->
                <div class="search-row1">
                    <label class="search-label">発注日</label>
                    <?= $this->Form->control('order_date_from', [
                        'label' => false,
                        'type' => 'date',
                        'class' => 'start-date',
                        'value' => $this->request->getQuery('order_date_from'),
                        'style' => 'width:140px;'
                    ]) ?>

                    <div class="search-field" style="align-self:center;font-weight:bold;">〜</div>

                    <?= $this->Form->control('order_date_to', [
                        'label' => false,
                        'type' => 'date',
                        'class' => 'end-date',
                        'value' => $this->request->getQuery('order_date_to'),
                        'style' => 'width:140px;'
                    ]) ?>

                    <label class="search-label">納品希望日</label>
                    <?= $this->Form->control('deli_req_date_from', [
                        'label' => false,
                        'type' => 'date',
                        'class' => 'start-date',
                        'value' => $this->request->getQuery('deli_req_date_from'),
                        'style' => 'width:140px;'
                    ]) ?>

                    <div class="search-field" style="align-self:center;font-weight:bold;">〜</div>

                    <?= $this->Form->control('deli_req_date_to', [
                        'label' => false,
                        'type' => 'date',
                        'class' => 'end-date',
                        'value' => $this->request->getQuery('deli_req_date_to'),
                        'style' => 'width:140px;'
                    ]) ?>

                    <?php if ((int)$level === 1): ?>
                        <div class="search-row1">
                            <label class="search-label">書出確定期間</label>

                            <?= $this->Form->control('export_confirm_date_from', [
                                'label' => false,
                                'type' => 'date',
                                'value' => $this->request->getQuery('export_confirm_date_from'),
                                'style' => 'width:140px;'
                            ]) ?>

                            <div class="search-field" style="align-self:center;font-weight:bold;">〜</div>

                            <?= $this->Form->control('export_confirm_date_to', [
                                'label' => false,
                                'type' => 'date',
                                'value' => $this->request->getQuery('export_confirm_date_to'),
                                'style' => 'width:140px;'
                            ]) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ▼ 2段目：発注状態・施設名 -->
                <div class="search-row1">

                    <label class="search-label">発注状態</label>
                    <?= $this->Form->control('order_status', [
                        'label' => false,
                        'type' => 'select',
                        'options' => ['0' => '未確定', '1' => '確定'],
                        'default' => '',
                        'empty' => '未選択',
                        'value' => $this->request->getQuery('order_status')
                    ]) ?>

                    <label class="search-label">施設名</label>

                    <?php if ((int)$level === 1): ?>
                        <?= $this->Form->control('user_name', [
                            'label' => false,
                            'type' => 'select',
                            'options' => $users,
                            'empty' => 'すべて',
                            'value' => $this->request->getQuery('user_id')
                        ]) ?>

                    <?php elseif (in_array((int)$level, [2,4], true)): ?>
                        <div style="min-width:200px; padding:0.3rem 0;"><?= h($userName) ?></div>

                    <?php else: ?>
                        <?= $this->Form->control('user_name', [
                            'label' => false,
                            'type' => 'select',
                            'options' => $codeToName,
                            'empty' => 'すべて',
                            'value' => $this->request->getQuery('user_name')
                        ]) ?>
                    <?php endif; ?>
                </div>

                <!-- ▼ 検索ボタン（右下固定） -->
                <div class="search-button-box">
                    <?= $this->Form->button('検索', [
                        'name' => 'action',
                        'value' => 'search',
                        'class' => 'search-btn'
                    ]) ?>
                </div>

            </div>
        </div>
    </div>

    <p style="text-align:right;margin:0;">件数 <?= $count ?> 件</p>

    <!-- ▼ 一覧テーブル -->
    <div class="Extractscrollbox">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>選択</th>
                    <th>発注日</th>
                    <th>納品希望日</th>
                    <th>納品予定日</th>
                    <th>確定納品日</th>
                    <th>商品名</th>
                    <th>規格</th>
                    <th>発注数</th>

                    <?php if (in_array((int)$permissionCode, [1, 5])): ?>
                        <th>施設名</th>
                    <?php endif; ?>

                    <th>発注状態</th>
                    <th>書出確定日</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($tFoodOrder as $order): ?>
                    <tr class="<?= !empty($order['has_fix'] ?? null) ? 'highlight-row' : '' ?>">
                        <td>
                            <?php
                                $isAdmin  = ($permissionCode === 1);
                                $isEditor = in_array($permissionCode, [2,3,4], true);
                                $isViewer = ($permissionCode === 5);

                                if ($isAdmin) {
                                    echo $this->Form->checkbox("select[{$order['food_order_id']}]", ['class' => 'row-check']);
                                } else {
                                    if (!$isConfirmed) {
                                        echo $this->Form->checkbox("select[{$order['food_order_id']}]", ['class' => 'row-check']);
                                    } else {
                                        echo '—';
                                    }
                                }
                            ?>
                        </td>

                        <td><?= h($order['order_date']) ?></td>
                        <td><?= h($order['deli_req_date']) ?></td>
                        <td><?= h($order['deli_shedule_date']) ?></td>
                        <td><?= h($order['deli_confirm_date']) ?></td>
                        <td><?= h($order['food_name']) ?></td>
                        <td><?= h($order['food_specification']) ?></td>
                        <td><?= h($order['order_quantity']) ?></td>

                        <?php if (in_array((int)$permissionCode, [1, 5])): ?>
                            <td><?= h($order['user_name']) ?></td>
                        <?php endif; ?>

                        <td><?= ['0'=>'未確定','1'=>'確定'][$order['order_status']] ?? '不明' ?></td>
                        <td><?= h($order['export_confirm_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ▼ 操作ボタン -->
    <div class="TFoodOrderbox3" style="margin-top:2%; display:flex; justify-content:space-between; align-items:center;">
        <div class="inbox" style="display:flex; gap:1rem;">

            <?php if ((int)$level === 1): ?>

                <?= $this->Form->button('新規', ['name'=>'action', 'value'=>'add']) ?>
                <?= $this->Form->button('編集', ['name'=>'action', 'value'=>'edit']) ?>
                <?= $this->Form->button('削除', [
                    'name'=>'action',
                    'value'=>'delete',
                    'onclick'=>'return checkBeforeDelete();'
                ]) ?>


                <button type="submit" name="action" value="unconfirm"
                    class="dynamic-confirm" data-action="unconfirm">
                    確定解除
                </button>
                <?= $this->Form->button('データ書出', ['name'=>'action','value'=>'export']) ?>
                <button type="submit" name="action" value="confirm"
                    class="dynamic-confirm" data-action="confirm">
                    確定
                </button>

                

            <?php elseif ((int)$level === 2): ?>

                <?= $this->Form->button('新規', ['name'=>'action', 'value'=>'add']) ?>
                <?= $this->Form->button('編集', ['name'=>'action', 'value'=>'edit']) ?>
                <?= $this->Form->button('削除', [
                    'name'=>'action',
                    'value'=>'delete',
                    'onclick'=>'return checkBeforeDelete();'
                ]) ?>

            <?php endif; ?>
        </div>

        <div class="inbox1">
            <?= $this->Html->link('戻る', ['controller'=>'Mmenus','action'=>'index'], [
                'class'=>'button',
                'style'=>'display:flex;align-items:center;'
            ]) ?>
        </div>
    </div>

    <!-- ▼ エラーモーダル -->
    <div id="confirmModal" class="modal"
        style="display:none; position:fixed; top:30%; left:30%; width:40%; background:#fff; border:2px solid #333; padding:2rem; z-index:1000;">
        
        <p style="color:red;">
        <?php if (isset($confirmError)): ?>
            <?php
                $key = trim((string)$confirmError);
                $messages = [
                    'unregistered' => '未登録のデータが含まれているため、処理を実行できません。',
                    'confirm' => '確定済のデータを含むため、確定できません。',
                    'unconfirm' => '未確定のデータを含むため、確定解除できません。',
                    'already_confirmed' => 'すでに確定済みです。',
                    'already_unconfirmed' => 'すでに未確定の状態です。',
                ];
                $msg = $messages[$key] ?? '選択内容に問題があります。';
            ?>
            <div style="color:red;"><?= h($msg) ?></div>
        <?php endif; ?>
        </p>

        <button onclick="closeModal()">閉じる</button>
    </div>

    <?= $this->Form->end() ?>

    <?php if (!empty($confirmError)): ?>
        <script>
            const selectedIds = <?= json_encode($selectedIds ?? []) ?>;
            window.addEventListener('DOMContentLoaded', () => {
                showConfirmModal(selectedIds);
            });
        </script>
    <?php endif; ?>

</div>
</div>

<!-- ▼ CSS -->
<style>

.search-box {
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    flex-wrap:nowrap;
    gap:0.5rem;
    padding:1rem;
    background:#fff;
    border:1.5px solid #ccc;
    border-radius:0.4rem;
    flex:1 1 auto;
}

.search-row0 {
    display:flex;
    flex-direction:column;
    gap:1.5rem;
    width:900px;
}

.search-row1 {
    display:flex;
    flex-wrap:wrap;
    gap:1rem;
    margin:0 auto;
    align-items:center;
}

.search-label {
    display:inline-block;
    width:100px;
    text-align:right;
    font-weight:normal;
}

.search-field {
    min-width:0 !important;
    max-width:180px !important;
    flex:1;
}

.input.date {
    display:contents !important;
}

/* ▼ 検索ボタン右下配置（修正版） */
.search-button-box {
    display:flex;
    justify-content:flex-end;
    width:100%;
    margin-top:5px;         /* 少し下へ */
}

.search-btn {
    height:35px;
    padding:0 20px;
}

</style>

<!-- ▼ JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const confirmButtons = document.querySelectorAll('.dynamic-confirm');

    confirmButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            const checkboxes = document.querySelectorAll('.row-check:checked');
            const count = checkboxes.length;

            if (count === 0) {
                alert('行が選択されていません。');
                e.preventDefault();
                return false;
            }

            const isConfirm = button.dataset.action === 'confirm';
            const message = `${count}件選択されています。` +
                (isConfirm ? '確定しますか？' : '確定解除しますか？');

            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
</script>
