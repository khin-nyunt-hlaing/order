<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MUserGroup> $mUserGroup
 */
?>
<div class="MUser search content">

    <!-- ===== タイトル ===== -->
    <div class="title_box title-flex">
        <h2 class="title">施設グループ検索</h2>
        <button type="button" class="button close-btn">閉じる</button>
    </div>

    <!-- ===== 検索条件（GET） ===== -->
    <div class="search-box-wrapper">
        <?= $this->Form->create(null, ['type' => 'get', 'id' => 'searchForm']) ?>
        <?php $this->Form->setTemplates(['inputContainer' => '{{content}}']); ?>

        <div class="search-box">

            <div class="group-search two-col">

                <!-- 左列 -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">
                            施設グループコード <span class="note">（完全一致）</span>
                        </div>
                        <?= $this->Form->text('user_group_id', [
                            'value' => $this->request->getQuery('user_group_id')
                        ]) ?>
                    </div>

                    <div class="group-item">
                        <div class="group-label-top">
                            施設コード <span class="note">（完全一致）</span>
                        </div>
                        <?= $this->Form->text('facility_cd', [
                            'value' => $this->request->getQuery('facility_cd')
                        ]) ?>
                    </div>
                </div>

                <!-- 右列 -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">
                            施設グループ名 <span class="note">（部分一致）</span>
                        </div>
                        <?= $this->Form->text('user_group_name', [
                            'value' => $this->request->getQuery('user_group_name')
                        ]) ?>
                    </div>

                    <div class="group-item">
                        <div class="group-label-top">
                            施設名称 <span class="note">（部分一致）</span>
                        </div>
                        <?= $this->Form->text('facility_name', [
                            'value' => $this->request->getQuery('facility_name')
                        ]) ?>
                    </div>

                    <div class="group-item btn-row">
                        <?= $this->Form->submit('検索', ['class' => 'search-btn']) ?>
                    </div>
                </div>

            </div>
        </div>

        <?= $this->Form->end() ?>
    </div>

    <!-- ===== 検索結果 ===== -->
    <div class="input-range">
        <div class="intable-wrapper">
            <table class="intable">
                <thead>
                    <tr style="background-color: #CCE5FF">
                        <th>選択</th>
                        <th>施設グループコード</th>
                        <th>施設グループ名称</th>
                    </tr>
                </thead>
                <tbody id="disp-user">
                    <?php foreach ($viewedUsers as $id => $name): ?>
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    class="group-check"
                                    data-id="<?= h($id) ?>"
                                    data-name="<?= h($name) ?>"
                                >
                            </td>
                            <td><?= h($id) ?></td>
                            <td><?= h($name) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
/* タイトル＋閉じる */
.title-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.close-btn {
    height: 40px;
    padding: 0 20px;
}

/* 検索条件 */
.group-search {
    width: 100%;
    max-width: 1400px;
    min-width: 1000px;
    margin: 0 auto;
    padding: 17px 35px;
    display: flex;
 
}

.group-search.two-col {
    flex-direction: row;
}

.col {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.group-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.group-item input[type="text"] {
    height: 40px;
    width: 100%;
    max-width: 360px;
}

.note {
    margin-left: 4px;
    white-space: nowrap;
}

.btn-row {
    align-items: flex-end;
    padding-top: 22px;
}

.search-btn,
.button {
    height: 40px;
    padding: 0 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.search {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-weight: 600;
}

/* 一覧クリック */
.styled-table tbody tr {
    cursor: pointer;
}
.styled-table tbody tr:hover {
    background-color: #e6f0ff;
}
/* ===== 一覧（閲覧施設と同一スタイル） ===== */
.MUser.search .intable-wrapper {
    max-height: 320px;
    overflow-y: scroll;
    overflow-x: scroll;
    border: 1.5px solid #ccc;   /* ← 枠線 */
    background: #fff;
    width: 80%;
    display: block;
}



/* テーブル本体（styled-table 相当） */
.MUser.search .intable {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

/* セル共通 */
.MUser.search .intable th,
.MUser.search .intable td {
    border: 1px solid #ccc;
    padding: 8px;
    height: 40px;
    font-size: 14px;
    text-align: center;
    vertical-align: middle;
    
}

/* ヘッダー */
.MUser.search .intable thead th {
    background-color: #ffe2e2;
    position: sticky;
    top: 0;
    z-index: 2;
}



/* チェックボックス列 */
.MUser.search .intable th:first-child,
.MUser.search .intable td:first-child {
    width: 80px;
    text-align: center;
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const closeBtn = document.querySelector('.close-btn');

    closeBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const checked = document.querySelectorAll(
            '#disp-user input[type="checkbox"]:checked'
        );

        // ❌ 0件エラー
        if (checked.length === 0) {
            alert('施設グループが選択されていません。');
            return;
        }

        // ❌ 複数選択エラー
        if (checked.length > 1) {
            alert('施設グループは1件のみ選択してください。');
            return;
        }

        // ✅ 正常（1件）
        const cb = checked[0];
        const id = cb.dataset.id;
        const name = cb.dataset.name;

        if (window.opener && !window.opener.closed) {
            window.opener.setUserGroup(id, name);
        }

        // ✅ 正常時のみ閉じる
        window.close();
    });

});
</script>
