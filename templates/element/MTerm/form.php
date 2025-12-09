<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MTerm $mTerm
 * @var string $mode // 'add' or 'edit'
 */
?>

<div class="mTerm index content">
    <?= $this->Form->create($mTerm, ['type' => 'file']) ?>
    <input type="hidden" id="end_date" name="end_date"
       value="<?= h($mTerm->end_date ? $mTerm->end_date->format('Y-m-d') : '') ?>">
    <div class="titlebox">
        <p1><?= $mode === 'edit' ? '献立期間編集' : '献立期間追加' ?></p1>
        <?= $this->Flash->render() ?>
    </div>

    <table class="term-table">
        <tr>
            <th class="head-empty"></th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th>土</th>
            <th>日</th>
        </tr>

        <tr class="data-row">
            <th class="row-title large-label">献立日</th>

            <td>
            <?php
            // ▼ POST 値（エラー時でも保持させる）
            $postValue = $this->request->getData('start_date') ?? null;

            // ▼ next_start（続けて登録）
            $nextStart = $this->request->getQuery('next_start') ?? null;

            // ▼ 編集モード時の DB 値
            $editValue = !empty($mTerm->start_date)
                ? $mTerm->start_date->format('Y-m-d')
                : null;

            // ▼ 最終的に start_date にセットする値（優先順位が重要）
            // ① POST 値（エラー時でもこれを最優先） 
            if (!empty($postValue)) {
                $value = $postValue;

            // ② 編集モード → DB の値
            } elseif ($mode === 'edit') {
                $value = $editValue;

            // ③ 新規で next_start がある（続けて登録）
            } elseif (!empty($nextStart)) {
                $value = $nextStart;

            // ④ それ以外は空欄
            } else {
                $value = '';
            }
            ?>

            <?= $this->Form->control('start_date', [
                'label' => false,
                'type'  => 'date',
                'class' => 'datebox editable',
                'id'    => 'start_monday',
                'value' => $value
            ]) ?>
            </td>

            <?php foreach (['tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $d): ?>
                <td>
                    <input type="text" id="start_<?= h($d) ?>" class="datebox readonly" readonly>
                </td>
            <?php endforeach; ?>
        </tr>

        <tr class="data-row last-row">
            <th class="row-title large-label">修正締切日</th>

            <?php foreach (['monday','tue','wed','thu','fri','sat','sun'] as $d): ?>
                <td>
                    <?= $this->Form->control("upd_deadline_$d", [
                        'label' => false,
                        'type'  => 'date',
                        'class' => 'datebox editable',
                        'id'    => "upd_$d",
                        'value' => $updDates["upd_deadline_$d"] ?? ''
                    ]) ?>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>
    <hr class="separator">


    <div class="flex-vertical">

        <div class="input-range">
            <div class="label-stack simple-label">
                <span>新規締切日</span>
            </div>
            <?= $this->Form->control('add_deadline_date', [
                'label' => false,
                'id' => 'add_deadline_date',
                'type' => 'date'
                
            ]) ?>
        </div>
        
        <div class="input-range">
            <div class="label-stack simple-label">
                <span>受付開始日</span>
            </div>
            <?= $this->Form->control('entry_start_date', [
                'label' => false,
                'type'  => 'date',
                'id'    => 'entry_start_date'
            ]) ?>
        </div>


        <div class="input-range">
            <div class="label-stack simple-label label-char-set">
                <span>受付状態</span>
            </div>
            <?= $this->Form->control('char_set', [
                'label'    => false,
                'id'       => 'char_set',
                'readonly' => true,
                'class'    => 'readonly-like',
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
<div id="continueModal" class="custom-modal" style="display:none;">
    <div class="custom-modal-content">
        <p class="modal-message">続けて登録しますか？</p>

        <div class="modal-buttons">
            <button id="modalYes" class="modal-btn yes-btn">はい</button>
            <button id="modalNo" class="modal-btn no-btn">いいえ</button>
        </div>
    </div>
</div>

<style>
.input input,
.input select,
.input textarea {
    margin-bottom: 0 !important;
}

.separator {
    border: none;
    height: 3px;
    background-color: #777;
    margin-top: 10px !important;
    margin-bottom: 25px !important;
    width: 100%;
}
.flex-vertical {
    padding-top: 0;
    padding-left: 5%;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    justify-content: flex-start;
    align-items: flex-start;
}

.input-range {
    align-items: center;
    display: flex;
    gap: 0.5rem;
    margin-left:-42px !important;
}

.label-stack {
    display: flex;
    flex-direction: column;
    width: 8em;
    
}
.label-stack.simple-label.label-char-set {
    padding-right: 0px !important;
}
.term-table .row-title {
    width: 120px !important;
    display: table-cell !important;
    text-align: right !important;
    padding-right: 17px !important;
    font-weight: normal !important;
    color: #566877 !important;  
    
}

/* ▼ 下段ラベルも右寄せに統一 */
.label-stack.simple-label {
    width: 120px !important;
    text-align: right !important;
    justify-content: center !important;
    padding-right: 10px !important;
}


.mTermBox {
    display: flex;
    gap: 20px;
    padding-right: 5%;
    justify-content: flex-end;
    margin-top: 30px;
}

.readonly-like {
    background: #f4f4f4;
    color: #555;
}


/* -----------------------------------
 * ▼ テーブル（献立日／修正締切日）
 * ----------------------------------- */
.term-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate !important;
    border-spacing: 0 25px !important;
    margin: -10px 0 0;
}

/* ▼ 曜日を中央寄せ + 境界線削除 */
.term-table tr:first-child th {
    height: 25px !important; 
    text-align: center !important;
    vertical-align: middle !important;
    border: none !important;
}

/* ▼ 曜日下の線も削除 */
.term-table tr:first-child {
    padding: 0 !important;
    margin: 0 !important;
    height: 20px !important;
}


/* ▼ 行間のスペース（献立日と修正締切日の間） */
.data-row {
    height: 40px !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    border-spacing: 0 20px !important; 
}

.term-table .data-row + .data-row {
    margin-top: 0 !important;
    display: table-row;
}

/* ▼ 左側ラベル（献立日・修正締切日） */
.row-title {
    background: #fff !important;
    border: none !important;
    padding-left: 8px !important;
    vertical-align: middle !important;
    font-weight: normal !important;   /* 太字解除 */
    color: #566877 !important;
    font-size: 2rem;
    font-weight: 400;
}

/* ▼ 全セルの高さ統一 */
.term-table th,
.term-table td {
    
    padding: 0 !important;
    margin: 0 !important;
    vertical-align: middle !important;
}

/* ▼ 枠つき input（右側日付欄） */
.datebox {
    width: 100%;
    height: 40px !important;
    border: 1px solid #777 !important;
    box-sizing: border-box !important;
    padding: 0 6px !important;
    background: #fff;
    border-radius: 0 !important;
}

/* ▼ 読取専用 */
.readonly {
    background: #e8e8e8 !important;
    display: flex !important;
    align-items: center !important;
    height: 40px !important;
    padding-left: 6px !important;
    margin: 0 !important;
}

/* ▼ 月曜日 input（CakePHPラッパー対策） */
.term-table .data-row td:first-child .input {
    margin: 0 !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
}

/* ▼ 月曜date input */
.term-table .data-row td:first-child input[type="date"] {
    padding: 0 6px !important;
    margin: 0 !important;
}
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
}

/* 白いポップアップ本体 */
.custom-modal-content {
    background: #ffffff;
    padding: 25px 40px;
    border-radius: 14px;
    text-align: center;
    width: 340px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    font-family: "Hiragino Sans", "Meiryo", sans-serif;
}

/* メッセージ */
.modal-message {
    font-size: 16px;
    margin-bottom: 25px;
    color: #444;
}

/* ボタン配置 */
.modal-buttons {
    display: flex;
    justify-content: center;
    gap: 18px;
}

/* ボタン共通 */
.modal-btn {
    padding: 8px 35px;
    border-radius: 18px;
    font-size: 15px;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

/* OK（茶色ボタン） */
.yes-btn {
    background: #a5672e;
    color: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}

/* キャンセル（ベージュボタン） */
.no-btn {
    background: #f6e3d3;
    color: #603813;
    box-shadow: 0 3px 8px rgba(0,0,0,0.18);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const MODE      = '<?= h($mode) ?>';
    const HOLIDAYS  = <?= json_encode($holidays ?? []); ?>;

    /* =========================================
     * ▼ 新規締切日専用：祝日のみ前倒し
     * ========================================= */
    function adjustBackwardHolidayOnly(d, holidays) {
        while (true) {
            const ymd = d.toISOString().split("T")[0];
            const isHoliday = holidays.includes(ymd);

            if (isHoliday) {
                d.setDate(d.getDate() - 1);
            } else {
                break;
            }
        }
        return d;
    }

    /* =========================================
     * ▼ 修正締切日専用：土日祝前倒し
     * ========================================= */
    function adjustBackwardForUpdDeadline(d, holidays) {
        while (true) {
            const ymd = d.toISOString().split("T")[0];
            const isHoliday = holidays.includes(ymd);
            const isWeekend = d.getDay() === 0 || d.getDay() === 6;

            if (isHoliday || isWeekend) {
                d.setDate(d.getDate() - 1);
            } else {
                break;
            }
        }
        return d;
    }

    /* =========================================
     * ▼ 献立日 → 火曜〜日曜自動計算
     * ========================================= */
    function fillWeek(baseId, prefix) {
        const base = document.getElementById(baseId);
        if (!base) return;

        const updateDates = () => {
            if (!base.value) return;

            const start = new Date(base.value);
            const days = ["tue", "wed", "thu", "fri", "sat", "sun"];

            days.forEach((d, i) => {
                const dt = new Date(start);
                dt.setDate(start.getDate() + (i + 1));

                const yyyy = dt.getFullYear();
                const mm = String(dt.getMonth() + 1).padStart(2, '0');
                const dd = String(dt.getDate()).padStart(2, '0');

                const el = document.getElementById(prefix + d);
                if (el) {
                    el.value = `${yyyy}-${mm}-${dd}`;
                }
            });
        };

        base.addEventListener("change", updateDates);
        updateDates();
    }

    fillWeek("start_monday", "start_");

    /* =========================================
     * ▼ 修正締切日（前週同一曜日 → 土日祝前倒し）
     * ========================================= */
    function setUpdDeadlineWeek(startVal, holidays = []) {
        if (!startVal) return;

        const start = new Date(startVal);

        const baseMonday = new Date(start);
        baseMonday.setDate(start.getDate() - 7);

        const labels = ["monday", "tue", "wed", "thu", "fri", "sat", "sun"];

        labels.forEach((label, i) => {
            let d = new Date(baseMonday);
            d.setDate(baseMonday.getDate() + i);

            d = adjustBackwardForUpdDeadline(d, holidays);

            const yyyy = d.getFullYear();
            const mm   = String(d.getMonth() + 1).padStart(2, '0');
            const dd   = String(d.getDate()).padStart(2, '0');

            const el = document.getElementById("upd_" + label);
            if (el) el.value = `${yyyy}-${mm}-${dd}`;
        });
    }

    /* =========================================
     * ▼ 新規締切日・受付開始日・終了日
     * ========================================= */
    const startInput      = document.getElementById('start_monday');
    const deadlineInput   = document.getElementById('add_deadline_date');
    const entryStartInput = document.getElementById('entry_start_date');
    const endHidden       = document.getElementById('end_date');

    startInput.addEventListener('change', function () {

        if (!startInput.value) return;

        const startDate = new Date(startInput.value);

        /* ---- 新規締切日（14日前 → 祝日のみ前倒し） ---- */
        let d = new Date(startDate);
        d.setDate(startDate.getDate() - 14);
        d = adjustBackwardHolidayOnly(d, HOLIDAYS);
        deadlineInput.value = d.toISOString().split('T')[0];

        /* ---- 受付開始日（42日前） ---- */
        const entry = new Date(startDate);
        entry.setDate(startDate.getDate() - 42);
        entryStartInput.value = entry.toISOString().split('T')[0];

        /* ---- 終了日（＋6日） ---- */
        const end = new Date(startDate);
        end.setDate(startDate.getDate() + 6);
        endHidden.value = end.toISOString().split('T')[0];

        /* ---- 修正締切日設定 ---- */
        setUpdDeadlineWeek(startInput.value, HOLIDAYS);

        setCharSet();
    });

    /* =========================================
     * ▼ 受付状態
     * ========================================= */
    const charSet = document.getElementById('char_set');

    function setCharSet() {
        if (!entryStartInput.value || !deadlineInput.value || !startInput.value) {
            charSet.value = "";
            return;
        }

        const today      = new Date(); today.setHours(0,0,0,0);
        const entryDate  = new Date(entryStartInput.value);
        const deadline   = new Date(deadlineInput.value);

        if (today < entryDate) charSet.value = "準備中";
        else if (today <= deadline) charSet.value = "受付中";
        else charSet.value = "受付完";
    }

    startInput.addEventListener('change', setCharSet);
    entryStartInput.addEventListener('change', setCharSet);
    deadlineInput.addEventListener('change', setCharSet);

    // ▼ 新規モードで start_date が入っている（＝next_startが来ている）ときだけ、自動計算
    if (MODE === 'add' && startInput.value) {
        startInput.dispatchEvent(new Event('change'));
    } else {
        setCharSet();
    }
});


/* =============================================
 * ▼ 続けて登録ポップアップ
 * ============================================= */
document.addEventListener("DOMContentLoaded", () => {

    const isContinue = <?= json_encode($this->request->getQuery('continue') == 1) ?>;

    if (isContinue) {

        const modal   = document.getElementById("continueModal");
        const btnYes  = document.getElementById("modalYes");
        const btnNo   = document.getElementById("modalNo");

        modal.style.display = "flex";

        
        btnYes.addEventListener("click", () => {

           
            const flashes = document.querySelectorAll(".flash, .message, .alert");
            flashes.forEach(el => el.remove());

           
            modal.style.display = "none";
        });

       
        btnNo.addEventListener("click", () => {
            window.location.href = "<?= $this->Url->build(['action' => 'index']) ?>";
        });
    }
});
</script>





