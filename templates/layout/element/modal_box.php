<!-- モーダル共通Element -->
<div id="<?= h($id ?? 'modal-default') ?>" class="modal" style="display: none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    
    <h2 class="modal-title">お知らせ閲覧</h2>
    
    <div class="modal-section">
      <p class="modal-label">お知らせタイトル</p>
      <div class="modal-title-content" style="font-size:2rem !important;"><?= h($announceTitle ?? 'タイトル未設定') ?></div>
    </div>

    <div class="modal-section" style="height:400px;">
      <p class="modal-label">お知らせ</p>
      <div class="modal-body-content" style="height:390px;"><?= h($announceText ?? '本文未設定') ?></div>
    </div>

    <div class="modal-section">
      <p class="modal-label">添付ファイル</p>
      <div class="modal-files">
        <?php if (!empty($attachedFiles)): ?>
          <div class="file-box-wrapper">
            <?php foreach ($attachedFiles as $i => $file): ?>
              <div class="file-box">
                <a href="<?= h($file['url']) ?>" target="_blank"><?= ($i + 1) ?>. <?= h($file['name']) ?></a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- 基本設定　css -->
<style>
  .modal {
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.4);
  overflow-y: auto;
  }
  .modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 1rem;
    width: 50%;
    border-radius: 8px;
  }
  .close {
    float: right;
    font-size: 1.5rem;
    cursor: pointer;
  }
  .modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 1rem;
    width: 90%;
    max-width: 800px;
    border-radius: 8px;
  }

  /* 中型モニター用 */
  @media screen and (min-width: 768px) {
    .modal-content {
      width: 70% !important;;
      max-width: 1000px !important;
      height:700px !important;
    }
  }

  /* 大型モニター用 */
  @media screen and (min-width: 1200px) {
    .modal-content {
      width: 80% !important;;
      max-width: 1200px !important;
      height:700px !important;
    }
  }
</style>
<!-- レイアウト　css -->
<style>
  .modal-content {
  background-color: #fff;
  margin: 5% auto;
  padding: 2rem;
  width: 90%;
  max-width: 800px;
  border-radius: 10px;
  }

    .modal-title {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 1.5rem;
    }

  .modal-section {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
  }

  .modal-label {
    font-weight: bold;
    margin-bottom: 0.3rem;
    width: 20%;
    font-size:2rem !important;
  }

  .modal-title-content {
    font-size: 1rem;
    padding-left: 1rem;
  }

  .modal-body-content {
    padding: 1rem;
    border: 1px solid #ccc;
    height: auto;
    max-height: 450px;
    width: 80%;
    overflow-y: auto;
    background-color: #f9f9f9;
    text-align: left; /* ← ★この行を追加 */
    white-space: pre-wrap; /* ← ★改行もきちんと反映 */
  }

  .modal-files a {
    color: blue;
    text-decoration: underline;
    display: inline-block;
    margin-right: 1rem;
    margin-bottom: 0.5rem;
  }
  .file-box-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  max-width: 600px;
  margin-top: 1rem;
}

.file-box {
  width: 30%;
  min-width: 150px;
  background-color: #f5f5f5;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  text-align: center;
}


</style>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const openBtns = document.querySelectorAll('.openModalBtn');

    openBtns.forEach(function (btn) {
      const modalId = btn.dataset.target || btn.getAttribute('for');
      const modal = document.getElementById(modalId);
      const closeBtn = modal?.querySelector('.close');
      const modalContent = modal?.querySelector('.modal-content');

      if (modal && closeBtn && modalContent) {
        btn.addEventListener('click', () => {
          modal.style.display = 'block';
        });

        closeBtn.addEventListener('click', () => {
          modal.style.display = 'none';
        });

        modal.addEventListener('click', (e) => {
          if (e.target === modal) {
            modal.style.display = 'none';
          }
        });

        modalContent.addEventListener('click', (e) => {
          e.stopPropagation();
        });
      }
    });
  });
</script>
