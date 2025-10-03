# Copy Link and QR Code

Adds **Copy Link** & **Show QR** buttons to any post type (posts, pages, WooCommerce products).  
Includes settings for post types, button placement, admin QR column toggle, shortcode, and a dynamic Gutenberg block.

[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://www.paypal.com/paypalme/kishanparmar4951)

---

## Features

- 📋 Copy the current page URL to the clipboard.  
- 📱 Generate a QR code on the fly (no external API required).  
- ⚙️ Settings page to enable/disable buttons per post type and control placement.  
- 🔧 Shortcode: `[copy_link_and_qr_code]`  
- 📦 Gutenberg block for easy insertion.  
- 🪶 Lightweight and privacy-friendly (no data collection).  

---

## Installation

1. Upload the plugin files to `/wp-content/plugins/copy-link-and-qr-code/`.  
2. Activate the plugin via the **Plugins** screen in WordPress.  
3. Configure settings under **Settings → Copy Link & QR Code**.  

---

## Screenshots

1. Copy and QR buttons on a post/page/product.  
2. Plugin settings page.  
3. Admin post list showing the QR code column (toggleable in settings).  

---

## Frequently Asked Questions (FAQ)

### ❓ Does this plugin collect user data?  
No. It does not collect, store, or send any data to external servers.  

### ❓ Can I use it with WooCommerce?  
Yes. It works on products as well as posts/pages.  

### ❓ Can I show QR codes in the admin post list?  
Yes. Enable the **Show QR Code Column in Admin** toggle in the plugin settings to display a QR code column in post/page/custom post type lists.  

---

## Changelog

### 1.1
- Added admin toggle to enable/disable QR Code column in post/page/custom post type list tables.  
- Updated settings page to include the new toggle.  
- Refactored `CLQRC_Admin` class for better maintainability.  
- Minor HTML and formatting improvements in the settings page.  
- Ensured compatibility with WordPress 6.1+ and PHP 7.4+.  

### 1.0
- Initial release.  
- Adds Copy Link & Show QR buttons on single posts/pages/CPTs.  
- Settings page for post types & button placement.  
- Shortcode `[copy_link_and_qr_code]` and Gutenberg block.  
- Privacy-friendly: no data collection.  

---

## Library Attribution

This plugin bundles the **[qrcode-generator](https://www.npmjs.com/package/qrcode-generator)** library.  

- Repository: [https://github.com/kazuhikoarase/qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator)  
- Homepage: [https://github.com/kazuhikoarase/qrcode-generator#readme](https://github.com/kazuhikoarase/qrcode-generator#readme)  
- Documentation: [https://docs.npmjs.com/](https://docs.npmjs.com/)  
- License: MIT  

This implementation is based on **JIS X 0510:1999**.  

The word **"QR Code"** is a registered trademark of [DENSO WAVE INCORPORATED](http://www.denso-wave.com/qrcode/faqpatent-e.html).  

---

## License

- Plugin: [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html)  
- Bundled library (**qrcode-generator**): MIT License  

---

## Privacy

This plugin does not collect, store, or transmit any personal data.
