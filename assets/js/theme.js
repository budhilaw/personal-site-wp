/**
 * Theme behaviour: color-scheme toggle and the mobile navigation.
 * Kept dependency-free and defer-loaded. The initial theme is set by a tiny
 * inline script in the document head to avoid a flash of the wrong scheme.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var STORAGE_KEY = 'personal-site-theme';

	function current() {
		return root.getAttribute( 'data-theme' ) === 'dark' ? 'dark' : 'light';
	}

	function setTheme( theme ) {
		root.setAttribute( 'data-theme', theme );
		try {
			localStorage.setItem( STORAGE_KEY, theme );
		} catch ( e ) {}
	}

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) {
			fn();
		} else {
			document.addEventListener( 'DOMContentLoaded', fn );
		}
	}

	ready( function () {
		var toggle = document.querySelector( '[data-theme-toggle]' );
		if ( toggle ) {
			toggle.setAttribute( 'aria-pressed', String( current() === 'dark' ) );
			toggle.addEventListener( 'click', function () {
				setTheme( current() === 'dark' ? 'light' : 'dark' );
				toggle.setAttribute( 'aria-pressed', String( current() === 'dark' ) );
			} );
		}

		var menuToggle = document.querySelector( '[data-menu-toggle]' );
		var nav = document.getElementById( 'primary-nav' );
		if ( menuToggle && nav ) {
			menuToggle.addEventListener( 'click', function () {
				var open = nav.getAttribute( 'data-open' ) === 'true';
				nav.setAttribute( 'data-open', String( ! open ) );
				menuToggle.setAttribute( 'aria-expanded', String( ! open ) );
			} );

			nav.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( 'a' ) ) {
					nav.setAttribute( 'data-open', 'false' );
					menuToggle.setAttribute( 'aria-expanded', 'false' );
				}
			} );

			document.addEventListener( 'keydown', function ( event ) {
				if ( event.key === 'Escape' && nav.getAttribute( 'data-open' ) === 'true' ) {
					nav.setAttribute( 'data-open', 'false' );
					menuToggle.setAttribute( 'aria-expanded', 'false' );
					menuToggle.focus();
				}
			} );
		}
	} );
} )();
