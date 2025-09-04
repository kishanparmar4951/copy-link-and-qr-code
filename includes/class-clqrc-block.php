<?php
/**
 * Registers the Gutenberg block (editor assets + server render).
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Block {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Register block assets and block type.
     */
    public function register_block() {
        $dir = COPY_LINK_AND_QR_CODE_PLUGIN_DIR;

        // Editor script for block (simple preview).
        wp_register_script(
            'copy-link-and-qr-code-block',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/js/block.js',
            array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor' ),
            COPY_LINK_AND_QR_CODE_VERSION,
            true
        );

        // Editor styles (optional)
        wp_register_style(
            'copy-link-and-qr-code-block-editor',
            COPY_LINK_AND_QR_CODE_PLUGIN_URL . 'assets/css/block-editor.css',
            array( 'wp-edit-blocks' ),
            COPY_LINK_AND_QR_CODE_VERSION
        );

        // Register a dynamic block with server render callback.
        register_block_type(
            'copy-link-and-qr-code/block',
            array(
                'editor_script'   => 'copy-link-and-qr-code-block',
                'editor_style'    => 'copy-link-and-qr-code-block-editor',
                'render_callback' => array( $this, 'render_block' ),
                'attributes'      => array(
                    // no attributes required now; future-proofing.
                ),
            )
        );
    }

    /**
     * Server-side render callback for the block.
     *
     * @param array $attributes Block attributes.
     * @return string HTML output for front-end.
     */
    public function render_block( $attributes ) {
        // Use the frontend class to produce consistent HTML.
        if ( ! class_exists( 'CLQRC_Frontend' ) ) {
            require_once COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'includes/class-clqrc-frontend.php';
        }

        $frontend = new CLQRC_Frontend();
        return $frontend->shortcode_output();
    }
}
