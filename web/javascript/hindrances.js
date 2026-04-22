// Hindrances javascript

function updateHindranceTitleState(hindrance) {
  const title = hindrance.closest('.card')?.querySelector('.card__title');
  if (!title) {
    return;
  }

  const isSelected = Array.from(hindrance.querySelectorAll('.js-checkbox')).some((checkbox) => checkbox.checked);
  title.closest('.card').classList.toggle('card--selected', isSelected);
}

function updatePointSpend() {
  let points = 4;
  document.querySelectorAll(".js-hindrance .js-checkbox").forEach((checkbox) => {
    if (checkbox.checked) {
      points -= ("minor" == checkbox.value) ? 1 : 2;
    }
  });
  let disableMinor = points < 1 ? true : false;
  let disableMajor = points < 2 ? true : false;
  document.querySelectorAll(".js-hindrance").forEach((hindrance) => {
    updateHindranceTitleState(hindrance);
  });
  document.querySelectorAll(".js-hindrance .js-checkbox:not(:checked)").forEach((checkbox) => {
    if ("major" == checkbox.value) {
      checkbox.disabled = disableMajor;
    } else {
      checkbox.disabled = disableMinor;
    }
  });
  document.querySelectorAll(".js-budget").forEach((el) => {
    el.textContent = points;
  });
}

function applySelectedFilter(showSelectedOnly) {
  document.querySelectorAll('.cards--hindrances .card').forEach((card) => {
    card.style.display = showSelectedOnly && !card.classList.contains('card--selected') ? 'none' : '';
  });
}

document.addEventListener("DOMContentLoaded", (event) => {
  updatePointSpend();

  const filterToggle = document.querySelector('.js-filter-selected');
  if (filterToggle) {
    filterToggle.addEventListener('change', () => applySelectedFilter(filterToggle.checked));
  }

  document.querySelectorAll(".js-hindrance").forEach((hindrance) => {
    hindrance.addEventListener('click', (e) => {
      let t = e.target;
      if ("LABEL" == t.nodeName) {
        return;
      }
      let was_checked = t.checked;
      hindrance.querySelectorAll('.js-checkbox').forEach((checkbox) => {
        checkbox.checked = false;
      });
      t.checked = was_checked;
      updatePointSpend();
      if (filterToggle) applySelectedFilter(filterToggle.checked);
    });
  });

});
