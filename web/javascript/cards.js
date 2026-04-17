function isActivationKey(event) {
  return event.key === 'Enter' || event.key === ' ';
}

function isCollapsed(title) {
  return title.classList.contains('card__title--collapsed');
}

function updateCollapsedState(title) {
  title.setAttribute('aria-expanded', isCollapsed(title) ? 'false' : 'true');
}

function toggleCollapsedState(title) {
  title.classList.toggle('card__title--collapsed');
  updateCollapsedState(title);
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.card__title--collapsible').forEach((title) => {
    title.setAttribute('role', 'button');
    title.setAttribute('tabindex', '0');
    updateCollapsedState(title);

    title.addEventListener('click', () => {
      toggleCollapsedState(title);
    });

    title.addEventListener('keydown', (event) => {
      if (!isActivationKey(event)) {
        return;
      }

      event.preventDefault();
      toggleCollapsedState(title);
    });
  });
});
