<?php
/**
 * Frontend display: shortcode + optional auto display
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Frontend {

    /**
     * Constructor
     */
    public function __construct() {
        // Shortcode
        add_shortcode( 'copy_link_and_qr_code', array( $this, 'shortcode_output' ) );

        // Auto insert if enabled
        $settings = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        if ( ! empty( $settings['auto_display'] ) ) {
            add_filter( 'the_content', array( $this, 'maybe_add_buttons' ) );
        }
    }

    /**
     * Shortcode output.
     *
     * @param array $atts Shortcode attributes (not used currently)
     * @return string
     */
    public function shortcode_output( $atts = array() ) {
        if ( ! is_singular() ) {
            return '';
        }

        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return '';
        }

        $url = get_permalink( $post_id );

        ob_start();
        ?>
        <div class="clqrc-buttons">
            <button type="button" class="clqrc-copy" data-url="<?php echo esc_url( $url ); ?>">
                <?php esc_html_e( 'Copy Link', 'copy-link-and-qr-code' ); ?>
            </button>
            <button type="button" class="clqrc-qr" data-url="<?php echo esc_url( $url ); ?>">
                <?php esc_html_e( 'Show QR', 'copy-link-and-qr-code' ); ?>
            </button>
            <div class="clqrc-qr-popup" style="display:none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Maybe auto-insert buttons before/after content.
     *
     * @param string $content
     * @return string
     */
    public function maybe_add_buttons( $content ) {
        if ( ! is_singular() ) {
            return $content;
        }

        global $post;
        if ( ! $post || empty( $post->post_type ) ) {
            return $content;
        }

        $settings   = get_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
        $post_types = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array();

        if ( ! in_array( $post->post_type, $post_types, true ) ) {
            return $content;
        }

        $buttons = $this->shortcode_output();

        if ( 'before' === ( $settings['position'] ?? 'after' ) ) {
            return $buttons . $content;
        }

        return $content . $buttons;
    }
}
