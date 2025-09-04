/**
 * Frontend JS for Copy Link & QR Code
 * - Handles copy-to-clipboard
 * - Generates QR (SVG) using qrcode-generator library if available
 *
 * Requires: qrcode-generator (qrcode function)
 */

( function () {
	'use strict';

	/**
	 * Create SVG markup using qrcode-generator library.
	 * Expects qrcode() function (qrcode-generator) in global scope.
	 *
	 * @param {string} text Text to encode.
	 * @param {number} size Pixel size (square).
	 * @param {string} ecl Error correction level (L, M, Q, H).
	 * @param {number} margin Quiet zone (modules).
	 * @returns {string} SVG markup string (or fallback message).
	 */
	function createQrSvg( text, size, ecl, margin ) {
		if ( typeof qrcode !== 'function' ) {
			return '<div class="clqrc-qr-error">' + 'QR library not loaded' + '</div>';
		}

		try {
			// Create QR model
			var qr = qrcode( 0, ecl || 'M' );
			qr.addData( text );
			qr.make();

			var moduleCount = qr.getModuleCount();
			var cellSize = Math.max(1, Math.floor( size / moduleCount ));
			// qrcode-generator exposes createSvgTag helper in many builds.
			if ( typeof qr.createSvgTag === 'function' ) {
				return qr.createSvgTag( cellSize, margin || 2 );
			}

			// Fallback: build SVG manually
			var svgSize = ( moduleCount + ( margin || 2 ) * 2 ) * cellSize;
			var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + svgSize + '" height="' + svgSize + '" viewBox="0 0 ' + svgSize + ' ' + svgSize + '">';
			svg += '<rect width="100%" height="100%" fill="#ffffff"/>';
			svg += '<g fill="#000000">';
			for ( var r = 0; r < moduleCount; r++ ) {
				for ( var c = 0; c < moduleCount; c++ ) {
					if ( qr.isDark( r, c ) ) {
						var x = ( c + ( margin || 2 ) ) * cellSize;
						var y = ( r + ( margin || 2 ) ) * cellSize;
						svg += '<rect x="' + x + '" y="' + y + '" width="' + cellSize + '" height="' + cellSize + '"/>';
					}
				}
			}
			svg += '</g></svg>';
			return svg;
		} catch ( e ) {
			return '<div class="clqrc-qr-error">' + 'QR generation failed' + '</div>';
		}
	}

	/**
	 * Convert SVG string to data URL for download.
	 *
	 * @param {string} svg
	 * @return {string}
	 */
	function svgToDataUrl( svg ) {
		return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent( svg );
	}

	/**
	 * Attach handlers to all button blocks on the page.
	 */
	function init() {
		var containers = document.querySelectorAll( '.clqrc-buttons' );
		if ( ! containers || ! containers.length ) {
			return;
		}

		Array.prototype.forEach.call( containers, function ( container ) {
			var copyBtn = container.querySelector( '.clqrc-copy' );
			var qrBtn = container.querySelector( '.clqrc-qr' );
			var popup = container.querySelector( '.clqrc-qr-popup' );

			// Ensure popup element exists
			if ( ! popup ) {
				popup = document.createElement( 'div' );
				popup.className = 'clqrc-qr-popup';
				container.appendChild( popup );
			}

			// Copy handler
			if ( copyBtn ) {
				copyBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var url = copyBtn.getAttribute( 'data-url' ) || window.location.href;
					// Use Clipboard API if available
					if ( navigator.clipboard && navigator.clipboard.writeText ) {
						navigator.clipboard.writeText( url ).then( function () {
							// simple feedback
							var prev = copyBtn.innerHTML;
							copyBtn.innerHTML = CLQRC && CLQRC.copy_text ? CLQRC.copy_text : 'Copied';
							setTimeout( function () {
								copyBtn.innerHTML = prev;
							}, 1500 );
						} );
					} else {
						// Fallback: textarea
						var ta = document.createElement( 'textarea' );
						ta.value = url;
						document.body.appendChild( ta );
						ta.select();
						try {
							document.execCommand( 'copy' );
							var prev = copyBtn.innerHTML;
							copyBtn.innerHTML = 'Copied';
							setTimeout( function () {
								copyBtn.innerHTML = prev;
							}, 1500 );
						} catch ( ex ) {
							alert( 'Copy failed. Please select and copy manually.' );
						}
						ta.remove();
					}
				} );
			}

			// QR handler
			if ( qrBtn ) {
				qrBtn.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					var url = qrBtn.getAttribute( 'data-url' ) || window.location.href;

					// Toggle popup
					if ( popup.getAttribute( 'data-visible' ) === '1' ) {
						popup.style.display = 'none';
						popup.setAttribute( 'data-visible', '0' );
						popup.innerHTML = '';
						return;
					}

					// Build content
					var svg = createQrSvg( url, 256, 'M', 2 ); // defaults
					var inner = document.createElement( 'div' );
					inner.className = 'clqrc-qr-inner';
					inner.innerHTML = svg;

					// download controls
					var controls = document.createElement( 'div' );
					controls.style.marginTop = '8px';

					var dlSvg = document.createElement( 'a' );
					dlSvg.href = svgToDataUrl( svg );
					dlSvg.download = 'qr.svg';
					dlSvg.textContent = 'Download SVG';
					dlSvg.style.marginRight = '8px';
					controls.appendChild( dlSvg );

					// PNG download via canvas
					var dlPng = document.createElement( 'a' );
					dlPng.href = '#';
					dlPng.textContent = 'Download PNG';
					dlPng.addEventListener( 'click', function ( ev ) {
						ev.preventDefault();
						// Create canvas and draw
						try {
							var canvas = document.createElement( 'canvas' );
							// Determine size from svg width attribute if available
							var size = 256;
							canvas.width = size;
							canvas.height = size;
							var ctx = canvas.getContext( '2d' );
							var img = new Image();
							img.onload = function () {
								// white background
								ctx.fillStyle = '#ffffff';
								ctx.fillRect( 0, 0, canvas.width, canvas.height );
								ctx.drawImage( img, 0, 0, canvas.width, canvas.height );
								var pngUrl = canvas.toDataURL( 'image/png' );
								var link = document.createElement( 'a' );
								link.href = pngUrl;
								link.download = 'qr.png';
								document.body.appendChild( link );
								link.click();
								link.remove();
							};
							img.onerror = function () {
								alert( 'PNG export failed.' );
							};
							img.src = svgToDataUrl( svg );
						} catch ( ex ) {
							alert( 'PNG export failed.' );
						}
					} );
					controls.appendChild( dlPng );

					var close = document.createElement( 'div' );
					close.className = 'clqrc-qr-close';
					close.textContent = 'Close';
					close.addEventListener( 'click', function () {
						popup.style.display = 'none';
						popup.setAttribute( 'data-visible', '0' );
						popup.innerHTML = '';
					} );

					popup.innerHTML = '';
					popup.appendChild( inner );
					popup.appendChild( controls );
					popup.appendChild( close );
					popup.style.display = 'inline-block';
					popup.setAttribute( 'data-visible', '1' );
				} );
			}
		} );
	}

	// Initialize on DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();