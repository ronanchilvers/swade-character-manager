function updateEdgeTitleSelectionState(element, isSelected) {
  const card = element.closest('.card');
  if (!card) {
    return;
  }

  card.classList.toggle('card--selected', isSelected);
  const group = card.closest('.edges-group');
  let toggleOn = false;
  if (isSelected) {
    toggleOn = true;
  } else {
    const selectedEdges = group.querySelectorAll('.card--selected');
    toggleOn = 0 < selectedEdges.length;
  }
  group.classList.toggle('edges-group--selected', toggleOn);
}

function updateEdgeCounter(counter) {
  const input = counter.querySelector('.js-edge-input');
  const value = counter.querySelector('.js-edge-value');
  const minus = counter.querySelector(".js-edge-adjust[data-direction='-1']");
  const count = Math.max(0, parseInt(input.value || '0', 10));

  input.value = count;
  value.textContent = count;
  minus.disabled = count === 0;
  updateEdgeTitleSelectionState(counter, count > 0);
}

function updateEdgeToggle(toggle) {
  const input = toggle.querySelector('.js-edge-input');
  const button = toggle.querySelector('.js-edge-select');
  const value = Math.max(0, parseInt(input.value || '0', 10)) > 0 ? 1 : 0;

  input.value = value;
  button.classList.toggle('button--toggle--is-on', value === 1);
  button.setAttribute('aria-pressed', value === 1 ? 'true' : 'false');
  updateEdgeTitleSelectionState(toggle, value === 1);
}

function applyFilters() {
  const rankSelect = document.querySelector('.js-filter-rank');
  const selectedToggle = document.querySelector('.js-filter-selected');
  const rank = rankSelect ? rankSelect.value : '';
  const showSelectedOnly = selectedToggle ? selectedToggle.checked : false;

  document.querySelectorAll('.cards .card').forEach((card) => {
    const cardRank = card.dataset.rank || '';
    const passesRank = rank === '' || cardRank === '' || cardRank === rank;
    const passesSelected = !showSelectedOnly || card.classList.contains('card--selected');
    card.style.display = passesRank && passesSelected ? '' : 'none';
  });

  document.querySelectorAll('.edges-group').forEach((group) => {
    const hasVisible = Array.from(group.querySelectorAll('.cards .card')).some(
      (card) => card.style.display !== 'none',
    );
    group.style.display = hasVisible ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.js-edge-counter').forEach((counter) => {
    updateEdgeCounter(counter);

    counter.querySelectorAll('.js-edge-adjust').forEach((button) => {
      button.addEventListener('click', () => {
        const input = counter.querySelector('.js-edge-input');
        const direction = parseInt(button.dataset.direction || '0', 10);
        const current = Math.max(0, parseInt(input.value || '0', 10));

        input.value = Math.max(0, current + direction);
        updateEdgeCounter(counter);
      });
    });
  });

  document.querySelectorAll('.js-edge-toggle').forEach((toggle) => {
    updateEdgeToggle(toggle);

    const button = toggle.querySelector('.js-edge-select');
    button.addEventListener('click', () => {
      const input = toggle.querySelector('.js-edge-input');
      const current = Math.max(0, parseInt(input.value || '0', 10)) > 0 ? 1 : 0;
      input.value = current === 1 ? 0 : 1;
      updateEdgeToggle(toggle);
    });
  });

  const filterToggle = document.querySelector('.js-filter-selected');
  if (filterToggle) {
    filterToggle.addEventListener('change', applyFilters);
  }

  const rankSelect = document.querySelector('.js-filter-rank');
  if (rankSelect) {
    rankSelect.addEventListener('change', applyFilters);
  }
});
