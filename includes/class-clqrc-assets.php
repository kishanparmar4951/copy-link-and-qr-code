<?php
/**
 * Handles plugin CSS and JS enqueues.
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
        
        wp_enqueue_style(
            'copy-link-and-qr-code-frontend',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            COPY_LINK_AND_QR_CODE_VERSION
        );
        wp_enqueue_script(
            'copy-link-and-qr-code-qrcode-lib',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'libraries/qrcode-generator/qrcode.js',
            array(),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );        
        wp_enqueue_script(
            'copy-link-and-qr-code-frontend',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'copy-link-and-qr-code-qrcode-lib' ),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );
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