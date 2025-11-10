<div class="MCalendar index content">
<?= $this->Form->create(null, ['type' => 'file']) ?>
<h1>カレンダー取込</h1>



    <?= $this->Form->file('calendar_file') ?>
    <br><br>
    
    
    <div class="MCalendarBox">
    <div class="buttons"><?= $this->Form->button('取込',['class' => 'akabtn-like']) ?></div>
    <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], ['class' => 'aobtn-like']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>
<style>
.buttons{
    display: inline-block;
    
}
.MCalendarBox{
        display: flex;
        gap: 20px;
        padding-right:5%;
        justify-content: flex-end;
        margin-left: auto; /* これが右寄せのポイント */
    }
</style>