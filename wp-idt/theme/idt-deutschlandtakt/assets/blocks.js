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
} )( window.wp );
