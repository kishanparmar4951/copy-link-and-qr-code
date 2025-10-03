<?php
/**
 * Handles settings page.
 *
 * Adds settings:
 *  - enabled post types
 *  - position (before|after)
 *  - toggle admin QR column
 *  - toggle auto-display
 *  - shortcode info
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add settings page under "Settings".
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Copy Link & QR Code', 'copy-link-and-qr-code' ),
            __( 'Copy Link & QR Code', 'copy-link-and-qr-code' ),
            'manage_options',
            'copy-link-and-qr-code',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        register_setting(
            'copy_link_and_qr_code_settings',
            'copy_link_and_qr_code_settings',
            array( $this, 'sanitize_settings' )
        );

        add_settings_section(
            'clqrc_main',
            __( 'General Settings', 'copy-link-and-qr-code' ),
            array( $this, 'section_intro' ),
            'copy-link-and-qr-code'
        );

        add_settings_field(
            'post_types',
            __( 'Enable for Post Types', 'copy-link-and-qr-code' ),
            array( $this, 'field_post_types' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );

        add_settings_field(
            'position',
            __( 'Button Position', 'copy-link-and-qr-code' ),
            array( $this, 'field_position' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );

        add_settings_field(
            'admin_qr_column',
            __( 'Admin QR Column', 'copy-link-and-qr-code' ),
            array( $this, 'field_admin_qr_column' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );

        add_settings_field(
            'auto_display',
            __( 'Automatic Display', 'copy-link-and-qr-code' ),
            array( $this, 'field_auto_display' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );

        add_settings_field(
            'shortcode_info',
            __( 'Shortcode', 'copy-link-and-qr-code' ),
            array( $this, 'field_shortcode_info' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );
    }

    /**
     * Intro section callback.
     */
    public function section_intro() {
        echo '<p>' . esc_html__( 'Configure where and how the Copy Link & QR Code buttons should appear.', 'copy-link-and-qr-code' ) . '</p>';
    }

    /**
     * Sanitize settings callback.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_settings( $input ) {
        $defaults = copy_link_and_qr_code_default_settings();
        $output   = array();

        // Post types
        $all_post_types = get_post_types( array( 'public' => true ), 'names' );
        $submitted      = isset( $input['post_types'] ) ? (array) $input['post_types'] : $defaults['post_types'];
        $submitted      = array_map( 'sanitize_text_field', $submitted );
        $output['post_types'] = array_values( array_intersect( $all_post_types, $submitted ) );

        // Position
        $position = isset( $input['position'] ) ? sanitize_text_field( $input['position'] ) : $defaults['position'];
        $output['position'] = in_array( $position, array( 'before', 'after' ), true ) ? $position : $defaults['position'];

        // Admin QR column toggle
        $output['admin_qr_column'] = ! empty( $input['admin_qr_column'] ) ? true : false;

        // Auto display toggle
        $output['auto_display'] = ! empty( $input['auto_display'] ) ? true : false;

        return $output;
    }

    /**
     * Field: post types.
     */
    public function field_post_types() {
        $options    = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        $enabled    = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();

        foreach ( $post_types as $slug => $obj ) {
            printf(
                '<label style="display:block;"><input type="checkbox" name="copy_link_and_qr_code_settings[post_types][]" value="%s" %s /> %s</label>',
                esc_attr( $slug ),
                checked( in_array( $slug, $enabled, true ), true, false ),
                esc_html( $obj->labels->singular_name )
            );
        }
    }

    /**
     * Field: position.
     */
    public function field_position() {
        $options  = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        $position = isset( $options['position'] ) ? $options['position'] : 'after';
        ?>
        <select name="copy_link_and_qr_code_settings[position]">
            <option value="before" <?php selected( $position, 'before' ); ?>><?php esc_html_e( 'Before Content', 'copy-link-and-qr-code' ); ?></option>
            <option value="after" <?php selected( $position, 'after' ); ?>><?php esc_html_e( 'After Content', 'copy-link-and-qr-code' ); ?></option>
        </select>
        <?php
    }

    /**
     * Field: admin QR column toggle.
     */
    public function field_admin_qr_column() {
        $options = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        ?>
        <label>
            <input type="checkbox" name="copy_link_and_qr_code_settings[admin_qr_column]" value="1" <?php checked( $options['admin_qr_column'], true ); ?> />
            <?php esc_html_e( 'Enable QR code column in admin post/page/CPT list tables.', 'copy-link-and-qr-code' ); ?>
        </label>
        <?php
    }

    /**
     * Field: auto display toggle.
     */
    public function field_auto_display() {
        $options = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        ?>
        <label>
            <input type="checkbox" name="copy_link_and_qr_code_settings[auto_display]" value="1" <?php checked( $options['auto_display'], true ); ?> />
            <?php esc_html_e( 'Automatically insert buttons before/after content for enabled post types.', 'copy-link-and-qr-code' ); ?>
        </label>
        <?php
    }

    /**
     * Field: shortcode info (read-only).
     */
    public function field_shortcode_info() {
        ?>
        <p><?php esc_html_e( 'You can manually insert the buttons anywhere using this shortcode:', 'copy-link-and-qr-code' ); ?></p>
        <code>[copy_link_and_qr_code]</code>
        <?php
    }

    /**
     * Render settings page.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Copy Link & QR Code Settings', 'copy-link-and-qr-code' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'copy_link_and_qr_code_settings' );
                do_settings_sections( 'copy-link-and-qr-code' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
