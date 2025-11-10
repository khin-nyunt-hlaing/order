<div class="mDeliveryPattern index content">
<?= $this->Form->create($mDeliveryPattern, ['type' => 'file']) ?>
    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '配食商品パターン編集' : '配食商品パターン登録' ?></p1>
    </div>
    
<div class="flex-vertical" style="gap:2rem;">
            <div class="input-range">
            <div class="label-stack">
            <span style="font-size:1.8rem; width:200px">配食商品パターン名称</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('delivery_pattern_name', [
                'label' => false,
                'id' => 'var1',
                'name' => 'delivery_pattern_name',
                'type' => 'text',
                'required' => true,
                'data-max-units' => 200,
            ]) ?>
                <div id="counter"></div>
            </div>
            
            <div class="input-range">
            <div class="label-stack">
            <span>表示順</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('disp_no', [
                'label' => false,
                'id' => 'disp_no',
                'type' => 'number', // 数値と仮定
                'min' => 0,
                'maxlength' => 18,
                ]) ?>
                <div id="counter"></div>
            </div>

            <?php if ($mode === 'edit'): ?>
                <div class="input-range">
                    <div class="label-stack">
                        <span style="padding-bottom: 16px;">削除</span>
                    </div>
                    <div class="del-flg-checkbox">
                        <?= $this->Form->control('del_flg', [
                            'type' => 'checkbox',
                            'label' => '削除状態にする',
                            'hiddenField' => true,
                            'value' => '1',
                            'checked' => $mDeliveryPattern->del_flg === '1',
                        ]) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="input-range">
            <div class="label-stack">
                <span>配食商品一覧</span>
                <span style="font-size: 1.5rem;">（複数選択可）</span>
            </div>

            <div class="intable-wrapper">
                <table class="intable">
                    <thead>
                        <tr style="background-color: #CCE5FF;">
                            <th>選択</th>
                            <th>商品名</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mDeliveries as $deliveryId => $deliveryName): ?>
                            <tr>
                                <td>
                                    <?= $this->Form->checkbox('selected_deliveries[]', [
                                        'value' => (string)$deliveryId,
                                        'checked' => in_array((string)$deliveryId, array_map('strval', (array)($selectedIds ?? [])), true),
                                        'hiddenField' => false                           // 余計な隠しフィールドを出さない
                                    ]) ?>
                                </td>
                                <td><?= h($deliveryName) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>



        </div>
<div class="mDeliveryPatternBox">
     <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['id' => '', 'class' => 'akabtn-like']) ?>
     <a id="" href="<?= $this->Url->build(['action' => 'index']) ?>"
      class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
     
</div>

<?= $this->Form->end() ?>
</div>

<style>
    .mDeliveryPatternBox{
        display: flex;
        gap: 20px;
        padding-right:5%;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
    }
    .intable-wrapper {
    max-height: 300px;
    height: auto;
    overflow-y: auto;
    display: block;
    border: 1px solid #333;
    margin-top: 1rem;
    width: 50%;
}
</style>
<script>
  const input = document.getElementById('disp_no');

  input.addEventListener('input', () => {
    // 数値を文字列として扱う
    let val = input.value;

    // マイナスや小数点は除外する場合はここで処理
    val = val.replace(/[^0-9]/g, '');

    // 18桁を超えたら切り捨て
    if (val.length > 18) {
      val = val.slice(0, 18);
    }

    input.value = val;
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const input   = document.getElementById('var1');
    const counter = document.getElementById('counter');
    const maxUnits = Number(input.dataset.maxUnits) || 20;

    // 半角=1, 全角=2 としてカウント
    // 半角は ASCII と 半角カナ(FF61-FF9F) を1、それ以外は2
    const unitOf = ch =>
      (/^[\x00-\x7F]$/.test(ch) || (/^[\uFF61-\uFF9F]$/.test(ch))) ? 1 : 2;

    let composing = false; // IME変換中フラグ

    function clampValue(str) {
      let used = 0;
      let out = '';
      for (const ch of str) {               // code point単位で走査
        const u = unitOf(ch);
        if (used + u > maxUnits) break;
        out += ch;
        used += u;
      }
      return { out, used };
    }

    function update() {
      const { out, used } = clampValue(input.value);
      if (out !== input.value) input.value = out;
        // ここはユーザーに残りを見せるだけの表示なので不要ならコメントアウト可
        // if (counter) counter.textContent = `合算: ${used}/${maxUnits}（半角=1, 全角=2）`;
    }

    input.addEventListener('compositionstart', () => { composing = true; });
    input.addEventListener('compositionend',   () => { composing = false; update(); });

    input.addEventListener('input', () => {
      if (composing) return; // IME中は確定後に制御
      update();
    });

    // 初期表示時
    update();
  });
</script>