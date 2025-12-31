<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\TDeliOrder> $tDeliOrder
 */
?>
<div class="tDeliOrder index content" style="widht:80%">
  <?= $this->Form->create(null, ['type' => 'post']) ?>


        <div class="tDeliOrderbox1">
            <p class="cuttitlebox">献立発注一覧</p>
            

    <div class="search-box-wrapper">
            
      <div class="search-box">
          <div class="search-row0">


            <div class="search-row1">
              <label class="search-label">受付開始日</label>
                    <?= $this->Form->control('entry_start_date_from', [
                        'label' => false,
                        'type' => 'date',
                        'value' => $this->request->getData('entry_start_date_from')
                    ]) ?>
                    <label>〜</label>
                    <?= $this->Form->control('entry_start_date_to', [
                        'label' => false,
                        'type' => 'date',
                        'value' => $this->request->getData('entry_start_date_to')
                    ]) ?>
              
              <label class="search-label">新規締切日</label>
                <?= $this->Form->control('add_deadline_date_from', [
                      'label' => false,
                      'type' => 'date',
                      'value' => $this->request->getData('add_deadline_date_from')
                  ]) ?>
                  <label>〜</label>
                  <?= $this->Form->control('add_deadline_date_to', [
                      'label' => false,
                      'type' => 'date',
                      'value' => $this->request->getData('add_deadline_date_to')
                  ]) ?>
                </div>

            <div class="search-row1">
                  <label class="search-label">登録日</label>
                  <?= $this->Form->control('create_date_from', [
                      'label' => false,
                      'type' => 'date',
                      'value' => $this->request->getData('create_date_from')
                  ]) ?>
                  <label>〜</label>
                  <?= $this->Form->control('create_date_to', [
                      'label' => false,
                      'type' => 'date',
                      'value' => $this->request->getData('create_date_to')
                  ]) ?>

                  <label class="search-label">変更日</label>
                  <?= $this->Form->control('update_date_from', [
                      'label' => false,
                      'type' => 'date',
                      'value' => $this->request->getData('update_date_from')
                  ]) ?>
                  <label>〜</label>
                  <?= $this->Form->control('update_date_to', [
                      'label' => false,
                      'type' => 'date',
                      'value' => $this->request->getData('update_date_to')
                  ]) ?>
          </div>

            <div class="search-row1">
              
            <label class="search-label">施設</label>

                <?php
                // level: 0=閲覧, 1=名鉄協商, 2/4=施設
                ?>

                <?php if ((int)$level === 1): ?>
                    <!-- 名鉄協商：すべて選択可 -->
                    <?= $this->Form->control('user_id', [
                        'label' => false,
                        'type' => 'select',
                        'options' => $users,   // 全施設
                        'empty' => '未選択',
                        'value' => $this->request->getData('user_id')
                    ]) ?>

                <?php elseif (in_array((int)$level, [2,4], true)): ?>
                    <!-- 施設：自身の施設のみ（固定） -->
                    <?= $this->Form->control('user_name_display', [
                        'label' => false,
                        'type' => 'text',
                        'value' => $userName,
                        'readonly' => true,
                        'class' => 'readonly-input'
                    ]) ?>

                    <!-- 検索用に user_id は必ず送る -->
                    <?= $this->Form->hidden('user_id', [
                        'value' => $loginUserId
                    ]) ?>

                <?php else: ?>
                    <!-- 閲覧者：管理施設のみ -->
                    <?= $this->Form->control('user_id', [
                        'label' => false,
                        'type' => 'select',
                        'options' => $users,   // disp_user_ids から生成
                        'empty' => '未選択',
                        'value' => $this->request->getData('user_id')
                    ]) ?>
                <?php endif; ?>


                <div class="search-field" style="gap:1rem;">
                   <label class="search-label">発注状態</label>
                    <?= $this->Form->control('order_status', [
                            'label' => false,
                            'type' => 'select',
                            'options' => [
                                  'registered'     => '登録済',
                                  'not_registered' => '未登録'
                              ],
                            'empty' => '未選択',
                            'value' => $this->request->getData('order_status')
                        ]) ?>
                </div>
                
                    <div class="search-field" style="gap:1rem;">
                        <label class="search-label">確定状態</label>
                            <!-- 確定状態 -->
                              <?= $this->Form->control('confirm_status', [
                                  'label' => false,
                                  'type' => 'select',
                                  'options' => [
                                      0 => '未確定',
                                      1 => '確定'
                                  ],
                                  'empty' => '未選択',
                                  'value' => $this->request->getData('confirm_status')
                              ]) ?>
                      </div>
                  </div>
                  <div class="search-row1">
                    <div class="search-field" style="gap:0.5rem;">
                        <label class="search-label">施設名称</label>

                        <?= $this->Form->control('facility_name', [
                            'label' => false,
                            'type' => 'text',
                            'disabled' => ($level !== 1),
                            'value' => $this->request->getData('facility_name')
                        ]) ?>

                        <span class="search-note">（部分一致）</span>
                    </div>

                    <div class="search-field check-inline">
                      <label class="search-label">受付完了も表示する</label>
                      <?= $this->Form->control('include_completed', [
                          'type' => 'checkbox',
                          'label' => false,
                          'checked' => (bool)$this->request->getData('include_completed'),
                          'hiddenField' => false
                      ]) ?>
                  </div>


          </div>

        <div class="right-side" style="max-width: 100px; width: 100px;">
            <div class="search-col" style="justify-self: end;">
              <?= $this->Form->hidden('action', ['value' => 'search']) ?>
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
        <th style="padding: 1rem 1.5rem;">献立期間開始日</th>
        <th>献立期間終了日</th>
        <th>新規締切日</th>
        <th>受付状態</th>
        <th>施設名</th>
        <th>発注状態</th>
        <th>確定状態</th>
      </tr>
    </thead>
    <tbody>
      <!--  デバッグ表示：1件だけ -->
      <?php foreach ($tDeliOrder as $order): ?>
        <tr>
          <!--  チェックボックス：term_id 単位 -->
            <?php
              $ownerUid = $order->user_id ?? $loginUserId;
              $disabled = !empty($order->can_select) ? false : true;
              $title    = $disabled ? ($order->disabled_reason ?? '操作できません') : null;
            ?>
            <td>
              <?= $this->Form->checkbox(
                    "select[{$order->term_id}][{$ownerUid}]",
                    ['class' => 'toggle-color', 'disabled' => $disabled, 'title' => $title,
                      'value'        => '1',           // 明示
                      'hiddenField'  => false        // ★ 未チェックの 0 を送らない
                    ]
                  ) ?>
            </td>

          <!--  献立期間開始・終了・締切 -->
          <td>
        <?php
            // ① 日付は必ず表示する（null対策）
            $dateText = !empty($order->start_date)
                ? h($order->start_date)
                : '－';

            // ② クエリパラメータを安全に構築
            $query = [
                'term_id' => $order->term_id,
                // user_id が無ければログインユーザー（名鉄協商含む）
                'user_id' => $order->user_id ?? $loginUserId,
            ];

            // deli_order_id がある時だけ付与
            if (!empty($order->deli_order_id)) {
                $query['deli_order_id'] = $order->deli_order_id;
            }

            // ③ 必ずリンクを生成
            echo $this->Html->link(
              $dateText,
              ['action' => 'edit', '?' => $query],
              [
                  'class' => 'user-id-link',
                  'onclick' => 'event.stopPropagation();'
              ]
          );
        ?>
        </td>
          <td><?= h($order->end_date) ?></td>
          <td><?= h($order->add_deadline_date) ?></td>

          <!--  受付状態：コントローラで加工済み -->
          <td><?= h($order->reception_status ?? '新規受付中') ?></td>

          <!--  施設名（行ごとの表示名を使う） -->
          <td><?= h($order->display_user_name ?? $userName) ?></td>

          <!--  発注状態：deli_order_id 有無 -->
          <td><?= h($order->order_status_label ?? '') ?></td>

          <!--  確定状態：コントローラで加工済み -->
          <td><?= h($order->confirm_status ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php 
    // ru を安全に定義（AppControllerから来ない場合の保険） 
    $ru = (int)(($routeUseDiv ?? ($this->getRequest()->getAttribute('route_use_div') ?? 1)));
    
    // 受け取り（未定義安全化）
    $hasSelectable    = $hasSelectable    ?? false;
    $hasConfirmable   = $hasConfirmable   ?? false;
    $hasUnconfirmable = $hasUnconfirmable ?? false;

    // 権限
    $isAdmin  = ($permissionCode === 1);
    $isEditor = in_array($permissionCode, [2,3,4], true);

    // 各ボタン用の属性
    $addAttrs       = ($isAdmin || $hasSelectable)     ? [] : ['disabled' => 'disabled', 'title' => '追加可能な行がありません'];
    $confirmAttrs   = ($isAdmin && $hasConfirmable)    ? [] : ['disabled' => 'disabled', 'title' => '確定可能な行がありません'];
    $unconfirmAttrs = ($isAdmin && $hasUnconfirmable)  ? [] : ['disabled' => 'disabled', 'title' => '解除可能な行がありません'];
    $exportAttrs    = ($isAdmin)                       ? [] : ['disabled' => 'disabled', 'title' => '管理者のみ操作可能'];
  ?>

<?php if ($ru !== 0): // 0=非表示 のときは丸ごと出さない ?>
  <div class="TDeliOrderbox3" style="margin-top:5%; display:flex; justify-content:space-between; align-items:center;">
    <div class="inbox" style="display:flex; gap: 1rem;">
      <?php $selector = "input[type=checkbox][name^='select']:checked"; ?>

    <?php
        // 権限による可否をまとめて判定
        $isAdmin  = ((int)$level === 1); // 管理
        $isUpdate = ((int)$level === 2); // 更新
        $isView   = !$isAdmin && !$isUpdate; // 閲覧

        // 閲覧権限なら disabled にする
        $disabled = $isView ? ['disabled' => true] : [];
        ?>

    <?= $this->Form->button('登録', [
            'name'  => 'action',
            'value' => 'add',
            'style' => 'margin-left:10%',
        ] + $addAttrs + $disabled) ?>

    <?php $confirmStyle = 'margin-left:30%' . ($isAdmin ? '' : ';display:none'); ?>
    <?= $this->Form->button('確定', [
                            'name'  => 'action',
                            'value' => 'confirm',
                            'style' => $confirmStyle,
                            'onclick' => "const n=document.querySelectorAll(\"$selector\").length;
                                          if(n===0){alert('配食発注が選択されていません。');return false;}
                                          return confirm('選択されている行を確定しますが、よろしいですか？');",
        ] + $confirmAttrs + ($isAdmin ? [] : ['style'=>'display:none'])) ?>

    <?= $this->Form->button('確定解除', [
            'name'  => 'action',
            'value' => 'unconfirm',
            'onclick' => "const n=document.querySelectorAll(\"$selector\").length;
                          if(n===0){alert('配食発注が選択されていません。');return false;}
                          return confirm('選択されている行を確定解除しますが、よろしいですか？');",
        ] + $unconfirmAttrs + ($isAdmin ? [] : ['style'=>'display:none'])) ?>

    <?= $this->Form->button('データ書出', [
            'name'  => 'action',
            'value' => 'export',
        ] + $exportAttrs + ($isAdmin ? [] : ['style'=>'display:none'])) ?>
    </div>

    <div class="inbox1">
      <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], [
        'class' => 'button',
        'style'=>'display: flex; align-items: center;'
      ]) ?>
    </div>
  </div>
