function isActivationKey(event) {
  return event.key === 'Enter' || event.key === ' ';
}

function toggleRailItem(item) {
  const active = item.classList.toggle('sheet__rail__list__item--selected');
  item.setAttribute('aria-pressed', active ? 'true' : 'false');
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.sheet__rail__list__item').forEach((item) => {
    item.setAttribute('role', 'button');
    item.setAttribute('tabindex', '0');
    item.setAttribute('aria-pressed', 'false');

    item.addEventListener('click', () => {
      toggleRailItem(item);
    });

    item.addEventListener('keydown', (event) => {
      if (!isActivationKey(event)) {
        return;
      }

      event.preventDefault();
      toggleRailItem(item);
    });
  });
});
