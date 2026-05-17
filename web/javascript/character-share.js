(() => {
  async function toggleSharing(hash) {
    const res = await fetch('/characters/share/' + hash, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
    });
    if (!res.ok) {
      return null;
    }
    return res.json().catch(() => null);
  }

  function copyText(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).catch(() => {});
    }
  }

  // ---- Sheet page ----------------------------------------------------------

  const panel = document.querySelector('[data-share-panel]');
  if (panel) {
    const hash = document.querySelector('[data-sheet-root]').dataset.characterHash;

    function renderPanel(enabled, shareUrl) {
      const label = panel.querySelector('.sheet__share-panel__label');
      panel.innerHTML = '';
      if (label) {
        panel.appendChild(label);
      } else {
        const l = document.createElement('span');
        l.className = 'sheet__share-panel__label';
        l.textContent = 'Sharing';
        panel.appendChild(l);
      }

      if (enabled) {
        const status = document.createElement('span');
        status.className = 'sheet__share-panel__status sheet__share-panel__status--on';
        status.textContent = 'On';
        panel.appendChild(status);

        const input = document.createElement('input');
        input.className = 'sheet__share-panel__url';
        input.type = 'text';
        input.readOnly = true;
        input.value = shareUrl;
        input.setAttribute('aria-label', 'Share link');
        input.setAttribute('data-share-url', '');
        panel.appendChild(input);

        const copyBtn = document.createElement('button');
        copyBtn.className = 'button button--small';
        copyBtn.type = 'button';
        copyBtn.setAttribute('data-share-copy', '');
        copyBtn.textContent = 'Copy link';
        copyBtn.addEventListener('click', () => {
          copyText(input.value);
          copyBtn.textContent = 'Copied!';
          setTimeout(() => { copyBtn.textContent = 'Copy link'; }, 1500);
        });
        panel.appendChild(copyBtn);

        const disableBtn = document.createElement('button');
        disableBtn.className = 'button button--small button--danger';
        disableBtn.type = 'button';
        disableBtn.setAttribute('data-share-toggle', hash);
        disableBtn.textContent = 'Disable';
        disableBtn.addEventListener('click', () => doToggle(disableBtn));
        panel.appendChild(disableBtn);
      } else {
        const status = document.createElement('span');
        status.className = 'sheet__share-panel__status sheet__share-panel__status--off';
        status.textContent = 'Off';
        panel.appendChild(status);

        const enableBtn = document.createElement('button');
        enableBtn.className = 'button button--small button--primary';
        enableBtn.type = 'button';
        enableBtn.setAttribute('data-share-toggle', hash);
        enableBtn.textContent = 'Enable sharing';
        enableBtn.addEventListener('click', () => doToggle(enableBtn));
        panel.appendChild(enableBtn);
      }
    }

    async function doToggle(btn) {
      btn.disabled = true;
      const data = await toggleSharing(hash);
      btn.disabled = false;
      if (!data || !data.ok) {
        return;
      }
      const fullUrl = data.url ? window.location.origin + data.url : '';
      renderPanel(data.enabled, fullUrl);
    }

    // Wire initial toggle button
    const initialToggle = panel.querySelector('[data-share-toggle]');
    if (initialToggle) {
      initialToggle.addEventListener('click', () => doToggle(initialToggle));
    }

    // Wire initial copy button
    const initialCopy = panel.querySelector('[data-share-copy]');
    if (initialCopy) {
      const urlInput = panel.querySelector('[data-share-url]');
      initialCopy.addEventListener('click', () => {
        if (urlInput) {
          copyText(urlInput.value);
          initialCopy.textContent = 'Copied!';
          setTimeout(() => { initialCopy.textContent = 'Copy link'; }, 1500);
        }
      });
    }

    return; // Sheet handled; stop here
  }

  // ---- Home page: share toggle on character cards --------------------------

  function wireShareCard(card, hash) {
    const btn = card.querySelector('[data-share-toggle="' + hash + '"]');
    if (!btn) {
      return;
    }

    btn.addEventListener('click', async () => {
      btn.disabled = true;
      const data = await toggleSharing(hash);
      btn.disabled = false;
      if (!data || !data.ok) {
        return;
      }

      if (data.enabled) {
        const path = data.url || '';
        const fullUrl = window.location.origin + path;
        card.innerHTML =
          '<span class="card__share__badge card__share__badge--on">Shared</span>' +
          '<button class="button button--small" type="button"' +
          ' data-share-toggle="' + hash + '"' +
          ' data-share-path="' + path + '">Disable sharing</button>';
        wireShareCard(card, hash);
        copyText(fullUrl);
      } else {
        card.innerHTML =
          '<button class="button button--small" type="button"' +
          ' data-share-toggle="' + hash + '">Enable sharing</button>';
        wireShareCard(card, hash);
      }
    });
  }

  document.querySelectorAll('[data-share-card]').forEach((card) => {
    wireShareCard(card, card.dataset.shareCard);
  });
})();
