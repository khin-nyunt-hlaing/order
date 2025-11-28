<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MDelivery> $mDelivery
 * @var int $count
 * @var string|null $deliveryId
 * @var string|null $deliveryName
 * @var bool $includeDeleted
 */

$this->Form->setTemplates([
    'inputContainer' => '{{content}}',
]);
?>

<div class="mDelivery index content">

    <div class="title_box">
        <h2 class="title">献立商品マスタ</h2>
        <?= $this->element('show_deleted_filter') ?>
    </div>

    <?= $this->Form->create(null, ['type' => 'get', 'url' => ['action' => 'index']]) ?>

    <!-- 検索ボックス（枠あり・中身左寄せ） -->
     <div class="search-box-perfect">

        <!-- 検索フィールド全体の中身（IDと名称の入力フィールド群） -->
         <div class="search-inner-fields">
            
            <!-- S-1：献立商品IDの行 (横並び構造を統一) -->
             <div class="line-wrap-id">
                <label class="label-text-id">献立商品ID(完全一致)</label>
                
                <!-- ID入力フィールド -->
                 <div class="input-wrap">
                    <?= $this->Form->text('delivery_id', [
                        'value' => $deliveryId,
                        'class' => 'input-box' // input-box-id から input-box に変更
                    ]) ?>
                </div>
            </div>

            <!-- S-2：商品名称の行 (横並び) -->
            <div class="line-wrap-name">
                <label class="label-text-name">商品名称(部分一致)</label>
                
                <!-- 名称入力フィールド -->
                <div class="input-wrap">
                    <?= $this->Form->text('delivery_name', [
                        'value' => $deliveryName,
                        'class' => 'input-box' // input-box-name から input-box に変更
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- S-3：検索ボタンをフィールドの右隣に縦方向で配置 -->
          <div class="search-button-area">
              <?= $this->Form->button('検索', [
                  'type' => 'submit',
                  'class' => 'search-button akabtn-like' // スタイルをTFoodOrderに合わせるためにクラスを追加
              ]) ?>
          </div>
        
    </div>

    <?= $this->Form->end() ?>
    <?= $this->Form->create(null, ['type' => 'post']) ?>
    <div class="scrollbox">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>選択</th>
                    <th><?= $this->Paginator->sort('delivery_id', '献立商品ID') ?></th>
                    <th><?= $this->Paginator->sort('delivery_name', '商品名称') ?></th>
                    <th>削除</th>
                    <th><?= $this->Paginator->sort('disp_no', '表示順') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mDelivery as $m): ?>
                <tr>
                    <!-- 構文エラーを修正：閉じタグ ?> を追加 -->
                    <td><?= $this->Form->checkbox("select[{$m->delivery_id}]", ['class' => 'row-check']) ?></td>
                    <td><?= h($m->delivery_id) ?></td>
                    <td><?= h($m->delivery_name) ?></td>
                    <td><?= $m->deleted_at ? '削除' : '' ?></td>
                    <td><?= $m->deleted_at ? '999' : ($m->disp_no ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="operation-buttons">
        <div class="leftbox">
            <?= $this->Form->button('新規', ['name' => 'action', 'value' => 'add']) ?>
            <?= $this->Form->button('編集', ['name' => 'action', 'value' => 'edit']) ?>
            <?= $this->Form->button('削除', [
                'name' => 'action',
                'value' => 'delete',
                'onclick' => 'return checkBeforeDelete();'
            ]) ?>
        </div>
        <?= $this->Form->end() ?>

        <div class="rightbox">
            <?= $this->Html->link('戻る', ['controller' => 'Mmenus', 'action' => 'index'], ['class' => 'button']) ?>
        </div>
    </div>

</div>

<style>
/* ------------------ 共通スタイル ------------------ */
.input input, .input select, .input textarea, .input-box {
    margin-bottom: 0 !important;
}
.akabtn-like {
    /* TFoodOrderの「書出し」ボタンのスタイルを流用 */
    background-color: #d9534f;
    color: white;
    border: 1px solid #d43f3a;
    border-radius: 4px;
    cursor: pointer;
    box-shadow: 0 2px 2px rgba(0,0,0,0.1);
    transition: background-color 0.2s;
    text-decoration: none; /* <a>タグの場合に備えて */
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    font-size: 16px;
    height: 35px;
}
.akabtn-like:hover {
    background-color: #c9302c; 
}


/* ------------------ タイトル ------------------ */
.title_box {
    display: flex;
    justify-content: space-between; 
    align-items: flex-start;
    margin-bottom: 5px;
}
.title_box .title { margin-right: 20px; }

/* 件数表示の位置調整 */
.title_box .title2 { 
    margin-top: 5px; 
    font-size: 14px;
}

/* ------------------ 検索ボックス（枠）------------------ */
.search-box-perfect {
    display: flex;
    /* align-items: center; を align-items: flex-end; に変更してボタンを一番下に配置 */
    align-items: flex-end;
    flex-wrap: nowrap;
    gap: 2rem; /* フィールド群とボタンの間隔 */
    padding: 1rem;
    background: #fff;
    border: 1.5px solid #ccc;
    border-radius: 0.4rem;
    flex: 1 1 auto;
    min-width: 0;
    max-width: none;
    margin: 0 auto 20px auto; 
    /* TFoodOrderの検索ボックスのように左寄せにするため、max-widthを指定 */
    max-width: 600px;
    margin-left: 0; 
}

/* 検索フィールド全体の中身（IDと名称の入力フィールド群） */
.search-inner-fields {
    display: flex;
    flex-direction: column; /* 要素を縦に並べる */
    gap: 15px; /* 行間の調整 */
    flex-grow: 1; /* 親に合わせて拡張 */
    /* width: 250px; 以前の固定幅を削除 */
    margin-left: 0; /* 以前の 100px のマージンを削除 */
}


/* ------------------ 検索フィールド行 ------------------ */

.line-wrap-id,
.line-wrap-name {
    /* ラベルと入力欄を縦に並べるためのコンテナ */
    display: flex;
    flex-direction: column;
    width: 100%; /* 親に合わせて幅を確保 */
    max-width: 250px; /* 必要に応じて最大幅を設定 */
}

.label-text-id,
.label-text-name {
    display: block;
    font-size: 15px;
    margin-bottom: 5px; /* ラベルと入力ボックスの間隔を調整 */
    text-align: left; /* ラベルを左揃えにする */
    font-weight: normal; /* TFoodOrderに合わせる */
}

/* 入力枠のラッパー（構造の統一のために残す） */
.input-wrap {
    display: flex;
    align-items: center; 
    gap: 15px; 
}

/* テキスト入力枠 (IDと名称で共通の幅) */
.input-box {
    width: 250px; /* ラベル+入力欄として十分な幅 */
    height: 35px; 
    border: 1px solid #aaa;
    padding: 3px 6px;
    font-size: 14px;
    flex-grow: 1; /* 可能な限り幅を広げる */
}

/* ------------------ 検索ボタン ------------------ */

/* 検索ボタンのエリア */
.search-button-area {
    /* ボタンを縦方向の最下部（align-items: flex-end;）に配置するために、
       特別な設定は不要だが、将来的な拡張性を考えて残す。 */
    height: 35px; /* ボタンの高さに合わせる（align-items: flex-end; のため不要だが安全策として）*/
    flex-shrink: 0; /* 縮まないようにする */
}

.search-button {
    width: 80px; 
    height: 35px; 
    font-size: 16px;
    /* akabtn-like クラスのスタイルを適用 */
}
/* 以前の赤系スタイルを akabtn-like に移行 */
/* .search-button:hover {
    background-color: #c9302c; 
} */


/* ------------------ 一覧 ------------------ */
.scrollbox { overflow-x: auto; }
.operation-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}
.leftbox { display: flex; gap: 10px; }
.highlight-row { background-color: #d0ebff; }
</style>
<script>
    // 削除ボタン押下時の確認ダイアログ（元コードの onclick に対応）
    function checkBeforeDelete() {
        // 少なくとも一つのチェックボックスが選択されているか確認
        const checkboxes = document.querySelectorAll('.row-check');
        let checked = false;
        checkboxes.forEach(chk => {
            if (chk.checked) {
                checked = true;
            }
        });

        if (!checked) {
            // アラートの代わりにカスタムモーダル表示を推奨しますが、
            // 既存のコードの動作に近づけるため、メッセージをコンソールに出力します。
            console.error('削除対象が選択されていません。');
            return false; // 送信をキャンセル
        }
        
        // 実際のアプリケーションでは、window.confirm() の代わりにカスタムモーダルを使用してください
        // return window.confirm('選択されたデータを削除します。よろしいですか？');
        
        // 仮に常に true を返すことで処理を続行（**実際の環境ではカスタムモーダルが必要**）
        return true; 
    }
</script>