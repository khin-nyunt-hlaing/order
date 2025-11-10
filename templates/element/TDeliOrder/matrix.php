<?php
/** @var bool $isL1 */
/** @var string $mode */ // 'add' or 'edit'
/** @var bool $canEdit */
$canEdit = $canEdit ?? true;
$isL1    = $isL1 ?? false;
?>

<?php if (($mode === 'add') && $isL1 && empty($deliveryItems)): ?>
  <div id="limitedMsg" class="limited-box" style="padding:1rem;">施設を選択してください。</div>
<?php else: ?>
  
  <div class="limited-box">
    <table class="DE_box">
      <thead>
                            <tr>
                                <th>配食名</th>
                                <?php foreach ($days as $day): ?>
                                <th style="padding: 10px; font-weight: normal !important;"><?= h($day->format('n月j日')) ?>
                                (<?= ['日','月','火','水','木','金','土'][$day->format('w')] ?>)</th>
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
              $val = $quantityValues[$dId][$dateStr] ?? '';
            ?>
            <td style="text-align:center; padding:0.8rem 1rem; border:1px solid #888 !important; vertical-align:middle;">
              <input
                type="number"
                name="quantity[<?= $delivery->delivery_id ?>][<?= $dateStr ?>]"
                value="<?= h($val) ?>"
                class="form-control"
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
