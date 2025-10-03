=== Copy Link and QR Code ===
Contributors: kishanparmar
Donate link: https://www.paypal.com/paypalme/kishanparmar4951
Tags: copy link, qr code, share, shortcode, gutenberg block
Requires at least: 6.1
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add Copy Link & Show QR buttons to posts, pages, and custom post types. Includes settings for post types, button placement, admin toggle, shortcode, and a dynamic Gutenberg block.

== Description ==

**Copy Link and QR Code** allows you to add shareable buttons to posts, pages, and WooCommerce products:

* Copy the current page URL to the clipboard.
* Generate a QR code on the fly (no external API required).
* Configure post types, button placement, and toggle admin QR column via settings page.
* Shortcode: `[copy_link_and_qr_code]`
* Gutenberg block for easy insertion.
* Lightweight and privacy-friendly (no data collection).

This plugin bundles the **[qrcode-generator](https://www.npmjs.com/package/qrcode-generator)** library.

- Repository: [https://github.com/kazuhikoarase/qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator)  
- Homepage: [https://github.com/kazuhikoarase/qrcode-generator#readme](https://github.com/kazuhikoarase/qrcode-generator#readme)  
- Documentation: [https://docs.npmjs.com/](https://docs.npmjs.com/)  
- License: MIT  

This implementation is based on **JIS X 0510:1999**.  

The term "QR Code" is a registered trademark of [DENSO WAVE INCORPORATED](http://www.denso-wave.com/qrcode/faqpatent-e.html).  

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/copy-link-and-qr-code/`.  
2. Activate the plugin through the "Plugins" screen in WordPress.  
3. Configure settings under **Settings → Copy Link & QR Code**.  

== Frequently Asked Questions ==

= Does this plugin collect user data? =  
No. It does not collect, store, or send any data to external servers.  

= Can I use it with WooCommerce? =  
Yes. It works on products as well as posts and pages.  

= Can I show QR codes in the admin post list? =  
Yes. Enable the **Show QR Code Column in Admin** toggle in the plugin settings to display a QR code column in post/page/custom post type lists.

== Screenshots ==

1. Copy Link & Show QR buttons on a post/page/product.  
2. Plugin settings page.

== Changelog ==

= 1.1 =  
* Added admin setting toggle to enable/disable QR Code column in post/page/custom post type list tables.  
* Updated settings page to include the new toggle.  
* Refactored `CLQRC_Admin` class for better maintainability.  
* Minor HTML and formatting improvements in the settings page.  
* Ensured compatibility with WordPress 6.1+ and PHP 7.4+.  

= 1.0 =  
* Initial release.  
* Adds Copy Link & Show QR buttons on single posts, pages, and CPTs.  
* Settings page for post types & button position.  
* Shortcode `[copy_link_and_qr_code]` and Gutenberg block.  
* Privacy-friendly: no data collection.  

== Privacy ==

This plugin does not collect, store, or transmit any personal data.
