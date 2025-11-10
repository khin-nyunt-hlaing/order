<?php
// $message … Flashの本文
// $params['type'], $params['selectedIds'] … 上で渡した値
?>
<div style="color:#c62828;margin-bottom:.75rem;"><?= h($message) ?></div>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    if (typeof showConfirmModal === 'function') {
      showConfirmModal(<?= json_encode($params['selectedIds'] ?? []) ?>);
    } else {
      console.warn('showConfirmModal が見つかりません');
    }
  });
</script>
