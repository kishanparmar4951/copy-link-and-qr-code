<?php
/**
 * Plugin Name: Copy Link and QR Code
 * Contributors: kishanparmar
 * Author: Kishan Parmar
 * Author URI: https://profiles.wordpress.org/kishanparmar/
 * Donate link: https://www.paypal.com/paypalme/kishanparmar4951
 * Description: Adds “Copy Link” and “Show QR” buttons on single any post type. Includes settings (enable per post-type, position), shortcode, and a dynamic Gutenberg block.
 * Version: 1.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Text Domain: copy-link-and-qr-code
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! defined( 'COPY_LINK_AND_QR_CODE_VERSION' ) ) {
    define( 'COPY_LINK_AND_QR_CODE_VERSION', '1.0.1' );
}

if ( ! defined( 'COPY_LINK_AND_QR_CODE_PLUGIN_DIR' ) ) {
    define( 'COPY_LINK_AND_QR_CODE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'COPY_LINK_AND_QR_CODE_PLUGIN_URL' ) ) {
    define( 'COPY_LINK_AND_QR_CODE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


if ( ! defined( 'COPY_LINK_AND_QR_CODE_BASENAME' ) ) {
    define( 'COPY_LINK_AND_QR_CODE_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * include classes
 */
require_once COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'includes/class-clqrc-assets.php';
require_once COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'includes/class-clqrc-frontend.php';
require_once COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'includes/class-clqrc-admin.php';
require_once COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'includes/class-clqrc-block.php';
require_once COPY_LINK_AND_QR_CODE_PLUGIN_DIR . 'includes/class-clqrc-privacy.php';

/**
 * activation / deactivation
 */
register_activation_hook( __FILE__, 'copy_link_and_qr_code_activate' );
register_deactivation_hook( __FILE__, 'copy_link_and_qr_code_deactivate' );

/**
 * Default plugin settings
 *
 * @return array
 */
function copy_link_and_qr_code_default_settings() {
    return array(
        'post_types' => array( 'post', 'page' ),
        'position'   => 'after',
    );
}

/**
 * Activation handler — add defaults if not present.
 */
function copy_link_and_qr_code_activate() {
    if ( ! get_option( 'copy_link_and_qr_code_settings' ) ) {
        add_option( 'copy_link_and_qr_code_settings', copy_link_and_qr_code_default_settings() );
    }
}

/**
 * Add plugin settings link in Plugins page.
 */
add_filter( 'plugin_action_links_' . COPY_LINK_AND_QR_CODE_BASENAME, 'copy_link_and_qr_code_link' );
function copy_link_and_qr_code_link( $links ) {
    $links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=copy-link-and-qr-code' ) ) . '">' . esc_html__( 'Settings', 'copy-link-and-qr-code' ) . '</a>';
    return $links;
}


/**
 * Deactivation handler — keep options (admin may choose uninstall to remove).
 */
function copy_link_and_qr_code_deactivate() {
    // no-op for now. Use uninstall.php to remove options if desired.
}

add_action( 'plugins_loaded', function() {

    if ( class_exists( 'CLQRC_Assets' ) ) {
        new CLQRC_Assets();
    }
    if ( class_exists( 'CLQRC_Frontend' ) ) {
        new CLQRC_Frontend();
    }
    if ( class_exists( 'CLQRC_Admin' ) ) {
        new CLQRC_Admin();
    }
    if ( class_exists( 'CLQRC_Block' ) ) {
        new CLQRC_Block();
    }
    if ( class_exists( 'CLQRC_Privacy' ) ) {
        new CLQRC_Privacy();
    }
    
} );
