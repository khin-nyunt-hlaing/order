<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TDeliOrder $tDeliOrder
 * @var array $query
 * @var array $users
 * @var int $count
 */
?>
<div class="TDeliOrder index content">

<!-- 🔍 抽出フォーム -->
 <?= $this->Form->create(null, ['type' => 'get', 'class' => 'search-form','id' => 'extractForm','valueSources' => $this->request->is('post') ? ['data'] : ['query']]) ?>

<div class="tObox">
    <p class="cuttitlebox">献立発注データ書出し</p>

    <div class="search-box-wrapper">
            
      <div class="search-box">
          <div class="search-row0">


            <div class="search-row1">
              <label class="search-label">受付開始日</label>
              <div class="date-range" style="display: flex; gap:0.5rem;">
                    <?= $this->Form->control('entry_start_date_from', [
                        'label' => false,
                        'type' => 'date',
                        'class' => 'start-date',
                        // 'value' => $this->request->getData('entry_start_date_from')
                    ]) ?>
                    <label>〜</label>
                    <?= $this->Form->control('entry_start_date_to', [
                        'label' => false,
                        'type' => 'date',
                        'class' => 'end-date',
                        // 'value' => $this->request->getData('entry_start_date_to')
                    ]) ?>
                    </div>
              
              <label class="search-label">新規締切日</label>
                <div class="date-range" style="display: flex; gap:0.5rem;">
                <?= $this->Form->control('add_deadline_date_from', [
                      'label' => false,
                      'type' => 'date',
                        'class' => 'start-date',
                      // 'value' => $this->request->getData('add_deadline_date_from')
                  ]) ?>
                  <label>〜</label>
                  <?= $this->Form->control('add_deadline_date_to', [
                      'label' => false,
                      'type' => 'date',
                        'class' => 'end-date',
                      // 'value' => $this->request->getData('add_deadline_date_to')
                  ]) ?>
                </div>
                </div>

            <div class="search-row1">
                  <label class="search-label">登録日</label>
                  <div class="date-range" style="display: flex; gap:0.5rem;">
                  <?= $this->Form->control('create_date_from', [
                      'label' => false,
                      'type' => 'date',
                        'class' => 'start-date',
                      // 'value' => $this->request->getData('create_date_from')
                  ]) ?>
                  <label>〜</label>
                  <?= $this->Form->control('create_date_to', [
                      'label' => false,
                      'type' => 'date',
                        'class' => 'end-date',
                      // 'value' => $this->request->getData('create_date_to')
                  ]) ?>
                  </div>

                  <label class="search-label">変更日</label>
                  <div class="date-range" style="display: flex; gap:0.5rem;">
                  <?= $this->Form->control('update_date_from', [
                      'label' => false,
                      'type' => 'date',
                        'class' => 'start-date',
                      // 'value' => $this->request->getData('update_date_from')
                  ]) ?>
                  <label>〜</label>
                  <?= $this->Form->control('update_date_to', [
                      'label' => false,
                      'type' => 'date',
                        'class' => 'end-date',
                      // 'value' => $this->request->getData('update_date_to')
                  ]) ?>
                  </div>
          </div>

            <div class="search-row1">
              
            <label class="search-label">施設名</label>
                <?= $this->Form->control('user_id', [
                    'label' => false,
                    'type' => 'select',
                    'options' => $users,
                    'empty' => 'すべて',
                    // 'value' => $this->request->getData('user_name')
                ]) ?>


                <div class="search-field" style="gap:1rem;">
                  <label class="search-label">発注状態</label>
                  <!-- <?= $this->Form->control('order_status', [
                      'label' => false,
                      'type' => 'select',
                      'options' => [
                          'registered'     => '登録済',
                          'not_registered' => '未登録',
                      ],
                      'empty' => 'すべて',
                      // 'value' => $this->request->getData('order_status')
                  ]) ?> -->
                  <label>登録済</label>
                </div>
                    <div class="search-field" style="gap:1rem;">
                        <label class="search-label">確定状態</label>
                            <!-- 確定状態 -->
                              <?= $this->Form->control('confirm_status', [
                                  'label' => false,
                                  'type' => 'select',
                                  'options' => [
                                        '0'    => '未確定',
                                        '1'    => '確定'
                                    ],
                                  'empty' => 'すべて',
                                  // 'value' => $this->request->getData('confirm_status')
                              ]) ?>
                      </div>
                  </div>
                  <div class="search-row1">
                  <div class="search-field" style="gap:1rem;">
                      <label class="search-label">施設名称</label>

                      <?= $this->Form->control('facility_name', [
                          'label' => false,
                          'type' => 'text',
                          
                          'value' => $this->request->getQuery('facility_name') // ★ここ
                      ]) ?>

                      <span class="search-note">（部分一致）</span>
                  </div>

                  <div class="search-field check-inline">
                      <label class="search-label">受付完了も表示する</label>

                      <!-- OFF時も値を送るため -->
                      <?= $this->Form->hidden('include_completed', ['value' => '0']) ?>

                      <?= $this->Form->control('include_completed', [
                          'type'    => 'checkbox',
                          'label'   => false,
                          'value'   => '1',
                          'checked' => (bool)$this->request->getQuery('include_completed'),
                      ]) ?>
                  </div>
              </div>

            </div>

            <div class="right-side" style="max-width: 100px; width: 100px;">
            <div class="search-col" style="justify-self: end;">
                    <?= $this->Form->button('検索', ['name' => 'action', 'value' => 'search', 'id'=>'btnSearch']) ?>
            </div>
        </div>
      </div>
  </div>

    <p style="text-align:right; margin-right:5%; color: red; font-weight: bold;">
      <?= h($count) ?>件 
                          <!-- (<?= h($countRecords) ?> レコード)  -->
        が抽出されました</p>

