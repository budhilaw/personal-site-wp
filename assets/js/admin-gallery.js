/**
 * Multi-image gallery picker for the Portfolio editor.
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
		var field = document.querySelector( '[data-ps-gallery]' );
		if ( ! field || ! window.wp || ! window.wp.media ) {
			return;
		}

		var items = field.querySelector( '[data-ps-gallery-items]' );
		var input = field.querySelector( '[data-ps-gallery-input]' );
		var addBtn = field.querySelector( '[data-ps-gallery-add]' );
		var labels = window.personalSiteGallery || {};
		var frame;

		function currentIds() {
			return input.value ? input.value.split( ',' ).filter( Boolean ) : [];
		}

		function sync() {
			var ids = Array.prototype.map.call(
				items.querySelectorAll( '[data-id]' ),
				function ( el ) {
					return el.getAttribute( 'data-id' );
				}
			);
			input.value = ids.join( ',' );
		}

		function addItem( id, url ) {
			var span = document.createElement( 'span' );
			span.className = 'ps-gallery__item';
			span.setAttribute( 'data-id', id );
			var img = document.createElement( 'img' );
			img.src = url;
			img.alt = '';
			var remove = document.createElement( 'button' );
			remove.type = 'button';
			remove.className = 'ps-gallery__remove';
			remove.setAttribute( 'data-ps-gallery-remove', '' );
			remove.setAttribute( 'aria-label', 'Remove' );
			remove.textContent = '×';
			span.appendChild( img );
			span.appendChild( remove );
			items.appendChild( span );
		}

		addBtn.addEventListener( 'click', function () {
			if ( ! frame ) {
				frame = wp.media( {
					title: labels.title || 'Select images',
					button: { text: labels.button || 'Add' },
					library: { type: 'image' },
					multiple: 'add',
				} );
				frame.on( 'select', function () {
					var existing = currentIds();
					frame.state().get( 'selection' ).toJSON().forEach( function ( att ) {
						if ( existing.indexOf( String( att.id ) ) !== -1 ) {
							return;
						}
						var url = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
						addItem( att.id, url );
					} );
					sync();
				} );
			}
			frame.open();
		} );

		items.addEventListener( 'click', function ( event ) {
			var remove = event.target.closest( '[data-ps-gallery-remove]' );
			if ( remove ) {
				var item = remove.closest( '[data-id]' );
				if ( item ) {
					item.parentNode.removeChild( item );
					sync();
				}
			}
		} );
	} );
} )();
