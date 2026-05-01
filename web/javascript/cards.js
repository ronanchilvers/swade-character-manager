function isActivationKey(event) {
  return event.key === 'Enter' || event.key === ' ';
}

function makeToggle(trigger, target, collapsedClass) {
  function isCollapsed() {
    return target.classList.contains(collapsedClass);
  }

  function updateCollapsedState() {
    trigger.setAttribute('aria-expanded', isCollapsed() ? 'false' : 'true');
  }

  function toggleCollapsedState() {
    target.classList.toggle(collapsedClass);
    updateCollapsedState();
  }

  trigger.setAttribute('role', 'button');
  trigger.setAttribute('tabindex', '0');
  updateCollapsedState();

  trigger.addEventListener('click', function () {
    toggleCollapsedState();
  });

  trigger.addEventListener('keydown', function (event) {
    if (!isActivationKey(event)) {
      return;
    }

    event.preventDefault();
    toggleCollapsedState();
  });
}

function makeCardToggle(title) {
  makeToggle(title, title, 'card__title--collapsed');
}

function makePanelToggle(panel) {
  var title = panel.querySelector(':scope > .panel__title');

  if (!title) {
    return;
  }

  makeToggle(title, panel, 'panel--collapsed');
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.card__title--collapsible').forEach(makeCardToggle);
  document.querySelectorAll('.panel--collapsible').forEach(makePanelToggle);
});
