<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MUser> $mUser
 */?>
<div class="MUser index content">

<div class="title_box">
    <h2 class="title">施設一覧</h2>
    <?= $this->element('show_deleted_filter') ?>
</div>

<div class="search-box-wrapper">
  <?= $this->Form->create(null, ['type' => 'post', 'class' => 'search-form']) ?>

  <div class="search-box">
    <!-- 縦列1：A + B -->
    <div class="search-col">
      <div class="search-field"><?= $this->Form->control('user_id', [
        'label' => '施設番号(完全一致)', 
        'type' => 'text',
        'value' => $userId]) ?>
        </div> 
      <div class="search-field"><?= $this->Form->control('user_name', [
        'label' => '施設名(部分一致)',
        'type' => 'text',
        'value' => $userName]) ?>
        </div> 
    </div>

    <!-- 縦列2：C + D -->
    <div class="search-col">
        <!-- 発注サービス -->
        <!-- C -->
            <div class="search-field">
                    <?= $this->Form->control('use_service_id', [
                        'label' => '発注サービス',
                        'type' => 'select',
                        'options' => $serviceList ?? [],
                        'empty' => 'すべて',
                        'value' => $serviceId ?? '',
                    ]) ?>
            </div>
    
        <!-- 利用状態 -->
        <!-- D -->
            <div class="search-field">
                    <?= $this->Form->control('status', [
                        'label' => '利用状態',
                        'type' => 'select',
                        'options' => $statusList,
                        'empty' => false,
                        'default' => 'すべて',
                    ]) ?>
            </div>
    </div>

    <!-- 縦列3：E + 抽出 -->
    <div class="search-col">
      <div class="search-field"><?= $this->Form->control('user_group_id', [
          'label' => '施設グループ',
          'type' => 'select',
          'options' => $groupList ?? [],
          'empty' => '選択してください',
          'value' => $userGroupId ?? '',
      ]) ?></div> <!-- E -->
      <div class="search-field"><?= $this->Form->submit('抽出') ?></div>
   
  </div>
  <?= $this->Form->end() ?>
</div>
</div>
<p class="count-right">件数 <?= h($count) ?> 件</p>

    <!--フォーム開始：ボタンとチェックボックス送信用-->
    <?= $this->Form->create(null, ['type' => 'post']) ?>

    <div class="Extractscrollbox">
        <table class="styled-table">
        <thead>
            <tr>
                <th>選択</th>
                <th>施設番号</th>
                <th>施設名称</th>
                <th>発注サービス</th>
                <th>状態</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mUser as $user): ?>
                    <?php if (!in_array($user->status, [0, 1, 2])) continue; ?>
            <tr>
                <td>
                    <?= $this->Form->control("select[{$user->user_id}]", [
                           'type' => 'checkbox',
                           'label' => false,
                           'class' => 'toggle-color',
                       ]) ?>
                </td>
                <td><?= h($user->user_id) ?></td>
                <td><?= h($user->user_name) ?></td>
                <td><?= h($user->service->service_name ?? '未設定') ?></td>

        <td>
            <?php if ($user->status == 0): ?>
                準備中
            <?php elseif ($user->status == 1): ?>
                利用中
            <?php elseif ($user->status == 2): ?>
                取引停止    
            <?php endif; ?>
        </td>
                
            </tr>
            <?php endforeach; ?>
                    
        </tbody>
    </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">

    <!-- 操作ボタン -->
    <div class="leftbox">
    <?= $this->Form->button('追加', ['name' => 'action', 'value' => 'add']) ?>
    <?= $this->Form->button('更新', ['name' => 'action', 'value' => 'edit']) ?>
    <?= $this->Form->button('削除', [
            'name' => 'action',
            'value' => 'delete',
            'onclick' => 'return checkBeforeDelete();'
        ]) ?>   
    </div>
    <!--戻るリンク -->
    <div class="rightbox">
        <?= $this->Html->link('戻る', ['controller' => 'Mmenus','Mmenu' => 'index'], ['class' => 'button',
        'style'=>'display: flex; align-items: center;']) ?>
    </div>
    </div>
    <!--フォーム終了 -->
    <?= $this->Form->end() ?>
</div>


<style>
    
    .MUser.index.content{
        max-height:100%;
    }
    .inbox{
        margin-bottom: 0;
        
    }
    .intable {
    width: 95%;              /* 幅90%で調整 */
    border-collapse: collapse;
}
    td, th {
        border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
    height: 40px; /* 行の高さ固定 */
}
.intable th {
    background-color: #f2f2f2;
}

.search-box {
  display: flex;
  gap: 2rem;
  width: 100%;
  border: 1.5px solid #ccc;
  border-radius: 0.5rem;
  background: #fff;
  box-sizing: border-box;
  justify-content: space-between;

  align-items: flex-start; /* ← 高さ揃えず、幅を効かせる */
}


.search-col {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  width: 40%;
}

.search-box-wrapper {
  display: flex;
  justify-content: center;
  width: 100%;
  height:20%;
  flex-direction: column;
}

.search-field {
  width: 100%;
}


</style>

<script>
    $(document).ready(function() {
        $('input[type="checkbox"]').on('change', function() {
            let row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                row.addClass('highlight-row');
            } else {
                row.removeClass('highlight-row');
            }
        });
    });

    function checkBeforeDelete() {
    const checked = document.querySelectorAll('input[name^="select["]:checked');
    const count = checked.length;

    if (count === 0) {
        
        return true; // フォームは送信する
    }

    return confirm(`${count}件選択されています。\n本当に削除しますか？`);
}
</script>
