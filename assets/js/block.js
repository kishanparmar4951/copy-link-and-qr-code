/**
 * Simple editor script for the block.
 * Shows a non-functional preview in the block editor.
 */

( function ( wp ) {
	'use strict';
	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	wp.blocks.registerBlockType( 'copy-link-and-qr-code/block', {
		title: __( 'Copy Link & QR', 'copy-link-and-qr-code' ),
		icon: 'share',
		category: 'widgets',
		edit: function () {
			return el( 'div', { className: 'clqrc-block' },
				el( 'strong', null, __( 'Copy Link & QR', 'copy-link-and-qr-code' ) ),
				el( 'p', null, __( 'This block will render Copy Link and QR buttons on the front-end.', 'copy-link-and-qr-code' ) )
			);
		},
		save: function () {
			// Server-side render; saved content is handled by render_callback.
			return null;
		}
	} );
} )( window.wp );
