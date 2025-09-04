<?php
/**
 * Handles frontend display: buttons, shortcode.
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLQRC_Frontend {

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'the_content', array( $this, 'maybe_add_buttons' ) );
        add_shortcode( 'copy_link_and_qr_code', array( $this, 'shortcode_output' ) );
    }

    /**
     * Add buttons before/after content if enabled.
     */
    public function maybe_add_buttons( $content ) {
        if ( ! is_singular() ) {
            return $content;
        }

        $settings   = get_option( 'copy_link_and_qr_code_settings', array() );
        $post_types = isset( $settings['post_types'] ) ? (array) $settings['post_types'] : array();
        $position   = isset( $settings['position'] ) ? $settings['position'] : 'after';

        if ( ! in_array( get_post_type(), $post_types, true ) ) {
            return $content;
        }

        $buttons_html = $this->get_buttons_html();

        if ( 'before' === $position ) {
            return $buttons_html . $content;
        }

        return $content . $buttons_html;
    }

    /**
     * Shortcode output.
     */
    public function shortcode_output() {
        return $this->get_buttons_html();
    }

    /**
     * Generate HTML for buttons.
     */
    private function get_buttons_html() {
        ob_start();
        ?>
        <div class="clqrc-buttons">
            <button class="clqrc-copy" data-url="<?php echo esc_url( get_permalink() ); ?>">
                <?php esc_html_e( 'Copy Link', 'copy-link-and-qr-code' ); ?>
            </button>
            <button class="clqrc-qr" data-url="<?php echo esc_url( get_permalink() ); ?>">
                <?php esc_html_e( 'Show QR', 'copy-link-and-qr-code' ); ?>
            </button>
            <div class="clqrc-qr-popup" style="display:none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}
