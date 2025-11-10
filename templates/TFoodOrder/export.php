<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\TFoodOrder> $tFoodOrder
 * @var array $query
 * @var array $users
 * @var int $dataCount
 */
?>
<div class="TFoodOrder index content">

<!-- 🔍 抽出フォーム -->
 <?= $this->Form->create(null, ['type' => 'get', 'class' => 'search-form','id' => 'extractForm','valueSources' => $this->request->is('post') ? ['data'] : ['query']]) ?>
    <div class="tObox">
        <p class="cuttitlebox">食材発注データ書出し</p>
            
        <div class="search-box-wrapper">
            <div class="search-box">
                <div class="search-row0">

                    <div class="search-row1">
                        <label class="search-label">発注日</label>
                        <div class="date-range" style="display: flex; gap:0.5rem;">
                        <?= $this->Form->control('order_date_from', [
                            'label' => false,
                            'type' => 'date',
                            'class' => 'start-date',
                        ]) ?>

                        <div class="search-field" style="align-self: center; font-weight: bold; width: auto; min-width: unset;">
                            〜
                        </div>

                        <?= $this->Form->control('order_date_to', [
                            'label' => false,
                            'type' => 'date',
                            'class' => 'end-date',
                        ]) ?>
                        </div>

                        <label class="search-label">納品希望日</label>
                        <div class="date-range" style="display: flex; gap:0.5rem;">
                        <?= $this->Form->control('deli_req_date_from', [
                            'label' => false,
                            'type' => 'date',
                            'class' => 'start-date',
                        ]) ?>

                        <div class="search-field" style="align-self: center; font-weight: bold; width: auto; min-width: unset;">
                            〜
                        </div>

                        <?= $this->Form->control('deli_req_date_to', [
                            'label' => false,
                            'type' => 'date',
                            'class' => 'end-date',
                        ]) ?>
                    </div>
                </div>
                    <div class="search-row1">
                        <label class="search-label">発注状態</label>
                        <?= $this->Form->control('order_status', [
                            'label' => false,
                            'type' => 'select',
                            'options' => ['0' => '未確定', '1' => '確定'],
                            'empty' => 'すべて',
                        ]) ?>

                        <label class="search-label">施設名</label>
                        <?= $this->Form->control('user_id', [
                            'label' => false,
                            'type' => 'select',
                            'options' => $users,
                            'empty' => 'すべて',
                        ]) ?>
                    </div>
                </div>

                <div class="search-col" style="grid-column: 3; justify-self: end;">
                    <div class="search-field" style="max-width:120px;">
                        <?= $this->Form->button('抽出') ?>
                    </div>
                </div>
            </div>
        </div>

            <p style="margin: 0 auto 0 auto; text-align: right; color: red; font-weight: bold; width:90%; center:auto;">
                <?= h($dataCount) ?>件が抽出されました
            </p>
        
    <?= $this->Form->end() ?>

    <div class="tObox">
        <span>出力先</span>
    <?= $this->Form->create(null, ['type' => 'post', 'url' => ['action' => 'export']]) ?>
            <?= $this->Form->hidden('order_date_from', ['value' => $this->request->getQuery('order_date_from')]) ?>
            <?= $this->Form->hidden('order_date_to', ['value' => $this->request->getQuery('order_date_to')]) ?>
            <?= $this->Form->hidden('deli_req_date_from', ['value' => $this->request->getQuery('deli_req_date_from')]) ?>
            <?= $this->Form->hidden('deli_req_date_to', ['value' => $this->request->getQuery('deli_req_date_to')]) ?>
            <?= $this->Form->hidden('order_status', ['value' => $this->request->getQuery('order_status')]) ?>
            <?= $this->Form->hidden('order_date', ['value' => $this->request->getQuery('order_date')]) ?>
            <?= $this->Form->hidden('user_id', ['value' => $this->request->getQuery('user_id')]) ?>

        <div class="child-box">
            <p style="width:  10%; text-align: right;">ファイル名</p>
            <?= $this->Form->control('export_file_name', [
                'label'=> false,
                'type' => 'text'
            ]) ?>
        </div>

        <div class="TFoodOrderBox">
            <?= $this->Form->button('書出し', [
                'id' => '',
                'class' => 'akabtn-like',
                'disabled' => (empty($dataCount) || $dataCount < 1)
            ]) ?>
            <a id="" href="<?= $this->Url->build(['action' => 'index']) ?>" class="aobtn-like">戻る</a>
            </div>
        </div>
    <?= $this->Form->end() ?>
</div>
          
<style>
    .search-box {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 1rem;
    background: #fff;
    border: 1.5px solid #ccc;
    border-radius: 0.4rem;
    }

    /* 左側：縦2段のブロック */
    .search-row0 {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 900px;
    }
    .input input, .input select, .input textarea {
        margin-bottom: 0 !important;
    }

        /* 各行：横に並べる（折り返さない） */
        .search-row1 {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        align-items: center;
        width: 100%;
        margin: 0 auto;
    }

        /* 中のフォーム */
        .search-field {
            min-width: 0 !important;
            max-width: 180px !important; /* ← 例：親に合わせて広がる */
            flex: 1;         /* ← 可能であれば柔軟拡張 */
            box-sizing: border-box;
    }
    
    .parent-box {
    width: auto; height: auto;
    min-width: 200px; max-width: 600px;
    min-height: 100px; max-height: 400px; margin-left: 5%;
    display: flex;
    flex-direction: column; /* 子1・子2を縦に */
    gap: 10px;
    }

    .child-box {
    display: flex;           /* 中の要素を横に */
    flex-direction: row;
    gap: 10px;
    align-items: center;
    }
    .TFoodOrderBox{
    display: flex;
    gap: 20px;
    padding-right:5%;
    justify-content: flex-end;
    margin-left: auto; /* これが右寄せのポイント */
    }
      .search-label{
    display: inline-block;
    width: 100px;        /* ← 横幅固定 */
    text-align: right;   /* ← 右詰め */
    font-weight: normal; /* ← 太字解除（必要に応じて） */
  }
    .search-box .input{
      display: block;
      min-width: 120px !important;
      margin: 0 !important;
  }
</style>
  <script>
    document.getElementById('fileInput').addEventListener('change', function() {
    const fullPath = this.value;
    document.getElementById('filePath').value = fullPath;
    });
</script>