<?= $this->Form->end() ?>

<div class="tObox">

<!-- 📤 書出しフォーム -->
    <span>出力先</span>
<?= $this->Form->create(null, ['type' => 'post', 'url' => ['action' => 'export']]) ?>
    <?php
        // リクエストから値を取得する安全な方法
        $req = $this->request;
        // 検索ボタン(GET)と書出しボタン(POST)の両方の値を統合
        $allParams = array_merge($req->getQueryParams(), (array)$req->getData());
    ?>
    
    <?php // 既存の検索条件を hidden フィールドに正確にセット ?>
    <?= $this->Form->hidden('entry_start_date_from',  ['value' => $allParams['entry_start_date_from'] ?? '']) ?>
    <?= $this->Form->hidden('entry_start_date_to',    ['value' => $allParams['entry_start_date_to'] ?? '']) ?>
    <?= $this->Form->hidden('add_deadline_date_from', ['value' => $allParams['add_deadline_date_from'] ?? '']) ?>
    <?= $this->Form->hidden('add_deadline_date_to',   ['value' => $allParams['add_deadline_date_to'] ?? '']) ?>
    <?= $this->Form->hidden('create_date_from',       ['value' => $allParams['create_date_from'] ?? '']) ?>
    <?= $this->Form->hidden('create_date_to',         ['value' => $allParams['create_date_to'] ?? '']) ?>
    <?= $this->Form->hidden('update_date_from',       ['value' => $allParams['update_date_from'] ?? '']) ?>
    <?= $this->Form->hidden('update_date_to',         ['value' => $allParams['update_date_to'] ?? '']) ?>
    <?= $this->Form->hidden('user_id',                ['value' => $allParams['user_id'] ?? '']) ?>
    <?= $this->Form->hidden('confirm_status',         ['value' => $allParams['confirm_status'] ?? '']) ?>
    
    <?php // 特に重要な「施設名称(部分一致)」を追加 ?>
    <?= $this->Form->hidden('facility_name',          ['value' => $allParams['facility_name'] ?? '']) ?>

    <?php // 受付完了も含むかどうか ?>
    <?= $this->Form->hidden('include_completed',      ['value' => $allParams['include_completed'] ?? '0']) ?>

    <div class="child-box">
        <p style="width: 10%; text-align: right;">ファイル名</p>
        <?= $this->Form->control('export_file_name', [
            'label'=> false,
            'type' => 'text',
        ]) ?>
    </div>

    <div class="TDeliOrderBox">
    <?= $this->Form->button('書出し', [
      'name' => 'action', 'value' => 'export',
      'type'     => 'submit',
      'class'    => 'akabtn-like',
      'disabled' => (empty($count) || $count < 1)
    ]) ?>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="aobtn-like">戻る</a>
    </div>
<?= $this->Form->end() ?>
</div>

