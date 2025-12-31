<div class="TDeliOrder index content">
    <?= $this->Form->create($mDelivery, ['type' => 'file','id' =>'TdeliAjax']) ?>

    <?php
        // ★ 配列フィールドをアンロック
        $mTerm = $mTerm ?? null;
        $this->Form->unlockField('quantity'); // quantity[...] 全体を対象
        $this->Form->unlockField('owner_id'); // L1で切り替える hidden/select も念のため
    ?>

    <?= $this->Form->hidden('term_id', ['id' => 'term_id', 'value' => $tDeliOrder->term_id ?? $mDelivery->term_id ?? '']) ?>

    <?php if (($mode ?? '') === 'edit' && !empty($tDeliOrder?->deli_order_id)): ?>
    <?= $this->Form->hidden('deli_order_id', ['value' => (int)$tDeliOrder->deli_order_id]) ?>
    <?php endif; ?>

    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '献立発注更新' : '献立発注登録' ?></p1>
    </div>
    
    <div class="flex-vertical" style="padding-left: 1.5%; padding-top: 3%;">
            <div class="input-range">
                <div class="label-stack">
                    <span>施設名</span>
                </div>

                <?php if (($mode ?? '') === 'add' && !empty($isL1)): ?>
                    <!-- add & L1 のときだけ施設セレクト -->
                    <?= $this->Form->control('facility_id', [
                        'type'    => 'select',
                        'label'   => false,
                        'id'      => 'facility_id',
                        'options' => $facilityOptions, // サービス=2/4のみ抽出済み
                        'empty'   => '施設を選択してください',
                    ]) ?>
                <?php else: ?>
                    <!-- それ以外（edit含む）は従来どおりテキスト表示 -->
                    <?= $this->Form->control('login_user', [
                        'label'    => false,
                        'id'       => 'login_user',
                        'type'     => 'text',
                        'readonly' => true,
                        'value'    => $userName,
                        'style'    => 'background-color: #eee;'
                    ]) ?>
                <?php endif; ?>
                <?= $this->Form->hidden('owner_id', [
                    'id'    => 'owner_id',
                    'value' => $ownerId ?? ''
                ]) ?>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const facilitySelect = document.getElementById('facility_id');
                    const ownerInput = document.getElementById('owner_id');

                    if (facilitySelect && ownerInput) {
                        facilitySelect.addEventListener('change', function () {
                            // 選択された施設IDを hidden の owner_id にコピー
                            ownerInput.value = this.value;
                        });
                    }
                });
                </script>

            </div>

            <div class="input-range">
                <div class="label-stack">
                    <span>献立期間</span>
                </div>
                <?= $this->Form->control('period_text', [
                    'label' => false,
                    'id' => 'period_text',
                    'type' => 'text',
                    'readonly' => true,
                    'value' => $mDelivery->period_text,
                    'style' => 'background-color: #eee; width: 25rem;'
                ]) ?>
                </div>

            <div class="input-range">
            <div class="label-stack">
            <span>新規締切日</span>
            </div>
            <?= $this->Form->control('add_deadline_date', [
                    'label' => false,
                    'id' => 'add_deadline_date',
                    'type' => 'text',
                    'readonly' => true,
                    'value' => $mDelivery->add_deadline_date ?? '',
                    'style' => 'background-color: #eee; width: 12rem;'
                ]) ?>
            <span class="note">正午12時</span>
            <div class="label-stack">
            <span>発注状態</span>
            </div>
            <?= $this->Form->control('order_status_text', [
            'label' => false,
            'id' => 'order_status_text',
            'type' => 'text',
            'readonly' => true,
            'value' => $mDelivery->order_status_text ?? '',
            'style' => 'background-color: #eee; width: 12rem;'
            ]) ?>

            <div class="label-stack">
            <span>確定状態</span>
            </div>
              <?= $this->Form->control('request_status_text', [
                'label' => false,
                'id' => 'request_status_text',
                'type' => 'text',
                'readonly' => true,
                'value' => $mDelivery->request_status_text ?? '',
                'style' => 'background-color: #eee; width: 12rem;'
            ]) ?>
            
            </div>

            <div class="deli-layout">

            <!-- 左：配食登録ラベル -->
            <div class="label-stack">
                <span>配食登録</span>
            </div>

            <!-- 右：テーブル -->
            <div class="deli-table" id="matrixWrap">
                <?= $this->element('TDeliOrder/matrix', [
                    'deliveryItems'  => $deliveryItems,
                    'days'           => $days,
                    'quantityValues' => $quantityValues,
                    'mode'           => $mode,
                    'isL1'           => $isL1 ?? false,
                    'canEdit'        => $canEdit ?? true,
                    'term'           => $mTerm,
                    'ownerId'        => $ownerId ?? null,
                    
                ]) ?>
            </div>

            </div>
            

    <div class="TDeliOrderBox">
        <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', 
                                ['id' => '',
                                 'class' => 'akabtn-like',
                                 'disabled' => !$isActive]) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>"
         class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
        
    </div>

<?= $this->Form->end() ?>
</div>
<style>
    /* 配食登録 行 */
.deli-layout {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-top: 1rem;
}

/* 左：ラベル */
.deli-label {
    width: 6em;          /* 他の label-stack と揃える */
    font-weight: bold;
    padding-top: 0.5rem;
}

