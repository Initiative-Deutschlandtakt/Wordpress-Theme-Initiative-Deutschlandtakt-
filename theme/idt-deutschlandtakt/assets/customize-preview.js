/**
 * Live-Vorschau im Customizer.
 *
 * Setzt die im Bereich „Website-Identität" eingestellte Logo-Höhe sofort im
 * Vorschaurahmen — ohne Neuladen. Gerendert wird sie über dasselbe Token wie
 * im Stylesheet (--header-logo-h), damit Vorschau und Frontend identisch sind.
 */
( function ( api ) {
	if ( ! api ) { return; }

	api( 'idt_header_logo_height', function ( setting ) {
		setting.bind( function ( value ) {
			var px = parseInt( value, 10 );
			if ( isNaN( px ) ) { return; }
			px = Math.min( 120, Math.max( 20, px ) );
			document.documentElement.style.setProperty( '--header-logo-h', px + 'px' );
		} );
	} );
}( window.wp && window.wp.customize ) );