<script>
    // ====== 1) ユーティリティ ======
    function validateBeforeExport(event) {
      const form = event.target.closest('form') || event.currentTarget; // submit時はcurrentTargetがform
      const extracted = form?.querySelector('input[name="extracted_flag"]');

      console.log('🟡 validateBeforeExport called');
      console.log('extracted:', extracted);
      console.log('extracted.value:', extracted?.value);

      // if (!extracted || extracted.value !== '1') {
      //   alert('先に「抽出」操作を行ってください');
      //   return false;
      // }
      // return true;
    }

    function onExportFormSubmit(e) {
      if (!validateBeforeExport(e)) e.preventDefault();
    }

    // ====== 2) 各種バインド関数 ======
    function bindFileInput(root = document) {
      const input = root.getElementById ? root.getElementById('fileInput') : document.getElementById('fileInput');
      if (!input) { console.warn('fileInput not found'); return; }

      // 既存リスナ解除（再バインドに強くする）
      input.removeEventListener('change', handleFileChange);
      input.addEventListener('change', handleFileChange);
    }

    function handleFileChange(e) {
      const fullPath = e.currentTarget.value;
      const filePathInput = document.getElementById('filePath');
      if (!filePathInput) { console.warn('filePath input not found'); return; }

      // 主要ブラウザはフルパスではなくファイル名のみ
      const fileName = e.currentTarget.files?.[0]?.name ?? fullPath;
      filePathInput.value = fileName;
    }

    function bindDateRange(root = document) {
      root.querySelectorAll?.('.date-range').forEach(range => {
        const start = range.querySelector('.start-date');
        const end = range.querySelector('.end-date');
        if (!start || !end) return;

        // 二重バインド防止
        start.removeEventListener('change', onStartChange);
        end.removeEventListener('change', onEndChange);

        start.addEventListener('change', onStartChange);
        end.addEventListener('change', onEndChange);

        function onStartChange() { end.min = start.value; }
        function onEndChange() { start.max = end.value; }
      });
    }

    function bindExportForm(root = document) {
      const form = root.querySelector('#export-form, form[data-export-form]');
      if (!form) { console.warn('export form not found'); return; }

      form.removeEventListener('submit', onExportFormSubmit);
      form.addEventListener('submit', onExportFormSubmit);
    }

    function bindAll(root = document) {
      bindFileInput(root);
      bindDateRange(root);
      bindExportForm(root);
    }

    // ====== 3) DOM構築後（＆Turbo）で一括バインド ======
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => bindAll());
    } else {
      bindAll();
    }

    // Turbo/SPA対応（使っていなければこの行は残しても害なし）
    document.addEventListener('turbo:load', () => bindAll());
</script>
<!-- 書出しフォームのテキストを保持 -->
<script>
    document.getElementById('extractForm')?.addEventListener('submit', function () {
      const src = document.getElementById('export-file-name'); // form②のテキスト
      const dst = document.getElementById('extract-export-file-name'); // form①のhidden
      if (src && dst) dst.value = src.value || '';
    });
</script>
  <script>
    document.getElementById('fileInput').addEventListener('change', function() {
    const fullPath = this.value;
    document.getElementById('filePath').value = fullPath;
    });
</script>


<style>
 .search-box {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    padding: 1rem;
    background: #fff;
    border: 1.5px solid #ccc;
    border-radius: 0.4rem;
    justify-content: flex-start;   /* 左寄せ */
    width: 100%;
    box-sizing: border-box;
}


/* 左側ブロック（縦並び） */
.search-row0 {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
}

/* 各行：横並び・左寄せ固定 */
.search-row1 {
    display: flex;
    flex-wrap: nowrap;
    gap: 1rem;
    align-items: center;
    width: 100%;
    justify-content: flex-start;   /* ★ 左寄せ */
    margin: 0;                     /* ★ 中央寄せ解除 */
}

/* 各入力ブロック */
.search-field {
    display: flex;
    align-items: center;
    text-align: center;
    min-width: 180px;
    flex-direction: row;
    margin-left: 0;
}

/* input共通 */
.search-box .input {
    display: block;
    min-width: 120px !important;
    margin: 0 !important;
}

/* ラベル */
.search-label {
    display: inline-block;
    width: 100px;
    text-align: right;
    font-weight: normal;
    white-space: nowrap;
}

/* 右側（検索ボタン） */
.right-side {
    margin-left: auto;
    flex-shrink: 0;
}

.search-col {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
}
/* 横並び・折り返し禁止 */
.check-inline {
    display: flex;

    gap:5.5rem;
    white-space: nowrap;
}

/* CakePHP が生成する div.input を潰す */
.check-inline .input {
    margin: 10 !important;
}

/* label を折らせない */
.check-inline .search-label {
    white-space: nowrap;
    margin: 0;
}
      .child-box {
    display: flex;           /* 中の要素を横に */
    flex-direction: row;
    gap: 10px;
    align-items: center;
    }
    .TDeliOrderBox{
    display: flex;
    gap: 20px;
    padding-right:5%;
    justify-content: flex-end;
    margin-left: auto; /* これが右寄せのポイント */
    }
</style>
