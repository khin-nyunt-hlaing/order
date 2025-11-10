<?php
/**
 * @var \App\Model\Entity\MTerm $mterm
 * @var string $mode
 */
?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MTerm $mTerm
 * @var string $mode // 'add' or 'edit'
 */
?>
<div class="mTerm index content">
    <?= $this->Form->create($mTerm, ['type' => 'file']) ?>

    <div class="titlebox">
        <p1><?= $mode === 'edit' ? '献立期間編集' : '献立期間追加' ?></p1>
        <?= $this->Flash->render() ?>   
    </div>

    <div class="flex-vertical">

        <div class="input-range">
            <div class="label-stack">
                <span>献立期間</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('start_date', ['label' => false, 'id' => 'start_date']) ?>
            <span>〜</span>
            <?= $this->Form->control('end_date', ['label' => false, 'id' => 'end_date']) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>受付開始日</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('entry_start_date', ['label' => false, 'id' => 'entry_start_date']) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>新規締切日</span>
                <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('add_deadline_date', ['label' => false, 'id' => 'add_deadline_date']) ?>
        </div>

        <div class="input-range">
            <div class="label-stack">
                <span>発注受付</span>
            </div>
            <?= $this->Form->control('char_set', [
                'label' => false,
                'id' => 'char_set',
                'readonly' => true,
                'class' => 'readonly-like'
            ]) ?>
        </div>

    </div>

    <div class="mTermBox">
        <?= $this->Form->button(
            $mode === 'edit' ? '更新' : '登録',
            ['class' => 'akabtn-like']
        ) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>"
         class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
    </div>

    <?= $this->Form->end() ?>
</div>

<!-- 既存CSS・JSは変更なしで共通利用 -->
<style>
.input input,
.input select,
.input textarea {
    margin-bottom: 0 !important;
}
.flex-vertical {
    padding-top: 5%;
    padding-left: 5%;
    display: flex;
    flex-direction: column;
    gap: 3rem;
    justify-content: center;
}
.input-range {
    align-items: center;
    display: flex;
    gap: 0.5rem;
}
.label-stack {
    display: flex;
    flex-direction: column;
    font-size: 2rem;
    align-items: center;
    width: 8em;
}
.mTermBox {
    display: flex;
    gap: 20px;
    padding-right: 5%;
    justify-content: flex-end;
    margin-left: auto;
}
.readonly-like {
    background-color: #f5f5f5;
    color: #888;
}
</style>

<!-- JavaScript：開始日→締切の自動計算 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('start_date');
    const deadlineInput = document.getElementById('add_deadline_date');

    startInput.addEventListener('change', function () {
        if (startInput.value) {
            // 例：開始日 + 7日を自動セット（任意）
            const start = new Date(startInput.value);
            const autoDeadline = new Date(start.getTime());
            autoDeadline.setDate(start.getDate() - 14);

            const yyyy = autoDeadline.getFullYear();
            const mm = String(autoDeadline.getMonth() + 1).padStart(2, '0');
            const dd = String(autoDeadline.getDate()).padStart(2, '0');

            deadlineInput.value = `${yyyy}-${mm}-${dd}`;
        } else {
            deadlineInput.value = '';
        }
    });
});
</script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    const entryStart = document.querySelector('#entry_start_date');
    const deadline = document.querySelector('#add_deadline_date');
    const startDate = document.querySelector('#start_date');
    const charSet = document.querySelector('#char_set');

    if (!entryStart || !deadline || !startDate || !charSet) return;

    const parseDate = (val) => {
        const [y, m, d] = val.split('-');
        return new Date(y, m - 1, d, 16, 59); // 16:59固定
    };

    const setCharSet = () => {
        if (!entryStart.value || !deadline.value || !startDate.value) {
            charSet.value = '';
            return;
        }

        const today = new Date();
        const entryDate = parseDate(entryStart.value);
        entryDate.setHours(0, 0, 0, 0); 
        const deadlineDate = parseDate(deadline.value);

        if (today <= entryDate) {
            charSet.value = '準備中';
        } else if (today <= deadlineDate) {
            charSet.value = '受付中';
        } else {
            charSet.value = '受付完';
        }
        charSet.readOnly = true;
        charSet.style.backgroundColor = '#eee';
    };

    // イベント登録
    entryStart.addEventListener('change', setCharSet);
    deadline.addEventListener('change', setCharSet);
    startDate.addEventListener('change', setCharSet);

    setCharSet(); // 初期判定
});
</script>