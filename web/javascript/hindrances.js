// Hindrances javascript

function updateHindranceTitleState(hindrance) {
  const title = hindrance.closest('.card')?.querySelector('.card__title');
  if (!title) {
    return;
  }

  const isSelected = Array.from(hindrance.querySelectorAll('.js-checkbox')).some((checkbox) => checkbox.checked);
  title.classList.toggle('card__title--selected', isSelected);
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

document.addEventListener("DOMContentLoaded", (event) => {
  updatePointSpend();
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
    });
  });

});
