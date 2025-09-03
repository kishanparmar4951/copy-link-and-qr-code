<?php
/**
 * Plugin Name: Copy Link and QR Code
 * Contributors: kishanparmar
 * Description: Adds “Copy Link” and “Show QR” buttons on single posts/pages/products. Includes settings (enable per post-type, position), shortcode, and a dynamic Gutenberg block.
 * Version: 1.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author: Kishan Parmar
 * Author URI: https://profiles.wordpress.org/kishanparmar/
 * Text Domain: copy-link-and-qr-code
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html 
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add Settings link on Plugins page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
    $settings_url = admin_url( 'options-general.php?page=clqr-settings' );
    $settings_link = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'copy-link-and-qr-code' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
});

if ( ! class_exists( 'CLQR_Plugin' ) ) {

final class CLQR_Plugin {

    const VERSION = '1.0';
    const OPTION_KEY = 'clqr_settings';

    private static $instance = null;
    private $defaults = array(
        'auto_insert' => true, // auto insert in content
        'post_types'  => array( 'post' => 1, 'page' => 1 ), // default enabled post types
        'position'    => 'after', // before|after
        'labels'      => array(
            'copy'        => 'Copy Link',
            'copied'      => 'Copied!',
            'show_qr'     => 'Show QR',
            'close'       => 'Close',
            'modal_title' => 'Scan to open this page',
            'error'       => 'Could not copy the link. Please copy it manually.',
        ),
    );

    /**
     * Get instance.
     */
    public static function instance() : self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {        
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
        add_filter( 'the_content', array( $this, 'append_buttons' ) );
        add_shortcode( 'clqr-buttons', array( $this, 'shortcode_buttons' ) );

        // Gutenberg block (dynamic).
        add_action( 'init', array( $this, 'register_block' ) );

        // Admin assets for settings page.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
    }

    /**
     * Register settings page.
     */
    public function admin_menu() : void {
        add_options_page(
            __( 'Copy Link and QR Code', 'copy-link-and-qr-code' ),
            __( 'Copy Link and QR Code', 'copy-link-and-qr-code' ),
            'manage_options',
            'clqr-settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Register plugin options.
     */
    public function register_settings() : void {
        register_setting( 'clqr_options_group', self::OPTION_KEY, array( $this, 'sanitize_settings' ) );

        add_settings_section( 'clqr_general', __( 'General', 'copy-link-and-qr-code' ), '__return_false', 'clqr-settings' );

        add_settings_field(
            'clqr_auto_insert',
            __( 'Auto insert buttons', 'copy-link-and-qr-code' ),
            array( $this, 'field_auto_insert_cb' ),
            'clqr-settings',
            'clqr_general'
        );

        add_settings_field(
            'clqr_post_types',
            __( 'Enable for post types', 'copy-link-and-qr-code' ),
            array( $this, 'field_post_types_cb' ),
            'clqr-settings',
            'clqr_general'
        );

        add_settings_field(
            'clqr_position',
            __( 'Position', 'copy-link-and-qr-code' ),
            array( $this, 'field_position_cb' ),
            'clqr-settings',
            'clqr_general'
        );

        add_settings_section( 'clqr_labels', __( 'Labels', 'copy-link-and-qr-code' ), '__return_false', 'clqr-settings' );

        // labels fields
        foreach ( $this->defaults['labels'] as $key => $default ) {
            add_settings_field(
                'clqr_label_' . $key,
                /* translators: %s: Label key name */
                sprintf(
                /* translators: %s: Label key name */
                    __( 'Label: %s', 'copy-link-and-qr-code' ),
                    ucfirst( str_replace( '_', ' ', $key ) )
                ),
                array( $this, 'field_label_cb' ),
                'clqr-settings',
                'clqr_labels',
                array( 'key' => $key )
            );
        }
    }

    /**
     * Sanitize settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = $this->defaults;

        // auto_insert
        $sanitized['auto_insert'] = isset( $input['auto_insert'] ) && $input['auto_insert'] ? true : false;

        // post_types
        $pt_input = isset( $input['post_types'] ) && is_array( $input['post_types'] ) ? $input['post_types'] : array();
        $enabled = array();
        $all_pts = get_post_types( array( 'public' => true ), 'names' );
        foreach ( $all_pts as $pt ) {
            if ( isset( $pt_input[ $pt ] ) && $pt_input[ $pt ] ) {
                $enabled[ $pt ] = 1;
            }
        }
        // fallback to defaults if empty
        if ( empty( $enabled ) ) {
            $enabled = $this->defaults['post_types'];
        }
        $sanitized['post_types'] = $enabled;

        // position
        $pos = isset( $input['position'] ) && in_array( $input['position'], array( 'before', 'after', 'manual' ), true ) ? $input['position'] : $this->defaults['position'];
        $sanitized['position'] = $pos;

        // labels
        $labels = isset( $input['labels'] ) && is_array( $input['labels'] ) ? $input['labels'] : array();
        foreach ( $this->defaults['labels'] as $key => $val ) {
            $raw = isset( $labels[ $key ] ) ? wp_kses_post( trim( $labels[ $key ] ) ) : $val;
            $sanitized['labels'][ $key ] = sanitize_text_field( $raw );
        }

        return $sanitized;
    }

    /**
     * Field callbacks.
     */
    public function field_auto_insert_cb() {
        $options = $this->get_options();
        printf(
            '<input type="checkbox" id="clqr_auto_insert" name="%1$s[auto_insert]" value="1" %2$s /> <label for="clqr_auto_insert">%3$s</label>',
            esc_attr( self::OPTION_KEY ),
            checked( 1, $options['auto_insert'], false ),
            esc_html__( 'Automatically append buttons to supported single views', 'copy-link-and-qr-code' )
        );
    }

    public function field_post_types_cb() {
        $options = $this->get_options();
        $pts     = get_post_types( array( 'public' => true ), 'names' );

        foreach ( $pts as $pt ) {
            $checked = isset( $options['post_types'][ $pt ] ) && $options['post_types'][ $pt ] ? 'checked' : '';
            printf(
                '<label style="display:inline-block;margin-right:8px;"><input type="checkbox" name="%1$s[post_types][%2$s]" value="1" %3$s /> %4$s</label>',
                esc_attr( self::OPTION_KEY ),
                esc_attr( $pt ),
                esc_attr( $checked ),
                esc_html( $pt )
            );
        }
    }

    public function field_position_cb() {
        // Get options and default position
        $options = $this->get_options();
        $pos = isset($options['position']) ? $options['position'] : $this->defaults['position'];

        $option_key = self::OPTION_KEY;
        ?>
        <label>
            <input type="radio" name="<?php echo esc_attr( $option_key ); ?>[position]" value="before" <?php checked( 'before', $pos ); ?> />
            Before content
        </label><br/>

        <label>
            <input type="radio" name="<?php echo esc_attr( $option_key ); ?>[position]" value="after" <?php checked( 'after', $pos ); ?> />
            After content
        </label><br/>

        <label>
            <input type="radio" name="<?php echo esc_attr( $option_key ); ?>[position]" value="manual" <?php checked( 'manual', $pos ); ?> />
            Manual only (use shortcode or block)
        </label>
        <?php
    }

    public function field_label_cb( $args ) {
        $key     = $args['key'];
        $options = $this->get_options();
        $value   = isset( $options['labels'][ $key ] ) ? $options['labels'][ $key ] : $this->defaults['labels'][ $key ];
        printf(
            '<input type="text" class="regular-text" name="%1$s[labels][%2$s]" value="%3$s" />',
            esc_attr( self::OPTION_KEY ),
            esc_attr( $key ),
            esc_attr( $value )
        );
    }

    /**
     * Settings page output.
     */
    public function settings_page() : void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Copy Link and QR Code', 'copy-link-and-qr-code' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'clqr_options_group' );
                do_settings_sections( 'clqr-settings' );
                submit_button();
                ?>
            </form>
            <h2><?php esc_html_e( 'Shortcode & Block', 'copy-link-and-qr-code' ); ?></h2>
           <p>
                <?php
                printf(
                    /* translators: %s: shortcode [clqr-buttons] */
                    esc_html__( 'Use the shortcode %s to place buttons manually. The Block "Copy Link and QR Code" is available in the block editor under Widgets or by searching "Copy Link".', 'copy-link-and-qr-code' ),
                    '<code>[clqr-buttons]</code>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Return plugin options merged with defaults.
     */
    public function get_options() : array {
        $options = get_option( self::OPTION_KEY, array() );
        return wp_parse_args( $options, $this->defaults );
    }

    /**
     * Whether current request should show buttons.
     */
    private function is_supported_singular() : bool {
        if ( is_admin() || is_feed() ) {
            return false;
        }

        if ( ! is_singular() ) {
            return false;
        }

        $options = $this->get_options();
        $pt      = get_post_type();

        if ( isset( $options['post_types'][ $pt ] ) && $options['post_types'][ $pt ] ) {
            return true;
        }

        return false;
    }

    /**
     * Enqueue frontend assets only where needed (or for block render).
     */
    public function enqueue() : void {
        // If not singular and not block rendered, still enqueue when a block is present in content
        $should_enqueue = false;

        $options = $this->get_options();

        // If auto_insert is enabled and this is supported singular, enqueue.
        if ( $options['auto_insert'] && $this->is_supported_singular() ) {
            $should_enqueue = true;
        }

        // If block exists in the post content, we need to enqueue. Check content.
        if ( ! $should_enqueue && is_singular() ) {
            global $post;
            if ( isset( $post->post_content ) && has_block( 'clqr/copy-link-and-qr-code', $post ) ) {
                $should_enqueue = true;
            }
        }

        // If shortcode exists, we must enqueue when viewing singular or wherever shortcode runs.
        if ( ! $should_enqueue && is_singular() ) {
            global $post;
            if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'clqr-buttons' ) ) {
                $should_enqueue = true;
            }
        }

        if ( ! $should_enqueue ) {
            return;
        }

        $asset_url = plugin_dir_url( __FILE__ ) . 'assets/';
        $ver       = self::VERSION;

        wp_enqueue_style( 'clqr-style', $asset_url . 'css/style.css', array(), $ver );
        wp_enqueue_script( 'clqr-qrcode', $asset_url . 'js/qrcode.min.js', array(), $ver, true );
        wp_enqueue_script( 'clqr-script', $asset_url . 'js/script.js', array( 'clqr-qrcode' ), $ver, true );

        // Pass runtime data
        $current_url = get_permalink();
        if ( false === $current_url ) {
            $current_url = home_url( '/' );
        }

        $options = $this->get_options();

        $labels = array_map( 'wp_kses_post', $options['labels'] );

        wp_localize_script(
            'clqr-script',
            'CLQR',
            array(
                'url'    => esc_url( $current_url ),
                'labels' => array_map( 'wp_kses_post', $labels ),
            )
        );
    }

    /**
     * Build buttons HTML.
     */
    private function get_buttons_html( string $url ) : string {
        $options = $this->get_options();
        $labels  = $options['labels'];

        $copy_label   = esc_html( $labels['copy'] );
        $qr_label     = esc_html( $labels['show_qr'] );
        $aria_label_1 = esc_attr__( 'Copy permalink to clipboard', 'copy-link-and-qr-code' );
        $aria_label_2 = esc_attr__( 'Open QR code modal', 'copy-link-and-qr-code' );

        $button_tpl = sprintf(
            '<div class="clqr-wrap" data-clqr-url="%1$s">
                <button type="button" class="clqr-btn clqr-copy" aria-label="%2$s">%3$s</button>
                <button type="button" class="clqr-btn clqr-qr" aria-label="%4$s">%5$s</button>
            </div>',
            esc_url( $url ),
            $aria_label_1,
            $copy_label,
            $aria_label_2,
            $qr_label
        );

        return $button_tpl;
    }

    /**
     * Filter the_content to append or prepend buttons depending on settings.
     */
    public function append_buttons( $content ) {
        $options = $this->get_options();

        // Only auto insert when enabled and supported
        if ( ! $options['auto_insert'] || ! $this->is_supported_singular() || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $url = get_permalink();
        if ( empty( $url ) ) {
            return $content;
        }

        $html = $this->get_buttons_html( $url );
        if ( 'before' === $options['position'] ) {
            return $html . "\n" . $content;
        }
        // default after
        return $content . "\n" . $html;
    }

    /**
     * Shortcode handler.
     */
    public function shortcode_buttons() : string {
        $url = get_permalink();
        if ( empty( $url ) ) {
            return '';
        }
        return $this->get_buttons_html( $url );
    }

    /**
     * Register a simple dynamic block that outputs the buttons via server render.
     */
    public function register_block() : void {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        $dir = plugin_dir_path( __FILE__ );
        $asset_url = plugin_dir_url( __FILE__ ) . 'assets/';

        // Editor script for the block (placeholder UI).
        wp_register_script(
            'clqr-block-editor',
            $asset_url . 'js/block-editor.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n' ),
            self::VERSION,
            true
        );

        register_block_type( 'clqr/copy-link-and-qr-code', array(
            'editor_script'   => 'clqr-block-editor',
            'render_callback' => array( $this, 'render_block_callback' ),
            'attributes'      => array(), // no attributes for now
        ) );
    }

    /**
     * Server render callback for the block.
     */
    public function render_block_callback( $attributes ) {
        // Use current global post
        global $post;
        if ( ! isset( $post ) ) {
            return '';
        }
        $url = get_permalink( $post );
        if ( empty( $url ) ) {
            return '';
        }

        // Ensure assets are enqueued when block renders.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

        return $this->get_buttons_html( $url );
    }

    /**
     * Enqueue admin assets for the settings page.
     */
    public function admin_assets( $hook ) {
        if ( 'settings_page_clqr-settings' !== $hook ) {
            // Also enqueue editor block script when editing posts is open
            $screen = get_current_screen();
            if ( $screen && 'post' === $screen->base ) {
                // enqueue block editor script already registered in register_block
                if ( wp_script_is( 'clqr-block-editor', 'registered' ) ) {
                    wp_enqueue_script( 'clqr-block-editor' );
                }
            }
            return;
        }

        $asset_url = plugin_dir_url( __FILE__ ) . 'assets/';
        wp_enqueue_script( 'clqr-admin', $asset_url . 'js/admin.js', array( 'wp-element' ), self::VERSION, true );

        // pass available public post types for nice UI (optional)
        $pts = get_post_types( array( 'public' => true ), 'objects' );
        $pt_names = array();
        foreach ( $pts as $pt ) {
            $pt_names[] = array( 'name' => $pt->name, 'label' => $pt->labels->singular_name );
        }

        wp_localize_script( 'clqr-admin', 'CLQRAdmin', array( 'postTypes' => $pt_names ) );
    }
}

// bootstrap
CLQR_Plugin::instance();

}
