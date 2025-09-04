<?php
/**
 * Uninstall script for Copy Link and QR Code.
 *
 * @package CopyLinkQR
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'copy_link_and_qr_code_settings' );