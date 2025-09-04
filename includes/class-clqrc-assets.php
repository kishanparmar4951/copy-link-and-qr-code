<?php
/**
 * Handles plugin CSS and JS enqueues.
 *
 * Local-first: if libraries/qrcode-generator/qrcode.js exists, use it.
 * Otherwise, only load CDN if admin turned on the "use CDN" setting.
 * If neither available, register a lightweight shim script that warns in console.
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Assets {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
    }

    /**
     * Register/Enqueue frontend assets.
     */
    public function enqueue_frontend() {
        // Styles
        wp_enqueue_style(
            'copy-link-and-qr-code-frontend',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            COPY_LINK_AND_QR_CODE_VERSION
        );

        // Determine QR library source
        $settings = wp_parse_args( get_option( 'copy_link_and_qr_code_settings', array() ), copy_link_and_qr_code_default_settings() );

        $local_unmin = COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'libraries/qrcode-generator/qrcode.js';
        $local_min   = COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'libraries/qrcode-generator/qrcode.min.js';
        $has_local_unmin = file_exists( $local_unmin );
        $has_local_min   = file_exists( $local_min );

        $handle = 'copy-link-and-qr-code-qrcode-lib';
        $registered = false;

        if ( $has_local_unmin || $has_local_min ) {
            $src = $has_local_unmin ? COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'libraries/qrcode-generator/qrcode.js' : COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'libraries/qrcode-generator/qrcode.min.js';
            wp_register_script( $handle, $src, array(), $settings['cdn_version'], true );
            $registered = true;
        } elseif ( ! empty( $settings['use_cdn'] ) ) {
            // Use CDN only if admin enabled it
            $cdn_ver = ! empty( $settings['cdn_version'] ) ? esc_attr( $settings['cdn_version'] ) : '1.4.4';
            $cdn_url = "https://cdn.jsdelivr.net/npm/qrcode-generator@{$cdn_ver}/qrcode.min.js";
            wp_register_script( $handle, $cdn_url, array(), $cdn_ver, true );
            $registered = true;
        }

        if ( $registered ) {
            wp_enqueue_script( $handle );
        } else {
            // Register a shim inline script that warns devs in console if library not found.
            $warn_handle = 'copy-link-and-qr-code-qrcode-shim';
            wp_register_script( $warn_handle, '', array(), COPY_LINK_AND_QR_CODE_VERSION, true );
            $shim = 'window.console && console.warn && console.warn("Copy Link & QR Code: qrcode-generator library not found. Place libraries/qrcode-generator/qrcode.js in the plugin folder or enable CDN in plugin settings.");';
            wp_add_inline_script( $warn_handle, $shim );
            wp_enqueue_script( $warn_handle );
        }

        // Frontend app script (depends on QR lib if present; shim safe)
        wp_enqueue_script(
            'copy-link-and-qr-code-frontend',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'copy-link-and-qr-code-qrcode-lib' ),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );

        // Localize text
        wp_localize_script(
            'copy-link-and-qr-code-frontend',
            'CLQRC',
            array(
                'copy_text' => __( 'Copied', 'copy-link-and-qr-code' ),
            )
        );
    }

    /**
     * Enqueue admin assets (only on plugin settings page).
     *
     * @param string $hook
     */
    public function enqueue_admin( $hook ) {
        // settings_page_copy-link-and-qr-code is the hook created in admin class
        if ( 'settings_page_copy-link-and-qr-code' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'copy-link-and-qr-code-admin',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            COPY_LINK_AND_QR_CODE_VERSION
        );

        wp_enqueue_script(
            'copy-link-and-qr-code-admin',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );
    }
}