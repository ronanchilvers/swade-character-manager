(() => {
  const sheet = document.querySelector('[data-sheet-root]');
  if (!sheet) {
    return;
  }

  const hash = sheet.dataset.characterHash;
  const statusEl = sheet.querySelector('[data-sheet-status]');

  function setStatus(text, modifier) {
    if (!statusEl) {
      return;
    }
    statusEl.textContent = text;
    statusEl.className = 'sheet__status';
    if (modifier) {
      statusEl.classList.add('sheet__status--' + modifier);
    }
  }

  function flashSaved() {
    setStatus('Saved', 'ok');
    clearTimeout(flashSaved._t);
    flashSaved._t = setTimeout(() => setStatus(''), 1500);
  }

  function flashError(message) {
    setStatus(message || 'Save failed', 'error');
  }

  function debounce(fn, wait) {
    let timer = null;
    return function debounced(...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(null, args), wait);
    };
  }

  async function post(section, body) {
    setStatus('Saving…', 'pending');
    try {
      const res = await fetch('/characters/sheet/' + hash + '/' + section, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(body),
      });
      if (!res.ok) {
        flashError('Save failed (' + res.status + ')');
        return;
      }
      const data = await res.json().catch(() => ({ ok: true }));
      if (data && data.ok === false) {
        flashError((data.errors && data.errors.join(', ')) || 'Save failed');
        return;
      }
      flashSaved();
    } catch (err) {
      flashError('Network error');
    }
  }

  const saveState   = debounce((partial) => post('state',   partial),       250);
  const saveGear    = debounce((rows)    => post('gear',    { rows }),      400);
  const saveWeapons = debounce((rows)    => post('weapons', { rows }),      400);
  const saveNotes   = debounce((notes)   => post('notes',   { notes }),     600);

  // ---- Wound / fatigue rails ------------------------------------------------

  function wireRail(field) {
    const items = Array.from(sheet.querySelectorAll('.sheet__rail__list__item[data-rail="' + field + '"]'));
    if (items.length === 0) {
      return;
    }

    function getStep(item, index) {
      return parseInt(item.dataset.railStep, 10) || index + 1;
    }

    function applyCount(count) {
      items.forEach((el, index) => {
        const isSelected = getStep(el, index) <= count;
        el.classList.toggle('sheet__rail__list__item--selected', isSelected);
        el.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
      });
    }

    function currentCount() {
      return items.reduce((count, el, index) => {
        if (!el.classList.contains('sheet__rail__list__item--selected')) {
          return count;
        }
        return Math.max(count, getStep(el, index));
      }, 0);
    }

    items.forEach((item, index) => {
      item.setAttribute('role', 'button');
      item.setAttribute('tabindex', '0');
      item.setAttribute('aria-pressed', item.classList.contains('sheet__rail__list__item--selected') ? 'true' : 'false');

      const activate = () => {
        const target = getStep(item, index);
        const next = currentCount() === target ? target - 1 : target;
        applyCount(next);
        saveState({ [field]: next });
      };

      item.addEventListener('click', activate);
      item.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          activate();
        }
      });
    });
  }

  function wireBooleanRailItem(field) {
    const item = sheet.querySelector('.sheet__rail__list__item[data-rail="' + field + '"]');
    if (!item) {
      return;
    }
    item.setAttribute('role', 'button');
    item.setAttribute('tabindex', '0');
    item.setAttribute('aria-pressed', item.classList.contains('sheet__rail__list__item--selected') ? 'true' : 'false');

    const toggle = () => {
      const next = !item.classList.contains('sheet__rail__list__item--selected');
      item.classList.toggle('sheet__rail__list__item--selected', next);
      item.setAttribute('aria-pressed', next ? 'true' : 'false');
      saveState({ [field]: next ? 1 : 0 });
    };

    item.addEventListener('click', toggle);
    item.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle();
      }
    });
  }

  wireRail('wounds');
  wireRail('fatigue');
  wireBooleanRailItem('incapacitated');

  // ---- Help dialogs --------------------------------------------------------

  function wireHelpDialogs() {
    const dialog = document.createElement('dialog');
    dialog.className = 'sheet__help-dialog';
    dialog.innerHTML = [
      '<form method="dialog" class="sheet__help-dialog__content">',
      '  <div class="sheet__help-dialog__header">',
      '    <h2 class="sheet__help-dialog__title"></h2>',
      '    <button class="sheet__help-dialog__close" type="submit" aria-label="Close help dialog">&times;</button>',
      '  </div>',
      '  <div class="sheet__help-dialog__body">',
      '    <p class="sheet__help-dialog__summary"></p>',
      '    <p class="sheet__help-dialog__effects"></p>',
      '  </div>',
      '</form>',
    ].join('');

    const title = dialog.querySelector('.sheet__help-dialog__title');
    const summary = dialog.querySelector('.sheet__help-dialog__summary');
    const effects = dialog.querySelector('.sheet__help-dialog__effects');

    document.body.appendChild(dialog);

    dialog.addEventListener('click', (e) => {
      if (e.target === dialog) {
        dialog.close();
      }
    });

    sheet.addEventListener('click', (e) => {
      const icon = e.target.closest('.help-icon');
      if (!icon || !sheet.contains(icon)) {
        return;
      }

      e.preventDefault();

      title.textContent = icon.dataset.name || 'Help';
      summary.textContent = icon.dataset.summary || '';
      effects.textContent = icon.dataset.effects || '';
      effects.hidden = !effects.textContent.trim();

      if (typeof dialog.showModal === 'function') {
        dialog.showModal();
      } else {
        window.alert([title.textContent, summary.textContent, effects.textContent].filter(Boolean).join('\n\n'));
      }
    });
  }

  wireHelpDialogs();

  // ---- Bennies / conviction counters ---------------------------------------

  function wireCounter(el) {
    const field = el.dataset.counter;

    function read() {
      return Math.max(0, parseInt(el.textContent, 10) || 0);
    }

    function step(delta) {
      const next = Math.max(0, read() + delta);
      el.textContent = String(next);
      el.setAttribute('aria-valuenow', String(next));
      saveState({ [field]: next });
    }

    const control = sheet.querySelector('[data-counter-control="' + field + '"]');
    if (control) {
      control.querySelectorAll('[data-counter-step]').forEach((button) => {
        button.addEventListener('click', () => {
          step(parseInt(button.dataset.counterStep, 10) || 0);
        });
      });
    }

    el.addEventListener('click', (e) => {
      step(e.shiftKey || e.altKey ? -1 : +1);
    });
    el.addEventListener('contextmenu', (e) => {
      e.preventDefault();
      step(-1);
    });
    el.addEventListener('wheel', (e) => {
      e.preventDefault();
      step(e.deltaY < 0 ? +1 : -1);
    }, { passive: false });
    el.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowUp' || e.key === '+' || e.key === '=') {
        e.preventDefault();
        step(+1);
      } else if (e.key === 'ArrowDown' || e.key === '-' || e.key === '_') {
        e.preventDefault();
        step(-1);
      }
    });
  }

  sheet.querySelectorAll('[data-counter]').forEach(wireCounter);

  // ---- Editable list rows (gear + weapons) ---------------------------------

  function collectRows(container, rowSelector, fields) {
    return Array.from(container.querySelectorAll(rowSelector)).map((row) => {
      const out = {};
      fields.forEach((key) => {
        const cell = row.querySelector('[data-cell="' + key + '"]');
        out[key] = cell ? cell.textContent.trim() : '';
      });
      return out;
    });
  }

  function cloneTemplateRow(type) {
    const tmpl = sheet.querySelector('template[data-row-template="' + type + '"]');
    if (!tmpl || !tmpl.content || !tmpl.content.firstElementChild) {
      return null;
    }
    const nodes = tmpl.content.cloneNode(true);
    // Pick the first meaningful element (tr or li), ignoring text nodes.
    const fragment = document.createDocumentFragment();
    nodes.childNodes.forEach((n) => fragment.appendChild(n.cloneNode(true)));
    return fragment;
  }

  function firstEditableCell(row) {
    return row ? row.querySelector('[contenteditable="true"]') : null;
  }

  function wireEditableList(options) {
    const container = sheet.querySelector(options.containerSelector);
    if (!container) {
      return;
    }

    const save = () => options.save(collectRows(container, options.rowSelector, options.fields));

    function addRow() {
      const fragment = cloneTemplateRow(options.addKey);
      if (!fragment) {
        return null;
      }
      options.insertRow(container, fragment);
      const rows = container.querySelectorAll(options.rowSelector);
      return rows[rows.length - 1] || null;
    }

    container.addEventListener('input', (e) => {
      if (e.target.closest(options.rowSelector)) {
        save();
      }
    });

    container.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' || e.shiftKey) {
        return;
      }
      const cell = e.target.closest('[contenteditable="true"]');
      const row = cell && cell.closest(options.rowSelector);
      if (!cell || !row) {
        return;
      }
      e.preventDefault();

      const rows = Array.from(container.querySelectorAll(options.rowSelector));
      const index = rows.indexOf(row);
      const nextRow = index >= 0 && index < rows.length - 1 ? rows[index + 1] : addRow();
      const target = firstEditableCell(nextRow);
      if (target) {
        target.focus();
      }
    });

    container.addEventListener('click', (e) => {
      if (e.target.matches('.sheet__row__remove')) {
        const row = e.target.closest(options.rowSelector);
        if (row) {
          row.remove();
          save();
        }
        return;
      }
      if (e.target.matches('button[data-add="' + options.addKey + '"]')) {
        const newRow = addRow();
        const target = firstEditableCell(newRow);
        if (target) {
          target.focus();
        }
      }
    });
  }

  wireEditableList({
    containerSelector: '[data-editable="gear"]',
    rowSelector: '[data-gear-row]',
    fields: ['name', 'notes'],
    addKey: 'gear',
    save: saveGear,
    insertRow(container, fragment) {
      const addRow = container.querySelector('.sheet__list__row--add');
      if (addRow) {
        container.insertBefore(fragment, addRow);
      } else {
        container.appendChild(fragment);
      }
    },
  });

  wireEditableList({
    containerSelector: '[data-editable="weapons"]',
    rowSelector: '[data-weapon-row]',
    fields: ['name', 'range', 'damage', 'ap', 'rof', 'weight', 'notes'],
    addKey: 'weapons',
    save: saveWeapons,
    insertRow(container, fragment) {
      const tbody = container.querySelector('tbody');
      if (tbody) {
        tbody.appendChild(fragment);
      }
    },
  });

  // ---- Notes textarea ------------------------------------------------------

  const notesEl = sheet.querySelector('[data-notes]');
  if (notesEl) {
    const autosize = () => {
      notesEl.style.height = 'auto';
      notesEl.style.height = notesEl.scrollHeight + 'px';
    };
    notesEl.addEventListener('input', () => {
      autosize();
      saveNotes(notesEl.value);
    });
    autosize();
  }
})();
