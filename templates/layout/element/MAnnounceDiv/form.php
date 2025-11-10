<div class="mAnnounceDiv index content">
<?= $this->Form->create($mAnnounceDiv, ['type' => 'file']) ?>
    <div class="titlebox">
            <p1><?= $mode === 'edit' ? 'お知らせ区分編集' : 'お知らせ区分登録' ?></p1>
            <?= $this->Flash->render() ?>
    </div>
<div class="flex-vertical">
            <div class="input-range">
            <div class="label-stack">
            <span>お知らせ区分番号</span>
            <span style="font-size: 1.5rem;"></span>
            </div>
            <?= $this->Form->control('announce_div', [
                'label' => false,
                'id' => 'announce_div',
                'type' => 'text',
                'required' => true,
                'readonly' => true, // ←常にグレーアウト
                'value' => $mAnnounceDiv->announce_div, // ←addは＋1済、editは既存値
                'style' => ($mode === 'add') ? 'display:none;' : 'background-color: #eee;' // グレー背景
            ]) ?>
            </div>
            <div class="input-range">
            <div class="label-stack">
            <span>お知らせ区分名称</span>
            <span style="font-size: 1.5rem;">(必須)</span>
            </div>
            <?= $this->Form->control('announce_div_name', [
                    'label' => false,
                    'id' => 'announce_div_name',
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
                    //'name' => 'disp_no',
                    'min' => 0,
                    'required' => true,
                ]) ?>
            </div>
        </div>
<div class="mAnnounceDivBox">
     <?= $this->Form->button($mode === 'edit' ? '更新' : '登録', ['id' => '', 'class' => 'akabtn-like']) ?>
     <a id="" href="<?= $this->Url->build(['action' => 'index']) ?>" 
     class="aobtn-like" onclick="return confirm('遷移すると入力内容が破棄されます。よろしいですか？')">戻る</a>
</div>
<?= $this->Form->end() ?>
</div>
<style>
    .mAnnounceDivBox{
        display: flex;
        gap: 20px;
        padding-right:5%;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
    }
</style>