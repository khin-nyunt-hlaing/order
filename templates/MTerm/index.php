<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MTerm> $mTerm
 */
?>
<div class="mterms index content">
    <h3 class="cuttitlebox">献立期間一覧</h3>

    <!-- ▼ 検索フォーム -->
    <div class="search-box-wrapper">
        <div class="term-area">

        <?= $this->Form->create(null, [
            'type' => 'get',
            'url'  => ['action' => 'index'],
            'class' => 'search-form'
        ]) ?>

            <div class="term-grid">

                <div class="term-left">

                    <div class="term-row">
                        <label class="term-label">献立日</label>
                        <?= $this->Form->control('start_from', [
                            'type'=>'date','label'=>false,'class'=>'term-input',
                            'value' => $startFrom ?? ''
                        ]) ?>
                    </div>

                    <div class="term-row">
                        <label class="term-label">締切日</label>
                        <?= $this->Form->control('add_from', [
                            'type'=>'date','label'=>false,'class'=>'term-input',
                            'value' => $addFrom ?? ''
                        ]) ?>
                    </div>

                    <div class="term-row">
                        <label class="term-label">変更締切日</label>
                        <?= $this->Form->control('upd_from', [
                            'type'=>'date','label'=>false,'class'=>'term-input',
                            'value' => $updFrom ?? ''
                        ]) ?>
                    </div>

                    <div class="term-row">
                        <label class="term-label">完了受付を含む</label>
                        <?= $this->Form->checkbox('completed', [
                            'value'=>1,
                            'checked' => ($completed ?? '') === '1'
                        ]) ?>
                    </div>

                </div>

                <div class="term-right">

                    <div class="term-row">
                        <span class="term-tilde">〜</span>
                        <?= $this->Form->control('start_to', [
                            'type'=>'date','label'=>false,'class'=>'term-input',
                            'value' => $startTo ?? ''
                        ]) ?>
                    </div>

                    <div class="term-row">
                        <span class="term-tilde">〜</span>
                        <?= $this->Form->control('add_to', [
                            'type'=>'date','label'=>false,'class'=>'term-input',
                            'value' => $addTo ?? ''
                        ]) ?>
                    </div>

                    <div class="term-row">
                        <span class="term-tilde">〜</span>
                        <?= $this->Form->control('upd_to', [
                            'type'=>'date','label'=>false,'class'=>'term-input',
                            'value' => $updTo ?? ''
                        ]) ?>
                    </div>

                    <div class="term-row"><div></div></div>

                </div>

            </div>

            <div class="term-submit">
                <?= $this->Form->submit('検索', [
                    'class'=>'btn btn-danger'
                ]) ?>
            </div>

        <?= $this->Form->end() ?>

        </div>
    </div>


    <!-- ▼ 一覧フォーム -->
    <?= $this->Form->create(null, ['type'=>'post']) ?>
    <p style="text-align:right">件数 <?= is_countable($MTerm) ? count($MTerm) : 0 ?> 件</p>

    <div class="Extractscrollbox">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>選択</th>
                    <th>献立期間</th>
                    <th>受付開始日</th>
                    <th>新規締切日</th>
                    <th>発注受付</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($MTerm as $row): ?>
            <tr>
                <td>
                    <?= $this->Form->checkbox("select[{$row->term_id}]", [
                        'value' => $row->term_id,
                        'hiddenField' => false,
                        'class' => 'toggle-color'
                    ]) ?>
                </td>

                <td>
                    <a href="javascript:void(0);"
                    class="term-link edit-link"
                    data-id="<?= $row->term_id ?>"
                    data-status="<?= h($row->status_message) ?>">
                        <?= h($row->start_date . " ～ " . $row->end_date) ?>
                    </a>
                </td>
                <td><?= h($row->entry_start_date) ?></td>
                <td><?= h($row->add_deadline_date) ?></td>

                <td>
                    <?= h($row->status_message ?? '-') ?>
                    <input type="hidden" class="row-status" value="<?= $row->status_message ?>">
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
        <div class="buttonlist">
            <?= $this->Form->button('新規',['name'=>'action','value'=>'add']) ?>
            <?= $this->Form->button('削除',[
                'name'=>'action','value'=>'delete',
                'onclick'=>'return checkBeforeDelete();'
            ]) ?>
        </div>

        <div>
            <?= $this->Html->link('戻る',['controller'=>'Mmenus','action'=>'index'],[
                'class'=>'button','style'=>'display:flex;align-items:center;'
            ]) ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>


<!-- ▼ JS & CSS -->
<style>
.term-area {
    width: 1150px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    padding: 20px 25px 10px 25px;
    background: #fff;
}

.term-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    column-gap: 40px;
}

.term-left {
    margin-left: -10px;
}

.term-row {
    display: flex;
    align-items: center;
    margin-bottom: 18px;
}

.term-label {
    width: 150px;
    font-weight: bold;
    margin-left: -20px;
}

.term-input {
    width: 170px !important;
}

.term-tilde {
    margin-right: 30px;
    font-weight: bold;
}

.term-submit {
    text-align: right;
    margin-top: 10px;
}

.term-left input[type="checkbox"] {
    width: 15px;
    height: 15px;
    transform: scale(1.3);
    margin-left: 5px;
    cursor: pointer;
}

.term-link {
    color: #0645AD;
    text-decoration: underline;
}

.term-link:hover {
    opacity: 0.8;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // CakePHP の edit 用 URL
    const baseUrl = "<?= $this->Url->build(['controller' => 'MTerm', 'action' => 'edit', '__ID__']) ?>";

    document.querySelectorAll(".edit-link").forEach(link => {

        link.addEventListener("click", function () {

            const id = this.dataset.id;
            const status = this.dataset.status;
            const url = baseUrl.replace("__ID__", id);

            // ★ 受付中のときだけ確認ダイアログ
            if (status === "受付中") {
                if (!confirm("受付中の献立です。編集しますか？")) {
                    return; // キャンセル → 何もしない
                }
            }

            // OK → 遷移
            window.location.href = url;
        });
    });
});

$(document).ready(function() {
    $('input.toggle-color[type="checkbox"]').on('change', function() {
        let row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            row.addClass('highlight-row');
        } else {
            row.removeClass('highlight-row');
        }
    });
});

function confirmEdit() {

    // チェックされた行を取得
    const checkedList = document.querySelectorAll('input[name^="select"]:checked');

    if (checkedList.length === 0) {
        alert("献立期間が選択されていません。");
        return false;
    }

    if (checkedList.length > 1) {
        alert("更新は1件のみ選択可能です。");
        return false;
    }

    const checked = checkedList[0];

    // ステータス hidden を取得
    const row = checked.closest("tr");
    const status = row.querySelector(".row-status").value;

    // 受付中のみダイアログ
    if (status === "受付中") {
        return confirm("受付中の献立です。編集しますか？");
    }

    return true;
}
</script>
