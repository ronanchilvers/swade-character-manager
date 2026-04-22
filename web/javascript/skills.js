function applySelectedFilter(showSelectedOnly) {
  document.querySelectorAll('.cards .card--skill.card--non-core').forEach((card) => {
    const select = card.querySelector('select.field__input');
    card.style.display = showSelectedOnly && 0 == select.options[select.selectedIndex].value ? 'none' : '';
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const filterToggle = document.querySelector('.js-filter-selected');
  if (filterToggle) {
    filterToggle.addEventListener('change', () => applySelectedFilter(filterToggle.checked));
  }
});
