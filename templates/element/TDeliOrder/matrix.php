
<?php
/** @var bool $isL1 */
/** @var string $mode */ // 'add' or 'edit'
/** @var bool $canEdit */
/** @var \App\Model\Entity\MTerm $mTerm */

$canEdit = $canEdit ?? true;
$isL1    = $isL1 ?? false;

// 曜日 → DBカラム対応
$weekdayMap = [
    0 => 'upd_deadline_sun',
    1 => 'upd_deadline_monday',
    2 => 'upd_deadline_tue',
    3 => 'upd_deadline_wed',
    4 => 'upd_deadline_thu',
    5 => 'upd_deadline_fri',
    6 => 'upd_deadline_sat',
];
?>

<?php if (($mode === 'add') && $isL1 && empty($deliveryItems)): ?>
  <div id="limitedMsg" class="limited-box" style="padding:1rem;">施設を選択してください。</div>
<?php else: ?>

<div class="limited-box">
  <table class="DE_box">
    <thead>
  <!-- 献立日 -->
  <tr>
    <th></th>
    <?php foreach ($days as $day): ?>
      <?php $w = (int)$day->format('w'); ?>
      <th style="padding:10px; font-weight:normal;">
        <?= h($day->format('n月j日')) ?>
        (<?= ['日','月','火','水','木','金','土'][$w] ?>)
      </th>
    <?php endforeach; ?>
  </tr>

  <!-- ★ 変更締切日（DBに日付が7日分ある前提） -->
  <tr>
  <th>変更締切日</th>
  <?php foreach ($days as $day): ?>
    <?php
      $w = (int)$day->format('w');

      // 曜日 → DBカラム
      $col = $weekdayMap[$w] ?? null;

      $deadline = null;
      if ($col && !empty($mTerm->$col)) {
          if ($mTerm->$col instanceof \DateTimeInterface) {
              $deadline = \Cake\I18n\FrozenDate::createFromInterface($mTerm->$col);
          } else {
              $deadline = \Cake\I18n\FrozenDate::parse((string)$mTerm->$col);
          }
      }
    ?>
    <th style="padding:10px; font-weight:normal;">
      <?php if ($deadline): ?>
        <?= h($deadline->format('n月j日')) ?>
        (<?= ['日','月','火','水','木','金','土'][(int)$deadline->format('w')] ?>)
      <?php else: ?>
        ―
      <?php endif; ?>
    </th>
  <?php endforeach; ?>
</tr>
</thead>


    <tbody>
    <?php foreach ($deliveryItems as $delivery): ?>
      <tr>
        <td><?= h($delivery->m_delivery->delivery_name ?? '未設定') ?></td>

        <?php foreach ($days as $day): ?>
          <?php
            $dId     = $delivery->delivery_id;
            $dateStr = $day->format('Y-m-d');
            $val     = $quantityValues[$dId][$dateStr] ?? '';
          ?>
          <td style="text-align:center; padding:0.8rem 1rem; border:1px solid #888; vertical-align:middle;">
            <input
              type="number"
              name="quantity[<?= $delivery->delivery_id ?>][<?= $dateStr ?>]"
              value="<?= h($val) ?>"
              style="width:100%; box-sizing:border-box; padding:0.5em;"
              min="0" step="1" max="999"
              <?= $canEdit ? '' : 'readonly disabled' ?>>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php endif; ?>
