/**
 * Registriert die IDT-Stilelemente als native, dynamische Blöcke.
 *
 * Build-frei: nur globale wp.*-Pakete, keine JSX. Die Feld-Metadaten kommen aus
 * window.IDT_BLOCKS (per wp_localize_script aus inc/blocks.php). Bearbeitet wird
 * über Formularfelder in der Seitenleiste; die Vorschau rendert serverseitig
 * (ServerSideRender) über dieselben Shortcode-Funktionen wie das Frontend.
 */
( function ( wp ) {
	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.serverSideRender ) {
		return;
	}

	var defs              = window.IDT_BLOCKS || {};
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps      = wp.blockEditor.useBlockProps;
	var C                 = wp.components;
	var ServerSideRender  = wp.serverSideRender;

	/** Erzeugt das passende Sidebar-Control für ein Feld. */
	function control( field, attrs, setAttributes ) {
		var key      = field.key;
		var onChange = function ( value ) {
			var update = {};
			update[ key ] = value;
			setAttributes( update );
		};

		if ( 'textarea' === field.type ) {
			return el( C.TextareaControl, { key: key, label: field.label, value: attrs[ key ], onChange: onChange } );
		}
		if ( 'select' === field.type ) {
			return el( C.SelectControl, { key: key, label: field.label, value: attrs[ key ], options: field.options, onChange: onChange } );
		}
		if ( 'toggle' === field.type ) {
			return el( C.ToggleControl, { key: key, label: field.label, checked: !! attrs[ key ], onChange: onChange } );
		}
		if ( 'range' === field.type ) {
			return el( C.RangeControl, { key: key, label: field.label, value: attrs[ key ], min: field.min, max: field.max, onChange: onChange } );
		}
		/* Farbwähler: Markenfarben als Vorschläge, freie Farbwahl bleibt möglich.
		 * Leert der Editor das Feld, greift wieder die Vorgabefarbe des Feldes. */
		if ( 'color' === field.type ) {
			return el( C.BaseControl, { key: key, label: field.label },
				el( C.ColorPalette, {
					colors:    field.palette || [],
					value:     attrs[ key ],
					clearable: false,
					onChange:  function ( value ) { onChange( value || field.default ); }
				} )
			);
		}
		return el( C.TextControl, { key: key, label: field.label, value: attrs[ key ], onChange: onChange } );
	}

	Object.keys( defs ).forEach( function ( name ) {
		var def       = defs[ name ];
		var blockName = 'idt/' + name;

		registerBlockType( blockName, {
			apiVersion: 2,
			title:      def.title,
			icon:       def.icon,
			category:   'idt',
			keywords:   def.keywords,
			attributes: def.attributes,
			edit: function ( props ) {
				var attrs    = props.attributes;
				var controls = def.fields.map( function ( field ) {
					return control( field, attrs, props.setAttributes );
				} );

				return el( Fragment, {},
					el( InspectorControls, {},
						el( C.PanelBody, { title: def.title, initialOpen: true }, controls )
					),
					el( 'div', useBlockProps(),
						el( ServerSideRender, { block: blockName, attributes: attrs } )
					)
				);
			},
			save: function () { return null; }
		} );
	} );

	/* ------------------------------------------------------------------
	 * Karten-Raster — Container-Block mit InnerBlocks.
	 *
	 * Statischer Block: gespeichert wird nur der Raster-Rahmen, die Karten
	 * darin bleiben eigenständige (dynamische) Blöcke. Bearbeitet wird also
	 * jede Karte einzeln, dargestellt werden sie gemeinsam im Raster.
	 * ---------------------------------------------------------------- */
	var cards = window.IDT_CARDS;
	if ( ! cards ) { return; }

	var InnerBlocks         = wp.blockEditor.InnerBlocks;
	var useInnerBlocksProps = wp.blockEditor.useInnerBlocksProps || wp.blockEditor.__experimentalUseInnerBlocksProps;
	var ALLOWED             = [ 'idt/concept', 'idt/newscard', 'idt/card' ];
	var TEMPLATE            = [ [ 'idt/concept', {} ], [ 'idt/concept', {} ], [ 'idt/concept', {} ] ];

	/** Rahmenklassen aus der gewählten Spaltenzahl — in edit und save identisch. */
	function cardsClass( attrs ) {
		return 'idt-cards idt-cards--' + ( attrs.cols || '3' );
	}

	registerBlockType( 'idt/kartenraster', {
		apiVersion: 2,
		title:      cards.title,
		icon:       'grid-view',
		category:   'idt',
		keywords:   [ 'raster', 'grid', 'karten', 'cards', 'spalten', 'dt' ],
		attributes: { cols: { type: 'string', default: '3' } },
		edit: function ( props ) {
			var inspector = el( InspectorControls, {},
				el( C.PanelBody, { title: cards.title, initialOpen: true },
					el( C.SelectControl, {
						label:    cards.label,
						value:    props.attributes.cols,
						options:  cards.options,
						onChange: function ( value ) { props.setAttributes( { cols: value } ); }
					} ),
					el( 'p', { style: { fontSize: '12px', color: '#757575' } }, cards.hint )
				)
			);

			var blockProps = useBlockProps( { className: cardsClass( props.attributes ) } );
			var inner      = { allowedBlocks: ALLOWED, template: TEMPLATE };

			/* useInnerBlocksProps legt die Raster-Klassen direkt auf die Liste der
			 * Kinder — nur so sieht der Editor aus wie das Frontend. */
			if ( useInnerBlocksProps ) {
				return el( Fragment, {}, inspector, el( 'div', useInnerBlocksProps( blockProps, inner ) ) );
			}
			return el( Fragment, {}, inspector, el( 'div', blockProps, el( InnerBlocks, inner ) ) );
		},
		save: function ( props ) {
			var blockProps = useBlockProps.save( { className: cardsClass( props.attributes ) } );
			if ( useInnerBlocksProps && useInnerBlocksProps.save ) {
				return el( 'div', useInnerBlocksProps.save( blockProps ) );
			}
			return el( 'div', blockProps, el( InnerBlocks.Content ) );
		}
	} );
} )( window.wp );
