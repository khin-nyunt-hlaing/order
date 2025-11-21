<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\TFoodOrder> $tFoodOrder
 */
?>
<div class="TFoodOrder index content">

<div class="TFoodOrderbox1">
    <p class="cuttitlebox">単品食材発注一覧</p>
            

    <div class="search-box-wrapper">
      <?= $this->Form->create(null, ['type' => 'post', 'class' => 'search-form', 'style' =>'width:1040px']) ?>
        <div class="search-box">
          <div class="search-row0">


            <div class="search-row1">

              <!-- 発注日 -->
              <label class="search-label">発注日</label>
              <?= $this->Form->control('order_date_from', [
                  'label' => false,
                  'type' => 'date',
                  'class' => 'start-date',
                  'value' => $this->request->getQuery('order_date_from'),
                  'style' => 'width:140px;'
              ]) ?>
              <div class="search-field" style="align-self: center; font-weight: bold; width: auto; min-width: unset;">〜</div>
              <?= $this->Form->control('order_date_to', [
                  'label' => false,
                  'type' => 'date',
                  'class' => 'end-date',
                  'value' => $this->request->getQuery('order_date_to'),
                  'style' => 'width:140px;'
              ]) ?>

              <!-- 納品希望日 -->
              <label class="search-label">納品希望日</label>
              <?= $this->Form->control('deli_req_date_from', [
                  'label' => false,
                  'type' => 'date',
                  'class' => 'start-date',
                  'value' => $this->request->getQuery('deli_req_date_from'),
                  'style' => 'width:140px;'
              ]) ?>
              <div class="search-field" style="align-self: center; font-weight: bold; width: auto; min-width: unset;">〜</div>
              <?= $this->Form->control('deli_req_date_to', [
                  'label' => false,
                  'type' => 'date',
                  'class' => 'end-date',
                  'value' => $this->request->getQuery('deli_req_date_to'),
                  'style' => 'width:140px;'
              ]) ?>

              <!-- 追加 -->
              <?php if ((int)$level === 1): ?>
              <div class="search-row1">
                  <label class="search-label">書出確定期間</label>

                  <?= $this->Form->control('export_confirm_date_from', [
                      'label' => false,
                      'type' => 'date',
                      'class' => 'start-date',
                      'value' => $this->request->getQuery('export_confirm_date_from'),
                      'style' => 'width:140px;'
                  ]) ?>

                  <div class="search-field" style="align-self:center; font-weight:bold; width:auto;">〜</div>

                  <?= $this->Form->control('export_confirm_date_to', [
                      'label' => false,
                      'type' => 'date',
                      'class' => 'end-date',
                      'value' => $this->request->getQuery('export_confirm_date_to'),
                      'style' => 'width:140px;'
                  ]) ?>

              </div>
              <?php endif; ?>

          </div>
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
                          <?php if ((int)$level === 1): // 管理：全施設セレクト ?>
                            <?= $this->Form->control('user_name', [
                                'label' => false,
                                'type' => 'select',
                                'options' => $users,
                                'empty' => 'すべて',
                                'value' => $this->request->getQuery('user_id')
                            ]) ?>

                          <?php elseif (in_array((int)$level, [2,4], true)): // 更新：自施設固定（POSTしない） ?>
                            <div style="min-width:200px; padding: 0.3rem 0;"><?= h($userName) ?></div>

                          <?php else: // 閲覧（サービス5）：disp_user_ids 限定セレクト ?>
                            <?= $this->Form->control('user_name', [
                                'label' => false,
                                'type' => 'select',
                                'options' => $codeToName,     // controllerでdisp_user_idsから作成
                                'empty' => 'すべて',
                                'value' => $this->request->getQuery('user_name')
                            ]) ?>
                          <?php endif; ?>
                    </div>
                </div>

              <div class="search-col" style="grid-column: 3; justify-self: end;">
                    <div class="search-field" style="max-width:120px;">
                        <?= $this->Form->button('検索', ['name' => 'action', 'value' => 'search']) ?>
                    </div>
                </div>
            </div>
          </div>
    </div>

    <p style="text-align:right; margin:0;">件数 <?= $count ?> 件</p>

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
                $isAdmin  = ($permissionCode === 1);                  // サービス1＝管理
                $isEditor = in_array($permissionCode, [2,3,4], true); // サービス2〜4＝更新
                $isViewer = ($permissionCode === 5);                  // サービス5＝閲覧

                if ($isAdmin) {
                    echo $this->Form->checkbox("select[{$order['food_order_id']}]", [
                        'class' => 'row-check',
                    ]);
                } else {
                    if (!$isConfirmed) {
                        echo $this->Form->checkbox("select[{$order['food_order_id']}]", [
                            'class' => 'row-check',
                        ]);
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
            

            <?php $statusLabels = [0 => '未確定', 1 => '確定']; ?>
            <td><?= h($statusLabels[$order['order_status']] ?? '不明') ?></td>
            <td><?= h($order['export_confirm_date']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>


  <div class="TFoodOrderbox3" style="margin-top:5%; display: flex; justify-content: space-between; align-items: center;">
  <div class="inbox" style="display: flex; gap: 1rem;">
     <?php if ((int)$level ===1): // 管理=全部 ?>
      <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
      <?= $this->Form->button('編集', ['name' => 'action', 'value' => 'edit']) ?>
      <?= $this->Form->button('削除', [
    'name' => 'action',
    'value' => 'delete',
    'onclick' => 'return checkBeforeDelete();'
    ]) ?>


  <!-- 確定解除ボタン -->
  <button type="submit" name="action" value="unconfirm" class="dynamic-confirm" data-action="unconfirm">
    確定解除
  </button>

      <?= $this->Form->button('データ書出', ['name' => 'action', 'value' => 'export']) ?>
      
  <!-- 確定ボタン -->
   <?= $this->Form->create(null, [
    'url' => ['action' => 'confirm'],
    'type' => 'post'
    ]) ?>
  <button type="submit" name="action" value="confirm" class="dynamic-confirm" data-action="confirm" style="margin-left:10%;">
    確定＋データ書出
  </button>

    <?php elseif ((int)$level === 2): // 更新=追加・更新のみ（※削除/確定/解除/書出しは出さない） ?>
      <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
      <?= $this->Form->button('編集', ['name' => 'action', 'value' => 'edit']) ?>
      <?= $this->Form->button('削除', [
      'name' => 'action',
      'value' => 'delete',
      'onclick' => 'return checkBeforeDelete();'
      ]) ?>

    <?php else: // 閲覧=非表示 ?>
      <!-- 閲覧権限はボタン非表示 -->
    <?php endif; ?>
  </div>

  <div class="inbox1">
    <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], [
      'class' => 'button',
      'style'=>'display: flex; align-items: center;'
    ]) ?>
  </div>
  </div>
  <div id="confirmModal" class="modal" style="display:none; position:fixed; top:30%; left:30%; width:40%; background:#fff; border:2px solid #333; padding:2rem; z-index:1000;">
  <p style="color:red;">
  <?php if (isset($confirmError)): ?>
  <?php
    $key = trim((string)$confirmError); // ← ここが必要
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
<style>
  .search-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    align-items: flex-end;
    flex-wrap: nowrap;
    gap: 0.5rem;
    padding: 1rem;
    background: #fff;
    border: 1.5px solid #ccc;
    border-radius: 0.4rem;
    flex: 1 1 auto;   /* ← これが無いと中身分の幅で止まる */
    min-width: 0;
    max-width: none; /* または 1020px に変更 */
  }

  /* 左側：縦2段のブロック */
      .search-row0 {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      width:900px
      }

  .input input, .input select, .input textarea {
    margin-bottom: 0 !important;
    }


  /* 各行：横に並べる（折り返さない） */
      .search-row1 {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin: 0 auto;
      align-items: center;
  }

  /* 中のフォーム */
  .search-field {
    min-width: 0 !important;
    max-width: 180px !important; /* ← 例：親に合わせて広がる */
    flex: 1;         /* ← 可能であれば柔軟拡張 */
    box-sizing: border-box;
  }
  .child-box {
      display: flex;           /* 中の要素を横に */
      flex-direction: row;
      gap: 10px;
      align-items: center;
      }
      .TFoodOrderBox{
      display: flex;
      gap: 20px;
      padding-right:5%;
      justify-content: flex-end;
      margin-left: auto; /* これが右寄せのポイント */
      }
        .search-label{
      display: inline-block;
      width: 100px;        /* ← 横幅固定 */
      text-align: right;   /* ← 右詰め */
      font-weight: normal; /* ← 太字解除（必要に応じて） */
    }
      .search-box .input{
        display: block;
        min-width: 120px !important;
        margin: 0 !important;
    }
    .input.date {
    display: contents !important;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const confirmButtons = document.querySelectorAll('.dynamic-confirm');

    confirmButtons.forEach(function (button) {
      button.addEventListener('click', function (e) {
        const checkboxes = document.querySelectorAll('.row-check:checked');
        const count = checkboxes.length;

        if (count === 0) {
          alert('行が選択されていません。');
          e.preventDefault(); // フォーム送信をキャンセル
          return false;
        }

        const isConfirm = button.dataset.action === 'confirm';
        const actionText = isConfirm ? '確定しますか？' : '確定解除しますか？';
        const message = `${count}件選択されています。${actionText}`;

        if (!confirm(message)) {
          e.preventDefault(); // ユーザーがキャンセルした場合も送信中止
        }
      });
    });
  });
</script>
