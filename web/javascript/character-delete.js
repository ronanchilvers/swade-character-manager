(() => {
  function setSubmitState(dialog) {
    const input = dialog.querySelector('[data-character-delete-input]');
    const submit = dialog.querySelector('[data-character-delete-submit]');
    if (!input || !submit) {
      return;
    }

    submit.disabled = input.value.trim().toLowerCase() !== dialog.dataset.characterName.toLowerCase();
  }

  function resetDialog(dialog) {
    const input = dialog.querySelector('[data-character-delete-input]');
    if (input) {
      input.value = '';
    }
    setSubmitState(dialog);
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-character-delete-dialog]').forEach((dialog) => {
      const hash = dialog.dataset.characterDeleteDialog;
      const open = document.querySelector('[data-character-delete-open="' + hash + '"]');
      const input = dialog.querySelector('[data-character-delete-input]');
      const cancel = dialog.querySelector('[data-character-delete-cancel]');
      const form = dialog.querySelector('form');

      if (open) {
        open.addEventListener('click', () => {
          resetDialog(dialog);
          dialog.showModal();
          if (input) {
            input.focus();
          }
        });
      }

      if (input) {
        input.addEventListener('input', () => setSubmitState(dialog));
      }

      if (cancel) {
        cancel.addEventListener('click', () => dialog.close());
      }

      if (form) {
        form.addEventListener('submit', (event) => {
          if (input && input.value.trim().toLowerCase() === dialog.dataset.characterName.toLowerCase()) {
            return;
          }

          event.preventDefault();
          setSubmitState(dialog);
          if (input) {
            input.focus();
          }
        });
      }

      dialog.addEventListener('close', () => resetDialog(dialog));
    });
  });
})();
