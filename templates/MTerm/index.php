<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MTerm> $mTerm
 */
?>
<div class="mterms index content">
    <h3 class="cuttitlebox">献立期間一覧</h3>

    <div class="search-box-wrapper">
  <div class="search-box">
    <?= $this->Form->create(null, [
      'type' => 'post',
      'class' => 'search-form'
    ]) ?>
      <div class="search-field">
        <?= $this->Form->control('add_deadline_from', ['type' => 'date', 'label' => '新規締切日（開始）']) ?>
      </div>
      <p>~</p>
      <div class="search-field">
        <?= $this->Form->control('add_deadline_to', ['type' => 'date', 'label' => '新規締切日（終了）']) ?>
      </div>
      <div class="search-field">
        <?= $this->Form->button('抽出', ['name' => 'action', 'value' => 'search']) ?>
      </div>
      <?= $this->Form->end() ?>
  </div>
</div>

    <?= $this->Form->create(null, ['type' => 'post']) ?>
    <p style="text-align:right">件数 <?= is_countable($MTerm) ? count($MTerm) : 0 ?> 件</p>
    


    <div class="Extractscrollbox">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>選択</th> <!-- 🔸 チェックボックス列 -->
                    <th>献立期間</th>
                    <th>受付開始日</th>
                    <th>新規締切日</th>
                    <th>発注受付</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($MTerm as $MTerm): ?>
                <tr>
                    <!-- 🔸 チェックボックス（複数選択可能） -->
                    <td>
                       <?= $this->Form->control("select[{$MTerm->term_id}]", [
                           'type' => 'checkbox',
                           'label' => false,
                           'class' => 'toggle-color'
                       ]) ?>
                    </td>

                    <!-- 🔸 各データ列 -->
                    <td><?= h($MTerm->start_date) ?> ～ <?= h($MTerm->end_date) ?></td>
                    <td><?= h($MTerm->entry_start_date) ?></td>
                    <td><?= h($MTerm->add_deadline_date) ?></td>
                    <td><?= h($MTerm->status_message ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    
    <!-- 🔽 操作ボタン -->
    <div class="buttonlist">
    <?= $this->Form->button('追加', ['name' => 'action', 'value' => 'add']) ?>
    <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?>
    <?= $this->Form->button('削除', [
    'name' => 'action',
    'value' => 'delete',
    'onclick' => 'return checkBeforeDelete();'
    ]) ?>
    <?= $this->Form->button('ファイル取込', ['name' => 'action', 'value' => 'upload']) ?>
</div>


    <!-- 🔽 戻るリンク -->
    <div>
        <?= $this->Html->link('戻る', ['controller' => 'Mmenus','action' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
</div>

    <!-- 🔽 フォーム終了 -->
    <?= $this->Form->end() ?>

    <style>
    .highlight {
    background-color: #ffd6d6;
    font-weight: bold;
    }
    </style>
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
