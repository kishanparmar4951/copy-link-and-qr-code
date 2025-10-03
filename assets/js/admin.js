/**
 * Admin JS for Copy Link & QR Code
 * - Generates QR codes in the admin list table column
 *
 * Requires: QRCode.js (https://github.com/davidshimjs/qrcodejs)
 */
jQuery(document).ready(function ($) {
    if (typeof QRCode !== "undefined") {
        $(".clqrc-qr-admin").each(function () {
            const el = $(this)[0];
            const url = el.getAttribute("data-url");
            if (url) {
                new QRCode(el, {
                    text: url,
                    width: 80,
                    height: 80,
                });
            }
        });
    } else {
        console.warn("QRCode.js not loaded. Admin QR column will not render.");
    }
});
