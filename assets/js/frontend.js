/**
 * Frontend JS for Copy Link & QR Code
 * Handles copy-to-clipboard and QR popup (SVG + downloads)
 * Requires: qrcode-generator library (qrcode function)
 */

(function () {
    'use strict';

    /**
     * Create QR SVG using qrcode-generator
     */
    function createQrSvg(text, size, ecl, margin) {
        if (typeof qrcode !== 'function') {
            return '<div class="clqrc-qr-error">QR library not loaded</div>';
        }

        try {
            var qr = qrcode(0, ecl || 'M');
            qr.addData(text);
            qr.make();

            var moduleCount = qr.getModuleCount();
            var cellSize = Math.max(1, Math.floor(size / moduleCount));

            if (typeof qr.createSvgTag === 'function') {
                return qr.createSvgTag(cellSize, margin || 2);
            }

            // Manual SVG build fallback
            var svgSize = (moduleCount + (margin || 2) * 2) * cellSize;
            var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + svgSize + '" height="' + svgSize + '" viewBox="0 0 ' + svgSize + ' ' + svgSize + '">';
            svg += '<rect width="100%" height="100%" fill="#ffffff"/><g fill="#000000">';
            for (var r = 0; r < moduleCount; r++) {
                for (var c = 0; c < moduleCount; c++) {
                    if (qr.isDark(r, c)) {
                        var x = (c + (margin || 2)) * cellSize;
                        var y = (r + (margin || 2)) * cellSize;
                        svg += '<rect x="' + x + '" y="' + y + '" width="' + cellSize + '" height="' + cellSize + '"/>';
                    }
                }
            }
            svg += '</g></svg>';
            return svg;

        } catch (e) {
            return '<div class="clqrc-qr-error">QR generation failed</div>';
        }
    }

    /**
     * Convert SVG string to data URL
     */
    function svgToDataUrl(svg) {
        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
    }

    /**
     * Initialize Copy & QR buttons
     */
    function init() {
        var containers = document.querySelectorAll('.clqrc-buttons');
        if (!containers || !containers.length) return;

        containers.forEach(function (container) {
            var copyBtn = container.querySelector('.clqrc-copy');
            var qrBtn = container.querySelector('.clqrc-qr');
            var popup = container.querySelector('.clqrc-qr-popup');

            if (!popup) {
                popup = document.createElement('div');
                popup.className = 'clqrc-qr-popup';
                container.appendChild(popup);
            }

            // Copy-to-clipboard
            if (copyBtn) {
                copyBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var url = copyBtn.getAttribute('data-url') || window.location.href;

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(function () {
                            var prev = copyBtn.innerHTML;
                            copyBtn.innerHTML = (window.CLQRC && CLQRC.copy_text) ? CLQRC.copy_text : 'Copied';
                            setTimeout(function () { copyBtn.innerHTML = prev; }, 1500);
                        });
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = url;
                        document.body.appendChild(ta);
                        ta.select();
                        try { document.execCommand('copy'); } catch (e) { alert('Copy failed.'); }
                        ta.remove();
                    }
                });
            }

            // QR popup
            if (qrBtn) {
                qrBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var url = qrBtn.getAttribute('data-url') || window.location.href;

                    if (popup.getAttribute('data-visible') === '1') {
                        popup.style.display = 'none';
                        popup.setAttribute('data-visible', '0');
                        popup.innerHTML = '';
                        return;
                    }

                    var svg = createQrSvg(url, 256, 'M', 2);

                    // QR inner
                    var inner = document.createElement('div');
                    inner.className = 'clqrc-qr-inner';
                    inner.innerHTML = svg;

                    // Controls
                    var controls = document.createElement('div');
                    controls.style.marginTop = '8px';

                    var dlSvg = document.createElement('a');
                    dlSvg.href = svgToDataUrl(svg);
                    dlSvg.download = 'qr.svg';
                    dlSvg.textContent = 'Download SVG';
                    dlSvg.style.marginRight = '8px';
                    controls.appendChild(dlSvg);

                    var dlPng = document.createElement('a');
                    dlPng.href = '#';
                    dlPng.textContent = 'Download PNG';
                    dlPng.addEventListener('click', function (ev) {
                        ev.preventDefault();
                        try {
                            var canvas = document.createElement('canvas');
                            canvas.width = 256;
                            canvas.height = 256;
                            var ctx = canvas.getContext('2d');
                            var img = new Image();
                            img.onload = function () {
                                ctx.fillStyle = '#ffffff';
                                ctx.fillRect(0, 0, canvas.width, canvas.height);
                                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                var pngUrl = canvas.toDataURL('image/png');
                                var link = document.createElement('a');
                                link.href = pngUrl;
                                link.download = 'qr.png';
                                document.body.appendChild(link);
                                link.click();
                                link.remove();
                            };
                            img.src = svgToDataUrl(svg);
                        } catch (ex) {
                            alert('PNG export failed.');
                        }
                    });
                    controls.appendChild(dlPng);

                    // Close button
                    var close = document.createElement('div');
                    close.className = 'clqrc-qr-close';
                    close.textContent = 'Close';
                    close.addEventListener('click', function () {
                        popup.style.display = 'none';
                        popup.setAttribute('data-visible', '0');
                        popup.innerHTML = '';
                    });

                    popup.innerHTML = '';
                    popup.appendChild(inner);
                    popup.appendChild(controls);
                    popup.appendChild(close);
                    popup.style.display = 'inline-block';
                    popup.setAttribute('data-visible', '1');
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
