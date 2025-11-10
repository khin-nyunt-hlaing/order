<!-- templates/element/common/modal.php -->
<div id="customModal" style="display:none; position:fixed; top:30%; left:50%; transform:translateX(-50%);
background:#fff; border:1px solid #aaa; padding:20px; z-index:1000; box-shadow: 0 0 10px rgba(0,0,0,0.3);">
    <p id="modalText">ここに内容が入ります</p>
    <div id="modalButtons">
        <!-- JavaScriptで動的にボタンを追加 -->
    </div>
</div>

<div id="modalOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
background:rgba(0,0,0,0.4); z-index:999;"></div>

<!-- 🔻 呼び出し方：ボタンの onclick に以下を指定 -->
<!-- コメントで記述 -->
<!-- 
✅ 確認用ポップアップ：
<button onclick="showModal('この内容で保存してよろしいですか？', submitCallback)">保存</button>

✅ 削除確認ポップアップ（件数付き）：
<button onclick="showModal('3件のデータを削除します。よろしいですか？', deleteCallback)">削除</button>

✅ 表示だけ（内容確認モーダル）：
<button onclick="showModal('お知らせ本文の例\n改行も表示可能', null)">内容表示</button>
-->
