<div id="delivery-deadline-notice" style="display: none; padding: 1rem; background: #fffbe6; border: 1px solid #ffcc00; margin-bottom: 1rem; border-radius: 0.5rem; gap: 2rem; align-items: center; flex-wrap: wrap;">
    <div><strong>⏰ 現在時刻: </strong><span id="currentTime"></span></div>
    <div><strong>📌 最短納品希望日: </strong><span id="minDeliveryDate"></span></div>
    <div><span id="deadlineWarning" style="color: red; font-weight: bold;"></span></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const now = new Date();
    const hour = now.getHours();
    const minute = now.getMinutes();
    const totalMinutes = hour * 60 + minute;

    const limitStart = 16 * 60 + 30; // 16:30
    const limitEnd = 17 * 60;        // 17:00

    if (totalMinutes >= limitStart && totalMinutes < limitEnd) {
        const box = document.getElementById("delivery-deadline-notice");
        box.style.display = 'flex'; // ← 条件満たしたら枠ごと表示

        document.getElementById("currentTime").textContent = now.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);
        document.getElementById("minDeliveryDate").textContent = minDate.toLocaleDateString();

        const remaining = limitEnd - totalMinutes;
        document.getElementById("deadlineWarning").textContent = `⚠️ 納品希望日を本日から2日後で指定できるのはあと ${remaining} 分です。`;
    }
});
</script>
