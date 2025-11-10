<?= $this->Form->create(null, ['id' => 'filterForm']) ?>
<div class="deleted-filter">
    <span class="filter-label">削除データ</span>
    <?= $this->Form->checkbox('del_flg', [
        'id' => 'del_flg',
        'value' => '1',
        'hiddenField' => true,
        'checked' => $this->request->getData('del_flg') === '1'
    ]) ?>
    <label for="del_flg" class="filter-text">削除データを含める</label>
</div>
<?= $this->Form->end() ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('del_flg');
    if (checkbox) {
        checkbox.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    }
});
</script>
