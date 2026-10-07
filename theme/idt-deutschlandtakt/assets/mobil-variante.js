/**
 * Schalter „Mobil-Variante verwenden" in der Seitenleiste des Bildblocks.
 *
 * Build-frei wie assets/blocks.js. Der Schalter setzt bzw. entfernt nur die
 * Klasse `mobil-variante` im Feld „Zusätzliche CSS-Klasse(n)" — ausgewertet
 * wird sie serverseitig in inc/mobil-variante.php. Ist der Schalter an, fragt
 * das Script über /idt/v1/mobil-variante nach, ob die passende `-mobil`-Datei
 * in der Mediathek liegt, und zeigt dazu einen kurzen Hinweis.
 */
( function ( wp ) {
	if ( ! wp || ! wp.hooks || ! wp.compose || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.apiFetch || ! wp.url ) {
		return;
	}

	var cfg               = window.IDT_MOBIL || {};
	var cls               = cfg.className || 'mobil-variante';
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var useState          = wp.element.useState;
	var useEffect         = wp.element.useEffect;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var C                 = wp.components;
	var __                = wp.i18n.__;
	var sprintf           = wp.i18n.sprintf;

	function classList( className ) {
		return ( className || '' ).split( /\s+/ ).filter( Boolean );
	}

	/** Fragt den Server, ob es zur Bilddatei eine Mobil-Datei gibt. */
	function useMobilLookup( active, id, url ) {
		var state    = useState( null );
		var result   = state[ 0 ];
		var setResult = state[ 1 ];

		useEffect( function () {
			if ( ! active || ( ! id && ! url ) ) {
				setResult( null );
				return;
			}
			var current = true;
			setResult( { loading: true } );
			wp.apiFetch( {
				path: wp.url.addQueryArgs( '/idt/v1/mobil-variante', { id: id || 0, url: url || '' } ),
			} ).then( function ( res ) {
				if ( current ) {
					setResult( res );
				}
			} ).catch( function () {
				if ( current ) {
					setResult( null );
				}
			} );
			return function () {
				current = false;
			};
		}, [ active, id, url ] );

		return result;
	}

	function MobilPanel( props ) {
		var attrs   = props.attributes;
		var list    = classList( attrs.className );
		var active  = list.indexOf( cls ) !== -1;
		var lookup  = useMobilLookup( active, attrs.id, attrs.url );

		function toggle( on ) {
			var next = list.filter( function ( c ) {
				return c !== cls;
			} );
			if ( on ) {
				next.push( cls );
			}
			props.setAttributes( { className: next.length ? next.join( ' ' ) : undefined } );
		}

		var notice = null;
		if ( active && lookup && ! lookup.loading ) {
			notice = lookup.found
				? el( 'p', { className: 'components-base-control__help' },
					sprintf( __( 'Gefunden: %s', 'idt' ), lookup.url.split( '/' ).pop() ) )
				: el( C.Notice, { status: 'warning', isDismissible: false },
					lookup.expected
						? sprintf( __( 'Keine Datei „%s“ in der Mediathek gefunden — angezeigt wird überall das normale Bild.', 'idt' ), lookup.expected )
						: __( 'Zu diesem Bild lässt sich kein Name für die Mobil-Datei bilden.', 'idt' ) );
		}

		return el( InspectorControls, null,
			el( C.PanelBody, { title: __( 'Mobil-Variante', 'idt' ), initialOpen: active },
				el( C.ToggleControl, {
					label: __( 'Mobil-Variante verwenden', 'idt' ),
					help: sprintf(
						__( 'Bis %d px Bildschirmbreite wird stattdessen die Datei mit dem Zusatz „-mobil“ gezeigt (grafik.png → grafik-mobil.png, gleicher Upload-Ordner).', 'idt' ),
						cfg.breakpoint || 700
					),
					checked: active,
					onChange: toggle,
				} ),
				notice
			)
		);
	}

	var withMobilPanel = wp.compose.createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( 'core/image' !== props.name ) {
				return el( BlockEdit, props );
			}
			return el( Fragment, null,
				el( BlockEdit, props ),
				props.isSelected ? el( MobilPanel, props ) : null
			);
		};
	}, 'withIdtMobilPanel' );

	wp.hooks.addFilter( 'editor.BlockEdit', 'idt/mobil-variante', withMobilPanel );
} )( window.wp );
