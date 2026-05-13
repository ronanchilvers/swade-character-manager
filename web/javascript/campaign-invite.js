(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const link = document.querySelector('.js-invite-link');
    const trigger = document.querySelector('.js-copy-invite-link');
    if (!link || !trigger) {
      return;
    }

    const originalLabel = trigger.textContent;

    function flash(message) {
      trigger.textContent = message;
      window.setTimeout(() => {
        trigger.textContent = originalLabel;
      }, 2000);
    }

    function fallbackCopy(text) {
      const input = document.createElement('textarea');
      input.value = text;
      input.setAttribute('readonly', '');
      input.style.position = 'absolute';
      input.style.left = '-9999px';
      document.body.appendChild(input);
      input.select();
      try {
        document.execCommand('copy');
        flash('Copied!');
      } catch (err) {
        flash('Copy failed');
      }
      document.body.removeChild(input);
    }

    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      const text = link.textContent.trim();

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text)
          .then(() => flash('Copied!'))
          .catch(() => fallbackCopy(text));
        return;
      }

      fallbackCopy(text);
    });
  });
})();
