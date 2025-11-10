<dialog id="confirmModal" aria-labelledby="confirmModalTitle">
  <form method="dialog" style="margin:0;">
    <h5 id="confirmModalTitle" style="margin:0 0 .5rem;">通知</h5>
    <div id="confirmModalBody" style="margin-bottom:1rem;"></div>
    <div id="confirmModalFooter" style="display:flex; gap:.5rem; justify-content:flex-end;">
      <!-- JSから閉じるボタンを入れる -->
    </div>
  </form>
  <style>
    #confirmModal { border:none; border-radius:8px; padding:16px 18px; }
    #confirmModal::backdrop { background:rgba(0,0,0,.35); }
  </style>
</dialog>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', () => {
  const holder = document.getElementById('modal-flash');
  if (!holder) return;

  const b64 = holder.dataset.html || '';
  if (!b64) return;

  const bytes   = Uint8Array.from(atob(b64), c => c.charCodeAt(0));
  const decoded = new TextDecoder('utf-8').decode(bytes);

  const dlg    = document.getElementById('confirmModal');
  const bodyEl = document.getElementById('confirmModalBody');
  const footEl = document.getElementById('confirmModalFooter');
  if (!dlg || !bodyEl || !footEl) return;

  // ★ 外側クリックで閉じる（フォーム領域の外なら close）
  const formEl = dlg.querySelector('form');
  if (formEl) {
    dlg.addEventListener('click', (e) => {
      const r = formEl.getBoundingClientRect();
      const inside =
        e.clientX >= r.left && e.clientX <= r.right &&
        e.clientY >= r.top  && e.clientY <= r.bottom;
      if (!inside) {
        e.preventDefault();
        dlg.close('backdrop');
      }
    });
  }

  // （任意）Escでも閉じる
  dlg.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') dlg.close('esc');
  });

  bodyEl.innerHTML = decoded;
  footEl.innerHTML = '<button value="close" autofocus>閉じる</button>';

  if (typeof dlg.showModal === 'function') dlg.showModal();
  else dlg.setAttribute('open', '');
});
<?php $this->Html->scriptEnd(); ?>