<?php endif; ?>

<?php
// ビューの先頭〜少なくとも endif の前で
$modalFlash = $this->Flash->render('modal'); // 'modal' キーのフラッシュ
?>
<?= $this->element('modalMessage') /* 上の <dialog> を含む要素 */ ?>

<!-- Flashを安全にBase64で埋め込む -->
<div id="modal-flash"
     data-html="<?= h($modalFlash !== null ? base64_encode($modalFlash) : '') ?>">
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
  document.addEventListener('DOMContentLoaded', () => {
    const holder = document.getElementById('modal-flash');
    if (!holder) return;

    const b64 = holder.dataset.html || '';
    if (!b64) return; // Flashが無ければ何もしない

    // Base64 -> Uint8Array -> UTF-8 文字列（※1回だけ復号）
    const bytes   = Uint8Array.from(atob(b64), c => c.charCodeAt(0));
    const decoded = new TextDecoder('utf-8').decode(bytes);

    const dlg    = document.getElementById('confirmModal');
    const bodyEl = document.getElementById('confirmModalBody');
    const footEl = document.getElementById('confirmModalFooter');
    if (!dlg || !bodyEl || !footEl) return;

    bodyEl.innerHTML = decoded; // ここにフラッシュHTMLを流し込む（trusted想定）
    footEl.innerHTML = '<button value="close" autofocus>閉じる</button>';

    if (typeof dlg.showModal === 'function') {
      dlg.showModal();
    } else {
      dlg.setAttribute('open', '');
    }
  });
