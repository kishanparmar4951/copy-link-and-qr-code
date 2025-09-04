<?php
/**
 * Privacy integration for WordPress Privacy tools.
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Privacy {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
    }

    /**
     * Add plugin privacy text to Settings → Privacy (if available).
     */
    public function add_privacy_policy_content() {
        if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
            $content  = '<p>' . esc_html__( 'Copy Link and QR Code generates QR codes in the visitor\'s browser and provides a copy-to-clipboard action. The plugin does not collect, store, or transmit any personal data by default.', 'copy-link-and-qr-code' ) . '</p>';
            $content .= '<p>' . esc_html__( 'If an administrator chooses to load the qrcode-generator library from a CDN, the visitor\'s browser may request that static asset from the CDN which may log the visitor\'s IP according to the CDN provider\'s policy.', 'copy-link-and-qr-code' ) . '</p>';

            wp_add_privacy_policy_content(
                __( 'Copy Link and QR Code', 'copy-link-and-qr-code' ),
                wp_kses_post( wpautop( $content ) )
            );
        }
    }
}
