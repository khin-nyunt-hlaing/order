<?= $this->Form->create(null, ['id' => 'mainForm']) ?>

<?= $this->Form->button('削除', [
    'name' => 'action',
    'value' => 'delete',
    'onclick' => 'return checkBeforeDelete();'
]) ?>

<!-- ★ カスタムモーダル -->
<div id="customModal" style="display:none; position:fixed; top:30%; left:50%; transform:translateX(-50%);
background:#fff; border:1px solid #aaa; padding:20px; z-index:1000; box-shadow: 0 0 10px rgba(0,0,0,0.3);">
    <p id="modalText">削除しますか？</p>
    <button type="submit" name="action" value="delete">はい</button>
    <button onclick="closeModal()">キャンセル</button>
</div>

<!-- ★ 背景ぼかし -->
<div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
background:rgba(0,0,0,0.4); z-index:999;"></div>

<!-- ★ 削除チェックJS -->
<script>
function checkBeforeDelete() {
    const checked = document.querySelectorAll('input[name^="select["]:checked');
    const count = checked.length;

    if (count === 0) {
        return true; // チェック無しならそのまま送信
    }

    document.getElementById('modalText').textContent =
        `${count}件選択されています。削除しますか？`;
    document.getElementById('customModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
    return false; // 送信せずモーダル表示
}

function closeModal() {
    document.getElementById('customModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}
</script>
