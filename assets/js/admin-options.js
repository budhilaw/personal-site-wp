/**
 * Theme Options screen: tab switching, repeatable rows, and the media picker.
 */
( function () {
	'use strict';

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) {
			fn();
		} else {
			document.addEventListener( 'DOMContentLoaded', fn );
		}
	}

	ready( function () {
		// --- Tabs ------------------------------------------------------------
		var tabs = Array.prototype.slice.call( document.querySelectorAll( '[data-ps-tab]' ) );
		var panels = Array.prototype.slice.call( document.querySelectorAll( '[data-ps-panel]' ) );
		var activeField = document.querySelector( '[data-ps-active-tab]' );

		function activate( slug ) {
			tabs.forEach( function ( t ) {
				t.classList.toggle( 'is-active', t.dataset.psTab === slug );
			} );
			panels.forEach( function ( p ) {
				var on = p.dataset.psPanel === slug;
				p.classList.toggle( 'is-active', on );
				p.hidden = ! on;
			} );
			if ( activeField ) {
				activeField.value = slug;
			}
			try {
				localStorage.setItem( 'psActiveTab', slug );
			} catch ( e ) {}
		}

		tabs.forEach( function ( t ) {
			t.addEventListener( 'click', function () {
				activate( t.dataset.psTab );
			} );
		} );

		// Restore last tab unless the server already set one via ?tab=.
		var params = new URLSearchParams( window.location.search );
		if ( ! params.get( 'tab' ) ) {
			try {
				var saved = localStorage.getItem( 'psActiveTab' );
				if ( saved && document.querySelector( '[data-ps-panel="' + saved + '"]' ) ) {
					activate( saved );
				}
			} catch ( e ) {}
		}

		// --- Repeaters -------------------------------------------------------
		document.querySelectorAll( '[data-ps-repeater]' ).forEach( function ( rep ) {
			var list = rep.querySelector( '.ps-repeater__items' );
			var tpl = rep.querySelector( '[data-ps-template]' );
			var addBtn = rep.querySelector( '[data-ps-add]' );

			if ( addBtn && tpl && list ) {
				addBtn.addEventListener( 'click', function () {
					var html = tpl.innerHTML.replace( /__index__/g, 'new' + Date.now() );
					var wrap = document.createElement( 'div' );
					wrap.innerHTML = html.trim();
					var row = wrap.firstChild;
					list.appendChild( row );
					var first = row.querySelector( 'input, textarea' );
					if ( first ) {
						first.focus();
					}
				} );
			}

			rep.addEventListener( 'click', function ( event ) {
				var remove = event.target.closest( '[data-ps-remove]' );
				if ( remove ) {
					var row = remove.closest( '[data-ps-row]' );
					if ( row ) {
						row.parentNode.removeChild( row );
					}
				}
			} );
		} );

		// --- Media picker ----------------------------------------------------
		if ( window.wp && window.wp.media ) {
			document.querySelectorAll( '[data-ps-media]' ).forEach( function ( field ) {
				var input = field.querySelector( '[data-ps-media-input]' );
				var preview = field.querySelector( '.ps-media__preview' );
				var selectBtn = field.querySelector( '[data-ps-media-select]' );
				var removeBtn = field.querySelector( '[data-ps-media-remove]' );
				var frame;

				if ( selectBtn ) {
					selectBtn.addEventListener( 'click', function () {
						if ( ! frame ) {
							frame = wp.media( {
								title: ( window.personalSiteAdmin || {} ).choose || 'Choose image',
								button: { text: ( window.personalSiteAdmin || {} ).use || 'Use this image' },
								library: { type: 'image' },
								multiple: false,
							} );
							frame.on( 'select', function () {
								var att = frame.state().get( 'selection' ).first().toJSON();
								input.value = parseInt( att.id, 10 ) || '';
								var url = ( att.sizes && att.sizes.medium ) ? att.sizes.medium.url : att.url;
								var img = document.createElement( 'img' );
								img.src = url;
								img.alt = '';
								preview.replaceChildren( img );
								field.classList.add( 'has-image' );
							} );
						}
						frame.open();
					} );
				}

				if ( removeBtn ) {
					removeBtn.addEventListener( 'click', function () {
						input.value = '';
						preview.replaceChildren();
						field.classList.remove( 'has-image' );
					} );
				}
			} );
		}
	} );
} )();
