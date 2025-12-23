<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\App\Model\Entity\MCalendar[] $mCalendars
 */
$nowYear = date('Y');
?>

<div class="mFoodCategories index content">
    <div class="title_box">
        <h2 class="title">協商休日カレンダー編集</h2>
    </div>

    <!-- ===== 入力／登録 ===== -->
    <?= $this->Form->create(null, ['type' => 'post', 'id' => 'calendarForm']) ?>

    <div class="search-box-wrapper">
        <div class="search-box">
            <div class="group-search two-col">

                <!-- 休日(年) -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">休日(年)</div>
                        <?= $this->Form->text('holiday_year', [
                            'value' => $nowYear,
                            'maxlength' => 4
                        ]) ?>
                    </div>
                </div>

                <!-- 休日(月日) -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">休日(月日)</div>
                        <?= $this->Form->text('holiday_mmdd', [
                            'maxlength' => 4,
                            'placeholder' => 'MMDD'
                        ]) ?>
                    </div>
                </div>

                <!-- 休日名称 -->
                <div class="col">
                    <div class="group-item">
                        <div class="group-label-top">休日名称</div>
                        <?= $this->Form->text('holiday_name') ?>
                    </div>
                </div>

                <!-- 登録 -->
                <div class="group-item btn-row">
                    <?= $this->Form->submit('登録', ['class' => 'search-btn']) ?>
                </div>

            </div>
        </div>
    </div>

    <?= $this->Form->end() ?>


    <!-- ===== 一覧／削除（既存仕様） ===== -->
    <?= $this->Form->create(null, ['type' => 'post', 'id' => 'deleteForm']) ?>

    <div class="table-wrapper" style="height:400px; overflow-y:scroll; border:1.5px solid #ccc;">
        <table class="styled-table">
            <thead>
                <tr>
                    <th class="col-1">選択</th>
                    <th class="col-2">日付</th>
                    <th class="col-3">曜日</th>
                    <th class="col-4">休日名称</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mCalendars as $calendar): ?>
                <tr class="selectable-row"
                    data-date="<?= h($calendar->calendar_date) ?>"
                    data-name="<?= h($calendar->holiday_name) ?>"
                >
                    <td class="col-1" onclick="event.stopPropagation();">
                        <?= $this->Form->checkbox("select.{$calendar->calendar_date}", [
                            'label' => false
                        ]) ?>
                    </td>
                    <td class="col-2"><?= h($calendar->calendar_date) ?></td>
                    <td class="col-3"><?= h($calendar->weekday) ?></td>
                    <td class="col-4"><?= h($calendar->holiday_name) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <br>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    <!-- 左側の操作ボタン -->
    <div>
        <!-- <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?> -->
        <!-- <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?> -->
        <?= $this->Form->button('削除', [
            'name' => 'action',
            'value' => 'delete',
            'onclick' => 'return checkBeforeDelete();'
        ]) ?>
    </div>
   
    <?= $this->Form->end() ?>

    <div style="margin-top: 20px;">
        <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
    </div>
</div>

<script>
function checkBeforeDelete() {
    const checked = document.querySelectorAll('input[name^="select["]:checked');
    const count = checked.length;
    if (count === 0) return true;
    return confirm(`${count}件選択されています。\n本当に削除しますか？`);
}

// 機能3：一覧クリック → 入力欄セット
document.querySelectorAll('.selectable-row').forEach(row => {
    row.addEventListener('click', () => {
        const date = row.dataset.date; // YYYY-MM-DD
        const name = row.dataset.name;

        if (!date) return;

        document.querySelector('[name="holiday_year"]').value = date.substring(0, 4);
        document.querySelector('[name="holiday_mmdd"]').value =
            date.substring(5, 7) + date.substring(8, 10);
        document.querySelector('[name="holiday_name"]').value = name;
    });
});
</script>
<style>
    .col-1{
        max-width: 80px;          /* ①狭め固定 */
        text-align: center;    /* センター寄せ */
    }
    .col-2{
        max-width: 100px;          /* ①狭め固定 */
        word-break: break-word;    /* 単語途中でも折返し */
        white-space: normal;       /* 通常改行を許可 */
    }
    .col-3{
        max-width: 500px;          /* ①狭め固定 */
        white-space: normal;       /* 通常改行を許可 */
        word-break: break-word;    /* 単語途中でも折返し */
        text-align: center;    /* センター寄せ */
    }
    .col-4{
        max-width: 80px;          /* ①狭め固定 */
        white-space: normal;       /* 通常改行を許可 */
        word-break: break-word;    /* 単語途中でも折返し */
        text-align: center;    /* センター寄せ */
    }

    .leftbox{
    margin-right: auto;
    padding:5px;
}
.rightbox{
    margin-left: auto;
    padding:5px;
}

.highlight-row {
    background-color: #D0EBFF;
}

.group-search {
    width: 40%;
    max-width: 1400px;
    min-width: 1000px;
    margin: 0 auto;
    padding: 17px 35px;
    display: flex;
    font-size: 1.6rem;
    font-weight: 700;
}
.group-search.two-col {
    flex-direction: row;
}
.group-label-top {
    text-align: center;
}
.col {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.group-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.group-item input[type="text"] {
    height: 40px;
    width: 100%;
    max-width: 60%;
}
.note {
    margin-left: 4px;
    white-space: nowrap;
}
.btn-row {
    align-items: flex-end;
    padding-top: 30px;
}

.show-deleted-area {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    line-height: normal;
}

.deleted-btn {
    background-color: #49c5b6;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.9rem;
    display: inline-block;
    line-height: normal;
    white-space: nowrap;
}

.deleted-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    cursor: pointer;
}

.deleted-check input[type="checkbox"] {
    margin: 0;
    vertical-align: middle;
}
    .link-edit {
    color: #0000ee;
    text-decoration: underline;
    cursor: pointer;
}

.link-edit:hover {
    color: #551a8b;
}
</style>