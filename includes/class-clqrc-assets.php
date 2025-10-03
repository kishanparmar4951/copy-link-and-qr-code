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

        // Styles
        wp_enqueue_style(
            'copy-link-and-qr-code-frontend',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            COPY_LINK_AND_QR_CODE_VERSION
        );

        // QR code library
        wp_enqueue_script(
            'copy-link-and-qr-code-qrcode-lib',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'libraries/qrcode-generator/qrcode.js',
            array(),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );

        // Frontend JS
        wp_enqueue_script(
            'copy-link-and-qr-code-frontend',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'copy-link-and-qr-code-qrcode-lib' ),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );

        // Localize for copy text
        wp_localize_script(
            'copy-link-and-qr-code-frontend',
            'CLQRC',
            array(
                'copy_text' => __( 'Copied', 'copy-link-and-qr-code' ),
            )
        );
    }

    /**
     * Enqueue admin assets (only on plugin settings page and admin columns).
     *
     * @param string $hook
     */
    /**
 * Enqueue admin assets (settings page + QR column)
 *
 * @param string $hook
 */
public function enqueue_admin( $hook ) {

    // Enqueue plugin settings page styles & script
    if ( 'settings_page_copy-link-and-qr-code' === $hook ) {
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

    // Enqueue QR column scripts if admin QR column is enabled
    $settings = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
    if ( ! empty( $settings['admin_qr_column'] ) ) {

        // Ensure this is a post/page/CPT list table
        $current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( $current_screen && in_array( $current_screen->base, array( 'edit', 'edit-tags' ), true ) ) {

            // QR code library (qrcode-generator)
            wp_enqueue_script(
                'copy-link-and-qr-code-qrcode-lib-admin',
                COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'libraries/qrcode-generator/qrcode.js',
                array(),
                COPY_LINK_AND_QR_CODE_VERSION,
                true
            );

            // Admin column script
            wp_enqueue_script(
                'copy-link-and-qr-code-admin-column',
                COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/js/admin-columns.js',
                array( 'jquery', 'copy-link-and-qr-code-qrcode-lib-admin' ),
                COPY_LINK_AND_QR_CODE_VERSION,
                true
            );
        }
    }
}


}