/* 右：テーブル */
.deli-table {
    flex: 1;
    min-width: 0;        /* ← これ超重要（テーブル潰れ防止） */
}

    .TDeliOrderBox{
    padding-top:3%;
    display: flex;
    gap: 10px;
    padding-right:5%;
    justify-content: flex-end;
    margin-left: auto; /* これが右寄せのポイント */
    }
    .label-stack{
    width: 6em
    }

    .limited-box table,
    .limited-box th,
    .limited-box td {
        padding: 0 !important;
        margin: 0 !important;
        border-spacing: 0 !important;
        border-collapse: collapse !important;
        vertical-align: top !important;
    }
    .limited-box input[type="text"],
    .limited-box input[type="number"],
    .limited-box input[type="date"],
    .limited-box select {
        all: unset;
        width: 100%;
        height: 100%;
        box-sizing: border-box; /* ← content-box だと枠からはみ出すので修正 */
        border: 1px solid #888;  /* ← これで「入力欄の中にも枠線」が出る */
        padding: 0.2rem;         /* ← テキストが窮屈にならないように */
        border-radius: 0;        /* ← 必要なら角を丸めず */
    }
    .limited-box {
        max-height: 40rem;
        overflow-y: auto;
        border: 1px solid #aaa; /* 少し濃くする */
        padding: 0;
    }
    .DE_box {
        margin: 0;
        border-collapse: collapse;
        width: 100%;
        border: 2px solid #aaa; /* 外枠も少し濃く太く */
    }


    .DE_box td input[type="text"],
    .DE_box td input[type="number"],
    .DE_box td input[type="date"],
    .DE_box td select {
        width: 100%;
        height: 100%;
        border: none;
        padding: 0.5em;
        box-sizing: border-box;
    }
    /* 全体のテーブルセルに共通の設定 */
    .DE_box th,
    .DE_box td {
        padding: 0;               /* 入力との間の余白をなくす */
        border: 1px solid #888;   /* 枠線 */
        text-align: center;       /* 中央揃え */
        vertical-align: middle;   /* 上下中央寄せ */
        height: 2.5em;            /* 高さ */
        line-height: 1.5;         /* テキスト高さ補正 */
        font-size: 1.5rem;          /* テキストサイズ */
    }


    /* 通常のtd（配食名以外） */
    .DE_box td {
        height: 6rem;
    }

    /* 最初のtd（配食名）だけ別スタイルにする */
    .DE_box td:first-child {
        height: auto; /* ← td全体のheightを無効化したい場合 */
            text-align: center;          /* ← 左右中央に */
        vertical-align: middle !important;      /* ← 上下中央に */
        font-weight: bold;
        background-color: #f9f9f9;
        width: 8em;
        font-size: 1.8rem; /* 小さくしたいなら */
    }

    .DE_box input[type="text"] {
        width: 100%;
        box-sizing: border-box;
        padding: 0.3em;
        border: none; /* ← 線を重ねたくないなら */
    }

</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const mode = '<?= h($mode ?? "") ?>';
    const isL1 = <?= !empty($isL1) ? 'true' : 'false' ?>;
    // addmode+サービス１の場合着火
    if (mode !== 'add' || !isL1) return;

    const sel          = document.getElementById('facility_id');
    const ownerHidden  = document.getElementById('owner_id');
    const form         = document.querySelector('form');
    const termId       = '<?= h($tDeliOrder->term_id ?? $termId ?? 0) ?>';
    const csrfInput    = document.querySelector('input[name="_csrfToken"]');
    const csrfToken    = csrfInput ? csrfInput.value : null;

    // 施設選択 → 行列差し替え
        if (sel) {
            sel.addEventListener('change', async () => {
                const facilityId = sel.value;
                const wrap = document.getElementById('matrixWrap');
                const msg  = document.getElementById('limitedMsg');

                if (!facilityId) {
                if (wrap) wrap.innerHTML = '';     // テーブル消す
                if (msg)  msg.style.display = '';  // メッセージ表示（初期表示に戻す）
                return;
                }
                if (msg)  msg.style.display = 'none'; // 施設が選ばれたら隠す

                if (ownerHidden) ownerHidden.value = facilityId;

                const fd = new FormData();

                fd.append('term_id', termId);
                fd.append('facility_id', facilityId);
                document.querySelectorAll('input[name^="quantity["]').forEach(i => fd.append(i.name, i.value ?? ''));
                if (csrfToken) fd.append('_csrfToken', csrfToken);

                try {
                    const res = await fetch('<?= $this->Url->build(['controller'=>'TDeliOrder','action'=>'matrix']) ?>', {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });

                    const html = await res.text();
                    
                if (res.ok) {
                    // 成功時：テーブル領域の置き換え（どちらか片方に統一）
                    const wrap = document.getElementById('matrixWrap');
                    if (wrap) {
                    wrap.innerHTML = html;
                    } else {
                    // エラー時はフラッシュだけ差し替え
                        const flash = document.querySelector('#flash');
                        if (flash) flash.outerHTML = html;
                    }
                } else {
                    // 失敗(422等)：フラッシュだけ返す設計ならここで差し替え
                    const flash = document.querySelector('#flash');
                    if (flash) flash.outerHTML = html;
                }

                } catch (e) {
                console.error(e);
                alert('行列の再描画に失敗しました。');
                }
            });
        }
            // //addメソッドから入力保持
            // document.addEventListener('DOMContentLoaded', function () {
            //         $.post('/items/add',
            //         $('#qtyBox :input').serialize(),
            //         function(res){ console.log(res); },
            //         'json');
            // });
   }); 
</script>
