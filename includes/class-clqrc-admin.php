<?php
/**
 * Handles settings page.
 *
 * Adds settings:
 *  - enabled post types
 *  - position (before|after)
 *  - use_cdn (boolean)
 *  - cdn_version (string)
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
            'use_cdn',
            __( 'Allow CDN fallback', 'copy-link-and-qr-code' ),
            array( $this, 'field_use_cdn' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );

        add_settings_field(
            'cdn_version',
            __( 'CDN Library Version', 'copy-link-and-qr-code' ),
            array( $this, 'field_cdn_version' ),
            'copy-link-and-qr-code',
            'clqrc_main'
        );
    }

    /**
     * Intro section callback.
     */
    public function section_intro() {
        echo '<p>' . esc_html__( 'Choose where to show the Copy Link and QR buttons. For best compliance with WordPress.org, bundle the unminified qrcode-generator library under /libraries/qrcode-generator/qrcode.js. If you prefer to use the CDN, enable the option below as a fallback.', 'copy-link-and-qr-code' ) . '</p>';
    }

    /**
     * Sanitize settings callback.
     *
     * @param array $input
     * @return array
     */
    public function sanitize_settings( $input ) {
        $defaults = copy_link_and_qr_code_default_settings();
        $output = array();

        // post_types
        $all_post_types = get_post_types( array( 'public' => true ), 'names' );
        $submitted = isset( $input['post_types'] ) ? (array) $input['post_types'] : $defaults['post_types'];
        $output['post_types'] = array_values( array_intersect( $all_post_types, array_map( 'sanitize_text_field', $submitted ) ) );

        // position
        $position = isset( $input['position'] ) ? sanitize_text_field( $input['position'] ) : $defaults['position'];
        $output['position'] = in_array( $position, array( 'before', 'after' ), true ) ? $position : $defaults['position'];

        // use_cdn (checkbox)
        $output['use_cdn'] = ! empty( $input['use_cdn'] ) ? 1 : 0;

        // cdn_version
        $cdn_version = isset( $input['cdn_version'] ) ? sanitize_text_field( $input['cdn_version'] ) : $defaults['cdn_version'];
        // basic validation: allow digits and dots
        $cdn_version = preg_replace( '/[^0-9\\.]/', '', $cdn_version );
        $output['cdn_version'] = $cdn_version ? $cdn_version : $defaults['cdn_version'];

        return $output;
    }

    /**
     * Field: post types.
     */
    public function field_post_types() {
        $options     = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        $post_types  = get_post_types( array( 'public' => true ), 'objects' );
        $enabled     = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();

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
     * Field: use CDN checkbox.
     */
    public function field_use_cdn() {
        $options = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        $use = isset( $options['use_cdn'] ) ? (int) $options['use_cdn'] : 0;
        printf(
            '<label><input type="checkbox" name="copy_link_and_qr_code_settings[use_cdn]" value="1" %s /> %s</label>',
            checked( 1, $use, false ),
            esc_html__( 'Allow loading qrcode-generator from jsDelivr CDN if local file is not present', 'copy-link-and-qr-code' )
        );
    }

    /**
     * Field: CDN version.
     */
    public function field_cdn_version() {
        $options = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        $ver     = isset( $options['cdn_version'] ) ? $options['cdn_version'] : '';

        printf(
            '<input type="text" name="copy_link_and_qr_code_settings[cdn_version]" value="%s" class="regular-text" /><p class="description">%s</p>',
            esc_attr( $ver ),
            esc_html__( 'Specify the qrcode-generator version to use from jsDelivr (e.g. 1.4.4). Only used if "Allow CDN fallback" is enabled.', 'copy-link-and-qr-code' )
        );
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

            <h2><?php esc_html_e( 'Bundling guidelines', 'copy-link-and-qr-code' ); ?></h2>
            <div class="clqrc-admin-help">
                <p><?php esc_html_e( 'For WordPress.org plugin review, bundle the UNMINIFIED qrcode-generator source at:', 'copy-link-and-qr-code' ); ?></p>
                <code><?php echo esc_html( COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'libraries/qrcode-generator/qrcode.js' ); ?></code>
                <p><?php esc_html_e( 'If you cannot bundle the file, enabling the CDN fallback will load the library from jsDelivr. Bundling unminified source is preferred.', 'copy-link-and-qr-code' ); ?></p>
            </div>
        </div>
        <?php
    }
}
