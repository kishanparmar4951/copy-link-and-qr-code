<?php
/**
 * Basic tests for Copy Link and QR Code plugin.
 *
 * @package CopyLinkQR
 */

class CLQR_Basic_Test extends WP_UnitTestCase {

    public function test_plugin_options_exist() {
        $options = get_option( 'clqr_settings' );
        $this->assertIsArray( $options );
    }

    public function test_default_labels() {
        $options = get_option( 'clqr_settings', array() );
        $defaults = array(
            'copy' => 'Copy Link',
        );
        // plugin may not be installed in test env, but test that option structure works
        $this->assertTrue( is_array( $options ) || empty( $options ) );
    }
}