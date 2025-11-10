// ui-actions.js

// チェック前確認（削除前アラート）　◆複数の画面で実装中
function checkBeforeDelete() {
    const checked = document.querySelectorAll('input[name^="select["]:checked');
    const count = checked.length;

    if (count === 0) {
        return true; // フォームは送信する
    }

    return confirm(`${count}件選択されています。\n本当に削除しますか？`);
}
    // 戻るボタン確認処理
document.addEventListener('DOMContentLoaded', () => {
    const backBtn = document.getElementById('ANNOUNCEret');
    if (backBtn) {
        backBtn.addEventListener('click', function (e) {
            const confirmed = confirm('遷移すると入力内容が破棄されます。よろしいですか？');
            if (!confirmed) {
                e.preventDefault();
                backBtn.blur();
            }
        });
    }
});

// 行ハイライト処理（チェックボックス切替で行色変化）◆チェックボックスがある一覧に実装多数
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input.toggle-color[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const row = checkbox.closest('tr');
            if (checkbox.checked) {
                row.classList.add('highlight-row');
            } else {
                row.classList.remove('highlight-row');
            }
        });
    });
});

//解除
function showConfirmModal(selectedIds = []) {
  const modal = document.getElementById('confirmModal');
  const overlay = document.getElementById('modalOverlay');
  if (overlay) overlay.style.display = 'block';
  if (modal) modal.style.display = 'block';

  selectedIds.forEach(id => {
    const checkbox = document.querySelector(`input[name="select[${id}]"]`);
    if (checkbox) checkbox.checked = true;
  });

  // スクロールロック（画面揺れ対策）
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  const modal = document.getElementById('confirmModal');
  const overlay = document.getElementById('modalOverlay');
  if (modal) modal.style.display = 'none';
  if (overlay) overlay.style.display = 'none';

  // スクロール解除
  document.body.style.overflow = '';
}

//食材発注 更新
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category-select');
    const foodSelect = document.getElementById('food-select');

    if (!categorySelect || !foodSelect || typeof window.groupedFoods === 'undefined') {
        return;
    }

    const updateFoodOptions = function () {
        const categoryId = categorySelect.value;
        const foods = window.groupedFoods[categoryId] || [];

        foodSelect.innerHTML = '<option value="">分類を先に選択してください</option>';
        foods.forEach(function (item) {
            const option = document.createElement('option');
            option.value = String(item.id);
            option.textContent = item.label;
            foodSelect.appendChild(option);
        });

        // 初期選択済みの food_id があれば反映
        const selected = foodSelect.dataset.selected;
        if (selected) {
            foodSelect.value = selected;
        }
    };

    categorySelect.addEventListener('change', updateFoodOptions);

    // 🔽 初期状態で既に category_id があれば実行
    if (categorySelect.value !== '') {
        updateFoodOptions();
    }
});
// 初期値があればセットする（連動処理を走らせる）　食材発注　更新
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category-select');
    if (categorySelect && categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }

    const foodSelect = document.getElementById('food-select');
    const initialFoodId = window.initialFoodId;
    if (foodSelect && initialFoodId) {
        foodSelect.value = initialFoodId;
    }
});