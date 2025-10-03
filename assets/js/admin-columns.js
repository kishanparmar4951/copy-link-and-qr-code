/**
 * Admin Columns JS for Copy Link & QR Code
 * - Renders QR codes in post/page/CPT admin columns
 * Requires: qrcode-generator library (qrcode.js)
 */

(function ($) {
    'use strict';

    function renderQrColumns() {
        if (typeof qrcode !== 'function') {
            console.warn('QR library not loaded');
            return;
        }

        $('.clqrc-qr-admin').each(function () {
            var el = this;
            var url = $(el).data('url');
            if (!url) return;

            // Clear previous QR if any
            $(el).empty();

            try {
                // Generate QR
                var qr = qrcode(0, 'M');
                qr.addData(url);
                qr.make();

                // Create SVG manually
                var moduleCount = qr.getModuleCount();
                var size = 80; // width/height
                var cellSize = Math.floor(size / moduleCount);
                var margin = 2;
                var svgSize = (moduleCount + margin * 2) * cellSize;

                var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + svgSize + '" height="' + svgSize + '" viewBox="0 0 ' + svgSize + ' ' + svgSize + '">';
                svg += '<rect width="100%" height="100%" fill="#ffffff"/><g fill="#000000">';

                for (var r = 0; r < moduleCount; r++) {
                    for (var c = 0; c < moduleCount; c++) {
                        if (qr.isDark(r, c)) {
                            var x = (c + margin) * cellSize;
                            var y = (r + margin) * cellSize;
                            svg += '<rect x="' + x + '" y="' + y + '" width="' + cellSize + '" height="' + cellSize + '"/>';
                        }
                    }
                }
                svg += '</g></svg>';

                $(el).html(svg);

            } catch (e) {
                $(el).html('<div class="clqrc-qr-error">QR generation failed</div>');
            }
        });
    }

    // Wait for DOM ready
    $(document).ready(function () {
        renderQrColumns();
    });

})(jQuery);
