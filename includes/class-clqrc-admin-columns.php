<?php
/**
 * Admin list table QR Code column
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Admin_Columns {

    /**
     * Constructor
     */
    public function __construct() {
        $options = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );

        // Only hook if admin QR column is enabled
        if ( ! empty( $options['admin_qr_column'] ) ) {
            add_action( 'admin_init', array( $this, 'init_hooks' ) );
        }
    }

    /**
     * Init hooks for all public post types.
     */
    public function init_hooks() {
        $post_types = get_post_types( array( 'public' => true ), 'names' );

        foreach ( $post_types as $pt ) {
            add_filter( "manage_{$pt}_posts_columns", array( $this, 'add_qr_column' ) );
            add_action( "manage_{$pt}_posts_custom_column", array( $this, 'render_qr_column' ), 10, 2 );
        }
    }

    /**
     * Add QR Code column to list table.
     *
     * @param array $columns
     * @return array
     */
    public function add_qr_column( $columns ) {
        $columns['clqrc_qr'] = __( 'QR Code', 'copy-link-and-qr-code' );
        return $columns;
    }

    /**
     * Render QR Code in column.
     *
     * @param string $column
     * @param int    $post_id
     */
    public function render_qr_column( $column, $post_id ) {
        if ( 'clqrc_qr' !== $column ) {
            return;
        }

        $url = get_permalink( $post_id );

        // Display QR placeholder div; admin JS will render QR
        echo '<div class="clqrc-qr-admin" data-url="' . esc_url( $url ) . '" style="width:80px;height:80px;"></div>';
    }
}
