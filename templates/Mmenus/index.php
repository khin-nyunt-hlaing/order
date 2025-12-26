<h3 class="cuttitlebox" style="font-size:1.4em;">メインメニュー</h3>

<div class="menu-white-area">

  <!-- ===== 親メニュー ===== -->
  <div class="main-menus">
    <?php foreach ($menuTree as $parent => $subs): ?>
      <div class="parent-block">

        <button type="button" class="parent-btn" data-parent="<?= h($parent) ?>">
          <?= h($parent) ?>
        </button>

        <!-- 親の直下 -->
        <div class="submenu-area"></div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- ===== 付加情報 ===== -->
  <div class="menu-info">
    <p class="deadline">
      次回締切日：<?= h($nextDeadline ?? '-') ?>
    </p>
    <p class="menu-week">
      該当献立週：<?= h($menuWeek ?? '-') ?>
    </p>
  </div>

  <!-- ===== お知らせ ===== -->
  <!-- ===== お知らせ ===== -->
<div class="notice-header">
    <span class="notice-title">お知らせ</span>
    <span class="notice-count">件数 <?= h($count) ?> 件</span>
</div>

<?= $this->Form->create(null, ['type' => 'get']) ?>

<div class="scrollbox">
    <table class="styled-table">
        <thead>
            <tr>
                <th style="width:20%;">日付</th>

                <!-- 区分（抽出条件） -->
                <th style="width:20%;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="white-space:nowrap;">区分</span>
                        <?= $this->Form->select('announce_div', $announceDivList, [
                            'empty'   => 'すべて',
                            'value'   => $selectedDiv ?? '',
                            'id'      => 'announceDiv',
                            'onchange'=> 'this.form.submit();',
                            'style'   => '
                                width:100%;
                                height:26px;
                                padding:2px 6px;
                                font-size:12px;
                                margin-bottom:0;
                            '
                        ]) ?>
                    </div>
                </th>

                <th>お知らせ</th>
                <th style="width:10%;">添付</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($count === 0): ?>
                <tr>
                    <td colspan="4" style="text-align:center;">
                        データがありません
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($announces as $a): ?>
                    <tr>
                        <td><?= h($a->announce_start_date) ?></td>
                        <td><?= h($announceDivList[$a->announce_div] ?? '') ?></td>
                        <td><?= h($a->announce_title) ?></td>
                        <td><?= !empty($a->has_file) ? 'あり' : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->Form->end() ?>



</div>


<style>
/* ===== 全体 ===== */
.menu-white-area{
  background:#fff;
  padding:24px;
}

.main-menus{
  display:flex;
  justify-content:center;
  gap:24px;
  margin-bottom:20px;
}

/* ★ 親ブロックは左基準 */
.parent-block{
  display:flex;
  flex-direction:column;
  align-items:flex-start;
}

/* ★ 親ボタンだけ中央 */
.parent-btn{
  width:150px;
  height:32px;
  box-sizing:border-box;              /* ★ 必須 */
  border-radius:6px;
  border:1px solid #90caf9;
  background:#e3f2fd;
  font-size:12px;
  font-weight:700;
  color:#0d47a1;
  text-align:center;
  cursor:pointer;

  /* ★ 通常時にも shadow を確保（透明） */
  box-shadow:0 3px 8px rgba(21,101,192,0);

  transition:
    background .2s ease,
    color .2s ease,
    box-shadow .2s ease;
}

.parent-btn:hover{
  background:#bbdefb;
}

.parent-btn.active{
  background:#1565c0;
  color:#fff;


  box-shadow:0 3px 8px rgba(21,101,192,.3);
}


.submenu-area{
  margin-top:8px;
}


.sub-block{
  display:flex;
  gap:6px;
  margin-bottom:6px;
}


.sub-btn{
  width:140px;
  height:28px;
  border-radius:5px;
  border:1px solid #90caf9;
  background:#e3f2fd;
  font-size:9px;
  font-weight:600;
  color:#0d47a1;
  text-align:center;
  cursor:pointer;
}

/* menu */
.menu-list{
  display:none;
  flex-direction:column;
  text-align:center;
}

.menu-list.show{
  display:flex;
}


.menu-link{
  width:140px;
  height:28px;
  display:flex;
  align-items:center;
  justify-content:center;
  padding-left:0;
  margin-bottom:4px;
  border-radius:5px;
  border:1px solid #cfd8dc;
  background:#fafafa;
  font-size:9px;
  color:#000;
  text-decoration:none;
}

.menu-link:hover{
  background:#e3f2fd;
}

.notice-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:12px 0;
}

.notice-title,
.notice-count{
    font-size:14px;
    font-weight:600;
    color:#555;
}
.menu-info{
    margin: 12px 0 20px 20px;
}

.menu-info p{
    margin: 2px 0;
    font-size: 0.95rem;
}

.menu-info .deadline{
    color: #d32f2f;
    font-size:14px;
    font-weight:600;
}

.menu-info .menu-week{
    color: #d32f2f;
    font-size:14px;
    font-weight:600;
}

.scrollbox {
    max-height: 400px;
    overflow-y: auto;
    position: relative;
}

.styled-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}

</style>
<script>
const BASE_URL = "<?= $this->Url->build('/', ['fullBase' => false]) ?>";
const menuTree = <?= json_encode($menuTree, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script>
document.querySelectorAll('.parent-btn').forEach(btn => {
  btn.type = 'button';

  btn.addEventListener('click', () => {

    // active 切替
    document.querySelectorAll('.parent-btn')
      .forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // submenu 全消去
    document.querySelectorAll('.submenu-area')
      .forEach(a => a.innerHTML = '');

    const area = btn.nextElementSibling;
    const subs = menuTree[btn.dataset.parent];

    if (!subs) return; // 念のため

    Object.entries(subs).forEach(([subName, menus]) => {

      // ===== sub_menu_name が空の場合 =====
      if (!subName) {
        menus.forEach(m => {
          area.insertAdjacentHTML('beforeend', `
            <a class="menu-link"
               href="${BASE_URL}${m.controller}/${m.action}">
              ${m.menu_name}
            </a>
          `);
        });
        return;
      }

      // ===== sub_menu_name がある場合 =====
      const row = document.createElement('div');
      row.className = 'sub-block';

      const subBtn = document.createElement('button');
      subBtn.type = 'button';
      subBtn.className = 'sub-btn';
      subBtn.textContent = subName;

      const list = document.createElement('div');
      list.className = 'menu-list';

      menus.forEach(m => {
        list.insertAdjacentHTML('beforeend', `
          <a class="menu-link"
             href="${BASE_URL}${m.controller}/${m.action}">
            ${m.menu_name}
          </a>
        `);
      });

      subBtn.addEventListener('click', () => {
        document.querySelectorAll('.menu-list')
          .forEach(l => l.classList.remove('show'));
        list.classList.add('show');
      });

      row.appendChild(subBtn);
      row.appendChild(list);
      area.appendChild(row);
    });
  });
});

</script>



