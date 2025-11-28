<div class="mDeliveryPattern index content">
<?= $this->Form->create($mDeliveryPattern, ['type' => 'file']) ?>

    

    <div class="titlebox">
            <p1><?= $mode === 'edit' ? '配食商品パターン編集' : '配食商品パターン登録' ?></p1>
            <?= $this->Flash->render() ?>   
    </div>
    
<div class="flex-vertical" style="gap:2rem;">
            <div class="input-range">
            <div class="label-stack">
            <span style="font-size:1.8rem; width:200px">配食商品パターン名称</span>
            <!-- <span style="font-size: 1.5rem;">(必須)</span> -->
            </div>
            <?= $this->Form->control('delivery_pattern_name', [
                'label' => false,
                'id' => 'delivery_pattern_name',
                'type' => 'text',
                'required' => true,
            ]) ?>
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
                'min' => 0,]) ?>
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
                                    <?= $this->Form->checkbox("selected_deliveries[{$deliveryId}]", ['label' => false]) ?>
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