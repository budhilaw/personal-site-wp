/**
 * Editor definitions for the Personal Site homepage blocks.
 * Written without JSX so it runs straight from the browser (no build step).
 * Each block is dynamic — the front-end markup comes from PHP render callbacks,
 * so `save` returns null (or InnerBlocks.Content for the Focus container).
 *
 * The edit UIs add a labeled frame and (for query blocks) a skeleton preview so
 * each section reads as a distinct, designed block in the canvas.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var RichText = blockEditor.RichText;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;

	function setter( props, key ) {
		return function ( value ) {
			var update = {};
			update[ key ] = value;
			props.setAttributes( update );
		};
	}

	// A small label bar shown at the top of every block in the editor.
	function bar( label ) {
		return el(
			'div',
			{ className: 'ps-edit__bar', contentEditable: false },
			el( 'span', { className: 'ps-edit__chip' }, label ),
			el( 'span', { className: 'ps-edit__tag' }, __( 'Personal Site', 'personal-site' ) )
		);
	}

	// Skeleton card grid used to preview the query blocks.
	function skeleton() {
		function card() {
			return el(
				'div',
				{ className: 'ps-skeleton__card' },
				el( 'span', { className: 'ps-skeleton__media' } ),
				el( 'span', { className: 'ps-skeleton__line ps-skeleton__line--title' } ),
				el( 'span', { className: 'ps-skeleton__line' } )
			);
		}
		return el( 'div', { className: 'ps-skeleton', contentEditable: false }, card(), card(), card() );
	}

	/* ---- Hero ---- */
	blocks.registerBlockType( 'personal-site/hero', {
		title: __( 'Hero', 'personal-site' ),
		description: __( 'Large intro with a heading, text, and two buttons.', 'personal-site' ),
		category: 'personal-site',
		icon: 'megaphone',
		supports: { html: false, multiple: false, reusable: false },
		attributes: {
			heading: { type: 'string', default: __( 'I build things for the web.', 'personal-site' ) },
			text: { type: 'string', default: '' },
			primaryLabel: { type: 'string', default: __( 'View work', 'personal-site' ) },
			primaryUrl: { type: 'string', default: '' },
			secondaryLabel: { type: 'string', default: __( 'Read writing', 'personal-site' ) },
			secondaryUrl: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var a = props.attributes;
			var bp = useBlockProps( { className: 'ps-edit ps-edit--hero' } );
			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Buttons', 'personal-site' ) },
						el( TextControl, { label: __( 'Primary label', 'personal-site' ), value: a.primaryLabel, onChange: setter( props, 'primaryLabel' ) } ),
						el( TextControl, { label: __( 'Primary URL (blank = portfolio)', 'personal-site' ), value: a.primaryUrl, onChange: setter( props, 'primaryUrl' ) } ),
						el( TextControl, { label: __( 'Secondary label', 'personal-site' ), value: a.secondaryLabel, onChange: setter( props, 'secondaryLabel' ) } ),
						el( TextControl, { label: __( 'Secondary URL (blank = blog)', 'personal-site' ), value: a.secondaryUrl, onChange: setter( props, 'secondaryUrl' ) } )
					)
				),
				el(
					'div',
					bp,
					bar( __( 'Hero', 'personal-site' ) ),
					el(
						'div',
						{ className: 'ps-edit__body' },
						el( RichText, { tagName: 'h1', className: 'hero__title', value: a.heading, onChange: setter( props, 'heading' ), placeholder: __( 'Heading', 'personal-site' ) } ),
						el( RichText, { tagName: 'p', className: 'hero__text', value: a.text, onChange: setter( props, 'text' ), placeholder: __( 'Intro text', 'personal-site' ) } ),
						el(
							'div',
							{ className: 'ps-edit__buttons', contentEditable: false },
							el( 'span', { className: 'ps-edit__btn' }, a.primaryLabel || __( 'View work', 'personal-site' ) ),
							el( 'span', { className: 'ps-edit__btn ps-edit__btn--ghost' }, a.secondaryLabel || __( 'Read writing', 'personal-site' ) )
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---- Focus item (child) ---- */
	blocks.registerBlockType( 'personal-site/focus-item', {
		title: __( 'Focus item', 'personal-site' ),
		category: 'personal-site',
		icon: 'minus',
		parent: [ 'personal-site/focus' ],
		supports: { html: false },
		attributes: {
			title: { type: 'string', default: '' },
			text: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var a = props.attributes;
			var bp = useBlockProps( { className: 'focus-item' } );
			return el(
				'div',
				bp,
				el( RichText, { tagName: 'h3', className: 'focus-item__title', value: a.title, onChange: setter( props, 'title' ), placeholder: __( 'Title', 'personal-site' ) } ),
				el( RichText, { tagName: 'p', className: 'focus-item__text', value: a.text, onChange: setter( props, 'text' ), placeholder: __( 'Short description', 'personal-site' ) } )
			);
		},
		save: function () {
			return null;
		},
	} );

	/* ---- Focus (container) ---- */
	blocks.registerBlockType( 'personal-site/focus', {
		title: __( 'Focus', 'personal-site' ),
		description: __( 'A heading and a grid of focus items.', 'personal-site' ),
		category: 'personal-site',
		icon: 'screenoptions',
		supports: { html: false, multiple: false },
		attributes: {
			eyebrow: { type: 'string', default: __( 'Focus', 'personal-site' ) },
			heading: { type: 'string', default: __( 'What I focus on', 'personal-site' ) },
		},
		edit: function ( props ) {
			var a = props.attributes;
			var bp = useBlockProps( { className: 'ps-edit ps-edit--focus' } );
			return el(
				'div',
				bp,
				bar( __( 'Focus', 'personal-site' ) ),
				el(
					'div',
					{ className: 'ps-edit__body' },
					el( RichText, { tagName: 'p', className: 'ps-edit__eyebrow', value: a.eyebrow, onChange: setter( props, 'eyebrow' ), placeholder: __( 'Eyebrow', 'personal-site' ) } ),
					el( RichText, { tagName: 'h2', className: 'ps-edit__heading', value: a.heading, onChange: setter( props, 'heading' ), placeholder: __( 'Heading', 'personal-site' ) } ),
					el(
						'div',
						{ className: 'focus-grid' },
						el( InnerBlocks, {
							allowedBlocks: [ 'personal-site/focus-item' ],
							template: [ [ 'personal-site/focus-item' ], [ 'personal-site/focus-item' ], [ 'personal-site/focus-item' ] ],
							templateLock: false,
						} )
					)
				)
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );

	/* ---- Query blocks ---- */
	function queryBlock( name, settings ) {
		blocks.registerBlockType( name, {
			title: settings.title,
			description: settings.description,
			category: 'personal-site',
			icon: settings.icon,
			supports: { html: false, multiple: false },
			attributes: {
				eyebrow: { type: 'string', default: settings.eyebrow },
				heading: { type: 'string', default: settings.heading },
				count: { type: 'number', default: settings.count },
			},
			edit: function ( props ) {
				var a = props.attributes;
				var bp = useBlockProps( { className: 'ps-edit ps-edit--query' } );
				return el(
					Fragment,
					{},
					el(
						InspectorControls,
						{},
						el(
							PanelBody,
							{ title: __( 'Settings', 'personal-site' ) },
							el( RangeControl, { label: settings.countLabel, value: a.count, onChange: setter( props, 'count' ), min: 1, max: 12 } )
						)
					),
					el(
						'div',
						bp,
						bar( settings.title ),
						el(
							'div',
							{ className: 'ps-edit__body' },
							el( RichText, { tagName: 'p', className: 'ps-edit__eyebrow', value: a.eyebrow, onChange: setter( props, 'eyebrow' ), placeholder: __( 'Eyebrow', 'personal-site' ) } ),
							el( RichText, { tagName: 'h2', className: 'ps-edit__heading', value: a.heading, onChange: setter( props, 'heading' ), placeholder: __( 'Heading', 'personal-site' ) } ),
							skeleton(),
							el( 'p', { className: 'ps-edit__note', contentEditable: false }, settings.note + ' (' + a.count + ')' )
						)
					)
				);
			},
			save: function () {
				return null;
			},
		} );
	}

	queryBlock( 'personal-site/selected-work', {
		title: __( 'Selected work', 'personal-site' ),
		description: __( 'A grid of recent portfolio projects.', 'personal-site' ),
		icon: 'portfolio',
		eyebrow: __( 'Selected work', 'personal-site' ),
		heading: __( 'Recent projects', 'personal-site' ),
		count: 4,
		countLabel: __( 'Projects to show', 'personal-site' ),
		note: __( 'Latest portfolio projects appear here on the site.', 'personal-site' ),
	} );

	queryBlock( 'personal-site/recent-posts', {
		title: __( 'Recent posts', 'personal-site' ),
		description: __( 'A list of recent blog posts.', 'personal-site' ),
		icon: 'admin-post',
		eyebrow: __( 'Writing', 'personal-site' ),
		heading: __( 'Recent writing', 'personal-site' ),
		count: 5,
		countLabel: __( 'Posts to show', 'personal-site' ),
		note: __( 'Latest posts appear here on the site.', 'personal-site' ),
	} );

	/* ---- CTA ---- */
	blocks.registerBlockType( 'personal-site/cta', {
		title: __( 'Call to action', 'personal-site' ),
		description: __( 'A closing heading with a button.', 'personal-site' ),
		category: 'personal-site',
		icon: 'email',
		supports: { html: false, multiple: false },
		attributes: {
			heading: { type: 'string', default: __( 'Want to work together?', 'personal-site' ) },
			label: { type: 'string', default: __( 'Get in touch', 'personal-site' ) },
			url: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var a = props.attributes;
			var bp = useBlockProps( { className: 'ps-edit ps-edit--cta' } );
			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Button', 'personal-site' ) },
						el( TextControl, { label: __( 'Label', 'personal-site' ), value: a.label, onChange: setter( props, 'label' ) } ),
						el( TextControl, { label: __( 'URL (blank = email or Contact page)', 'personal-site' ), value: a.url, onChange: setter( props, 'url' ) } )
					)
				),
				el(
					'div',
					bp,
					bar( __( 'Call to action', 'personal-site' ) ),
					el(
						'div',
						{ className: 'ps-edit__body ps-edit__body--row' },
						el( RichText, { tagName: 'h2', className: 'ps-edit__heading', value: a.heading, onChange: setter( props, 'heading' ), placeholder: __( 'Heading', 'personal-site' ) } ),
						el( 'span', { className: 'ps-edit__btn', contentEditable: false }, a.label || __( 'Get in touch', 'personal-site' ) )
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
