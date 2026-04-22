// Hindrances javascript

function updateHindranceTitleState(hindrance) {
  const title = hindrance.closest('.card')?.querySelector('.card__title');
  if (!title) {
    return;
  }

  const isSelected = Array.from(hindrance.querySelectorAll('.js-checkbox')).some((checkbox) => checkbox.checked);
  title.closest('.card').classList.toggle('card--selected', isSelected);
}

function syncHindranceSelectionState() {
  document.querySelectorAll(".js-hindrance").forEach((hindrance) => {
    updateHindranceTitleState(hindrance);
  });
}

function applySelectedFilter(showSelectedOnly) {
  document.querySelectorAll('.cards--hindrances .card').forEach((card) => {
    card.style.display = showSelectedOnly && !card.classList.contains('card--selected') ? 'none' : '';
  });
}

document.addEventListener("DOMContentLoaded", () => {
  syncHindranceSelectionState();

  const filterToggle = document.querySelector('.js-filter-selected');
  if (filterToggle) {
    filterToggle.addEventListener('change', () => applySelectedFilter(filterToggle.checked));
  }

  document.querySelectorAll(".js-hindrance").forEach((hindrance) => {
    hindrance.addEventListener('click', (e) => {
      let t = e.target;
      if ("LABEL" == t.nodeName || "INPUT" != t.nodeName) {
        return;
      }
      let was_checked = t.checked;
      hindrance.querySelectorAll('.js-checkbox').forEach((checkbox) => {
        checkbox.checked = false;
      });
      t.checked = was_checked;
      syncHindranceSelectionState();
      if (filterToggle) applySelectedFilter(filterToggle.checked);
    });
  });

});
