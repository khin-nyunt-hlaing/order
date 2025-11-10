<?php
/**
 * Paginator element (CakePHP 5対応版 + Đẹp + Chuyên nghiệp)
 *
 * @var \Cake\View\Helper\PaginatorHelper $this->Paginator
 */
?>
<style>
.paginator {
    margin-top: 20px;
    text-align: center;
}

.paginator ul.pagination {
    list-style: none;
    display: inline-block;
    padding: 0;
    margin: 0;
}

.paginator ul.pagination li {
    display: inline-block;
    margin: 0 5px;
    padding: 6px 12px;
    background: #f5f5f5;
    border-radius: 4px;
    color: #333;
}

.paginator ul.pagination li.disabled {
    color: #ccc;
    background: #eee;
}

.paginator ul.pagination li.active {
    background: #ff6b6b;
    color: white;
    font-weight: bold;
}

.paginator p {
    margin-top: 10px;
}
</style>

<div class="paginator">
    <ul class="pagination">
        <!-- 最初 -->
        <?php if ($this->Paginator->hasPrev()): ?>
            <li><?= $this->Paginator->first('<< 最初') ?></li>
            <li><?= $this->Paginator->prev('< 前へ') ?></li>
        <?php else: ?>
            <li class="disabled"><< 最初</li>
            <li class="disabled">< 前へ</li>
        <?php endif; ?>

        <!-- ページ番号 -->
        <?php
        $numbers = $this->Paginator->numbers(['modulus' => 3, 'separator' => ' ']);
        if (!empty($numbers) && is_iterable($numbers)) :
            foreach ($numbers as $number) :
                // 現在のページを強調表示
                if (strpos($number, '<strong>') !== false) :
        ?>
            <li class="active"><?= strip_tags($number, '<strong>') ?></li>
        <?php
                else :
        ?>
            <li><?= $number ?></li>
        <?php
                endif;
            endforeach;
        else :
        ?>
            <li class="active"><?= $numbers ?></li>
        <?php endif; ?>

        <!-- 次へ -->
        <?php if ($this->Paginator->hasNext()): ?>
            <li><?= $this->Paginator->next('次へ >') ?></li>
            <li><?= $this->Paginator->last('最後 >>') ?></li>
        <?php else: ?>
            <li class="disabled">次へ ></li>
            <li class="disabled">最後 >></li>
        <?php endif; ?>
    </ul>

    <p><?= $this->Paginator->counter('ページ {{page}} / {{pages}}') ?></p>
</div>
