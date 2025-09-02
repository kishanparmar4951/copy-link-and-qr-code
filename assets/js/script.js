(function () {
  'use strict';

  if (typeof CLQR === 'undefined') {
    return;
  }

  function ensureModal() {
    let backdrop = document.querySelector('.clqr-modal-backdrop');
    if (backdrop) return backdrop;

    backdrop = document.createElement('div');
    backdrop.className = 'clqr-modal-backdrop';
    backdrop.setAttribute('role', 'dialog');
    backdrop.setAttribute('aria-modal', 'true');
    backdrop.innerHTML =
      '<div class="clqr-modal" aria-labelledby="clqr-modal-title">' +
      ' <div class="clqr-modal-header">' +
      '   <div id="clqr-modal-title" class="clqr-modal-title"></div>' +
      '   <button type="button" class="clqr-close" aria-label="' + (CLQR.labels.close || 'Close') + '">×</button>' +
      ' </div>' +
      ' <div class="clqr-qrcode"><div id="clqr-qrcode"></div></div>' +
      '</div>';

    document.body.appendChild(backdrop);

    function closeModal() {
      backdrop.style.display = 'none';
      const code = document.getElementById('clqr-qrcode');
      if (code) code.innerHTML = '';
    }

    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) closeModal();
    });

    backdrop.querySelector('.clqr-close').addEventListener('click', closeModal);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeModal();
    });

    return backdrop;
  }

  function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function (resolve, reject) {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.setAttribute('readonly', '');
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand('copy');
        resolve();
      } catch (err) {
        reject(err);
      } finally {
        document.body.removeChild(ta);
      }
    });
  }

  function init(container) {
    if (!container) return;

    const copyBtn = container.querySelector('.clqr-copy');
    const qrBtn   = container.querySelector('.clqr-qr');
    const url     = container.getAttribute('data-clqr-url') || CLQR.url;

    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        copyToClipboard(url).then(function () {
          const original = copyBtn.textContent;
          copyBtn.textContent = CLQR.labels.copied || 'Copied!';
          setTimeout(function () { copyBtn.textContent = original; }, 1200);
        }).catch(function () {
          alert(CLQR.labels.error || 'Could not copy the link. Please copy it manually.');
        });
      });
    }

    if (qrBtn) {
      qrBtn.addEventListener('click', function () {
        const backdrop = ensureModal();
        backdrop.style.display = 'flex';
        const titleEl = backdrop.querySelector('#clqr-modal-title');
        if (titleEl) {
          titleEl.textContent = CLQR.labels.modal_title || 'Scan to open this page';
        }
        const box = backdrop.querySelector('#clqr-qrcode');
        if (box) {
          box.innerHTML = '';
          // eslint-disable-next-line no-undef
          new QRCode(box, {
            text: url,
            width: 220,
            height: 220,
            correctLevel: QRCode.CorrectLevel.M
          });
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.clqr-wrap').forEach(init);
  });

})();