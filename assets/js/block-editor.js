( function ( wp ) {
	'use strict';
	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	wp.blocks.registerBlockType( 'clqr/copy-link-qr', {
		title: __( 'Copy Link and QR Code', 'copy-link-qr' ),
		//icon: 'shortcode',
		icon: el(
		    'svg',
		    { width: 20, height: 20, viewBox: '0 0 512 512', fill: 'currentColor' },
		    el('path', { d: 'M0 224h192V32H0v192zM64 96h64v64H64V96zm192-64v192h192V32H256zm128 128h-64V96h64v64zM0 480h192V288H0v192zm64-128h64v64H64v-64zm352-64h32v128h-96v-32h-32v96h-64V288h96v32h64v-32zm0 160h32v32h-32v-32zm-64 0h32v32h-32v-32z' })
		),
		category: 'widgets',
		edit: function () {
			return el( 'div', { className: 'clqr-block-placeholder' },
				el( 'p', null, __( 'Copy Link and QR Code — dynamic block. It will display buttons on the front-end.', 'copy-link-qr' ) )
			);
		},
		save: function () {
			// dynamic - saved by render_callback
			return null;
		}
	} );
} )( window.wp );
