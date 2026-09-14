<?php
/**
 * Prüft die Blöcke, die ihre Metadaten in einer block.json mitbringen.
 *
 * Hilfsprogramm für bin/check-theme.sh. Die übrigen Blöcke des Themes stehen
 * in idt_blocks_config() und werden von den PHP-Prüfungen dort miterfasst; ein
 * Block mit eigener block.json liegt daneben und würde sonst durchrutschen.
 *
 * Aufruf:
 *   php bin/block-meta.php <themeverzeichnis>
 *
 * Ausgabe: eine Zeile je Beanstandung, leer wenn alles stimmt.
 * Rückgabewert 0 (auch bei Beanstandungen — die Bewertung macht der Aufrufer),
 * 2 bei falschem Aufruf.
 *
 * Geprüft wird, was beim Ändern erfahrungsgemäß auseinanderläuft:
 *  - Namensraum idt/ und Textdomain idt (CLAUDE.md, Konvention 3),
 *  - die in "render" genannte Datei ist vorhanden,
 *  - die Skript-Handles aus block.json werden im Theme auch registriert —
 *    ohne wp_register_script() sucht WordPress eine *.asset.php aus einem
 *    Build-Schritt, den dieses Theme nicht hat, und lädt am Ende nichts,
 *  - die Attribut-Vorgaben stimmen mit der PHP-Funktion überein, aus der
 *    dieselben Werte für den Shortcode kommen. Laufen sie auseinander, zeigt
 *    der Block etwas anderes als der Shortcode — genau die Art stiller
 *    Abweichung, vor der Konvention 2 warnt.
 *
 * @package idt
 */

$theme = $argv[1] ?? '';

if ( ! is_dir( $theme ) ) {
	fwrite( STDERR, "Aufruf: php bin/block-meta.php <themeverzeichnis>\n" );
	exit( 2 );
}

$problems = array();
$blocks   = glob( $theme . '/blocks/*/block.json' );

if ( ! $blocks ) {
	exit( 0 ); // Keine block.json-Blöcke — nichts zu prüfen.
}

/* Alles PHP des Themes einmal einlesen: darin wird nach den Skript-Handles
   und nach der Vorgabe-Funktion gesucht. */
$php = '';
$it  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme ) );
foreach ( $it as $file ) {
	if ( $file->isFile() && 'php' === strtolower( $file->getExtension() ) ) {
		$php .= file_get_contents( $file->getPathname() ) . "\n";
	}
}

foreach ( $blocks as $path ) {
	$rel  = 'blocks/' . basename( dirname( $path ) ) . '/block.json';
	$meta = json_decode( file_get_contents( $path ), true );

	if ( ! is_array( $meta ) ) {
		$problems[] = "$rel: kein gültiges JSON (" . json_last_error_msg() . ')';
		continue;
	}

	$name = $meta['name'] ?? '';
	if ( 0 !== strpos( $name, 'idt/' ) ) {
		$problems[] = "$rel: Block außerhalb des idt/-Namensraums: '$name'";
	}

	if ( 'idt' !== ( $meta['textdomain'] ?? '' ) ) {
		$problems[] = "$rel: Textdomain ist '" . ( $meta['textdomain'] ?? '' ) . "', erwartet 'idt'";
	}

	/* "render": "file:./render.php" */
	if ( isset( $meta['render'] ) ) {
		$file = preg_replace( '#^file:\./#', '', $meta['render'] );
		if ( ! file_exists( dirname( $path ) . '/' . $file ) ) {
			$problems[] = "$rel: die in \"render\" genannte Datei $file fehlt";
		}
	}

	foreach ( array( 'script', 'editorScript', 'viewScript' ) as $key ) {
		if ( ! isset( $meta[ $key ] ) || 0 === strpos( $meta[ $key ], 'file:' ) ) {
			continue;
		}
		$handle = $meta[ $key ];
		if ( false === strpos( $php, "'" . $handle . "'" ) ) {
			$problems[] = "$rel: Handle '$handle' ($key) wird nirgends mit wp_register_script() angemeldet";
		}
	}

	/* Attribut-Vorgaben ↔ idt_<block>_defaults(): nur prüfen, wenn es eine
	   solche Funktion gibt — nicht jeder Block braucht eine. */
	$slug = substr( $name, strlen( 'idt/' ) );
	$fn   = 'idt_' . $slug . '_defaults';
	if ( ! preg_match( '/function\s+' . preg_quote( $fn, '/' ) . '\s*\(.*?\n\}/s', $php, $m ) ) {
		continue;
	}
	$body = $m[0];

	foreach ( (array) ( $meta['attributes'] ?? array() ) as $attr => $spec ) {
		if ( ! array_key_exists( 'default', $spec ) ) {
			continue;
		}
		$value = $spec['default'];

		/* Erwartete Schreibweise in der PHP-Liste, z. B.
		   'autoplay' => true,  bzw.  'subzeile' => 'Knoten :00 und :30', */
		if ( is_bool( $value ) ) {
			$literal = $value ? 'true' : 'false';
		} elseif ( is_int( $value ) || is_float( $value ) ) {
			$literal = (string) $value;
		} else {
			$literal = "'" . $value . "'";
		}

		$pattern = "/'" . preg_quote( $attr, '/' ) . "'\s*=>\s*" . preg_quote( $literal, '/' ) . '\s*,/';
		if ( ! preg_match( $pattern, $body ) ) {
			$problems[] = "$rel: Vorgabe für '$attr' ($literal) fehlt in $fn() oder weicht ab";
		}
	}
}

echo $problems ? implode( "\n", $problems ) . "\n" : '';
exit( 0 );