<?php $this->Html->scriptEnd(); ?>


  </div>
</div>
<style>
.search-box {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    padding: 1rem;
    background: #fff;
    border: 1.5px solid #ccc;
    border-radius: 0.4rem;
    justify-content: flex-start;   /* 左寄せ */
    width: 100%;
    box-sizing: border-box;
}


/* 左側ブロック（縦並び） */
.search-row0 {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
}

/* 各行：横並び・左寄せ固定 */
.search-row1 {
    display: flex;
    flex-wrap: nowrap;
    gap: 1rem;
    align-items: center;
    width: 100%;
    justify-content: flex-start;   /* ★ 左寄せ */
    margin: 0;                     /* ★ 中央寄せ解除 */
}

/* 各入力ブロック */
.search-field {
    display: flex;
    align-items: center;
    text-align: center;
    min-width: 180px;
    flex-direction: row;
    margin-left: 0;
}

/* input共通 */
.search-box .input {
    display: block;
    min-width: 120px !important;
    margin: 0 !important;
}

/* ラベル */
.search-label {
    display: inline-block;
    width: 100px;
    text-align: right;
    font-weight: normal;
}

/* 右側（検索ボタン） */
.right-side {
    margin-left: auto;
    flex-shrink: 0;
}

.search-col {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
}

/* テーブル */
.styled-table td,
.styled-table th {
    padding: 1rem 0.5rem !important;
}
.user-id-link {
    color: #0000EE;          /* ブラウザ標準の青 */
    text-decoration: underline;
    cursor: pointer;
}

.user-id-link:visited {
    color: #551A8B;          /* 訪問済み（任意） */
}
.readonly-input {
    background-color: #f5f5f5;
    color: #333;
    cursor: not-allowed;
}
/* 横並び・折り返し禁止 */
.check-inline {
    display: flex;

    gap:5.5rem;
    white-space: nowrap;
}

/* CakePHP が生成する div.input を潰す */
.check-inline .input {
    margin: 10 !important;
}

/* label を折らせない */
.check-inline .search-label {
    white-space: nowrap;
    margin: 0;
}

</style>
<?= $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js') ?>
<script>
  $(document).ready(function() {
      $('input.toggle-color[type="checkbox"]').on('change', function() {
          let row = $(this).closest('tr');
          if ($(this).is(':checked')) {
              row.addClass('highlight-row');
          } else {
              row.removeClass('highlight-row');
          }
      });
  });
</script>
