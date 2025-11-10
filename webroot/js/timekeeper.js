function showDeliveryToast() {
    const now = new Date();
    const hour = now.getHours();
    const minute = now.getMinutes();
    const minutesUntilDeadline = (17 * 60) - (hour * 60 + minute);

    if (hour === 16 && minute >= 30 && minute < 60) {
        const toast = document.getElementById('delivery-toast');
        const text = document.getElementById('delivery-toast-text');
        if (toast && text) {
            toast.style.display = 'block';
            text.textContent = `納品希望日の本日締切まであと ${minutesUntilDeadline} 分です。`;
        }
    }
}

function setDeliveryMinDate() {
    const deliveryInput = document.getElementById('deli_req_date');
    if (!deliveryInput) return;

    const now = new Date();
    const isAfterFive = now.getHours() >= 17;

    const minDate = new Date();
    minDate.setDate(minDate.getDate() + (isAfterFive ? 3 : 2));

    const yyyy = minDate.getFullYear();
    const mm = String(minDate.getMonth() + 1).padStart(2, '0');
    const dd = String(minDate.getDate()).padStart(2, '0');
    const minStr = `${yyyy}-${mm}-${dd}`;

    deliveryInput.min = minStr;
    deliveryInput.value = minStr;
}

function checkBeforeSave() {
    const deliveryDateInput = document.getElementById('deli_req_date');
    if (!deliveryDateInput || !deliveryDateInput.value) return false;

    const now = new Date();
    const deliveryDate = new Date(deliveryDateInput.value);
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate()); // 時間切り捨て

    const minDate = new Date(today);
    const isAfterFive = now.getHours() >= 17;
    minDate.setDate(minDate.getDate() + (isAfterFive ? 3 : 2));

    // 格納禁止条件：希望日が minDate より前
    if (deliveryDate < minDate) {
        showModal(`納品希望日は${minDate.toLocaleDateString()}以降である必要があります。選択された日付では登録できません。`, null);
        return false;
    }

    return true; // 登録許可
}
