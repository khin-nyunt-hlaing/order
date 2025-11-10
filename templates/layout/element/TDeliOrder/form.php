<div class="TDeliOrder index content">
    <?= $this->Form->create($mDelivery, ['type' => 'file']) ?>

    <?php
    // ★ 配列フィールドをアンロック
    $this->Form->unlockField('quantity'); // quantity[...] 全体を対象
    $this->Form->unlockField('owner_id'); // L1で切り替える hidden/select も念のため
    ?>

    <?= $this->Form->hidden('term_id', ['id' => 'term_id', 'value' => $tDeliOrder->term_id ?? $mDelivery->term_id ?? '']) ?>

    <?php if (($mode ?? '') === 'edit' && !empty($tDeliOrder?->deli_order_id)): ?>
    <?= $this->Form->hidden('deli_order_id', ['value' => (int)$tDeliOrder->deli_order_id]) ?>
    <?php endif; ?>

    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '配食発注登録' : '配食商品登録' ?></p1>
            <?= $this->Flash->render() ?>   
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
                    <?= $this->Form->hidden('owner_id', [
                        'id'    => 'owner_id',
                        'value' => $ownerId ?? ''
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
                    'style' => 'background-color: #eee; width: 30rem;'
                ]) ?>
                </div>

            <div class="input-range">
            <div class="label-stack">
            <span>新規締切日</span>
            </div>
            <?= $this->Form->control('upd_deadline_text', [
                    'label' => false,
                    'id' => 'upd_deadline_text',
                    'type' => 'text',
                    'readonly' => true,
                    'value' => $mDelivery->upd_deadline_text ?? '',
                    'style' => 'background-color: #eee;'
                ]) ?>

            <div class="label-stack">
            <span>発注状態</span>
            </div>
            <?= $this->Form->control('order_status_text', [
            'label' => false,
            'id' => 'order_status_text',
            'type' => 'text',
            'readonly' => true,
            'value' => $mDelivery->order_status_text ?? '',
            'style' => 'background-color: #eee;'
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
                'style' => 'background-color: #eee;'
            ]) ?>
            </div>

            <div id="matrixWrap">
                <?= $this->element('TDeliOrder/matrix', [
                    'deliveryItems'  => $deliveryItems,
                    'days'           => $days,
                    'quantityValues' => $quantityValues,
                    'mode'           => $mode,     // 'add' / 'edit'
                    'isL1'           => $isL1 ?? false,   // コントローラで用意したL1判定
                    'canEdit'        => $canEdit ?? true, // 権限で編集可否（任意）
                ]) ?>
            </div>
    <div class="TDeliOrderBox">
        <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['id' => '', 'class' => 'akabtn-like']) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>"
         class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
        
    </div>

<?= $this->Form->end() ?>
</div>
<style>
    .TDeliOrderBox{
    padding-top:3%;
    display: flex;
    gap: 20px;
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
        font-size: 2rem;          /* テキストサイズ */
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
            if (!facilityId) return;
            if (ownerHidden) ownerHidden.value = facilityId;

            const fd = new FormData();
            fd.append('term_id', termId);
            fd.append('facility_id', facilityId);
            if (csrfToken) fd.append('_csrfToken', csrfToken);

            try {
                const res = await fetch('<?= $this->Url->build(['controller'=>'TDeliOrder','action'=>'matrix']) ?>', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const html = await res.text();
                const wrap = document.getElementById('matrixWrap');
                if (wrap) wrap.innerHTML = html;
            } catch (e) {
                console.error(e);
                alert('行列の再描画に失敗しました。');
            }
        });
    }

    // 登録ボタンクリック → Ajax保存
    if (form) {
        form.addEventListener('submit', async function onSubmit(ev) {
            ev.preventDefault();
            try {
                const fd = new FormData(form);
                if (csrfToken && !fd.get('_csrfToken')) fd.append('_csrfToken', csrfToken);

                const res = await fetch(form.action || location.href, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    form.removeEventListener('submit', onSubmit);
                    form.submit();
                    return;
                }
                const data = await res.json();
                if (data.ok) {
                    // ★ メッセージをalert表示
                    alert(data.message || '保存しました');
                    // 一覧へリダイレクト
                    window.location.href = '<?= $this->Url->build(['action' => 'index']) ?>';
                } else {
                    const errors = data.errors || {};
                    alert(errors.global || Object.values(errors)[0] || '保存に失敗しました。');
                }
            } catch (e) {
                console.error(e);
                form.removeEventListener('submit', onSubmit);
                form.submit();
            }
            });
        }
    });
</script>


