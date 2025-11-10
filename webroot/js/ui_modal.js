function showModal(message, onConfirm) {
    document.getElementById('modalText').textContent = message;

    const btnBox = document.getElementById('modalButtons');
    btnBox.innerHTML = '';

    if (onConfirm) {
        const okBtn = document.createElement('button');
        okBtn.textContent = 'OK';
        okBtn.onclick = () => {
            closeModal();
            onConfirm(); // ✅ コールバック実行
        };
        btnBox.appendChild(okBtn);
    }

    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = onConfirm ? 'キャンセル' : '閉じる';
    cancelBtn.onclick = closeModal;
    btnBox.appendChild(cancelBtn);

    document.getElementById('customModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

function closeModal() {
    document.getElementById('customModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}
