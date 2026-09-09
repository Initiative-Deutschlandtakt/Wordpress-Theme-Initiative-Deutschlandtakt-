<?php
/**
 * Sammelt Funktionsnamen aus den PHP-Dateien eines Verzeichnisses.
 *
 * Hilfsprogramm für bin/check-theme.sh: Statt mit Grep zu raten, wird der
 * PHP-Tokenizer benutzt — der kennt den Unterschied zwischen einem Aufruf,
 * einer Variablen, einem Kommentar und einer Zeichenkette. Ein Kommentar, der
 * eine Funktion erwähnt, löst damit keinen Fehlalarm mehr aus.
 *
 * Aufruf:
 *   php bin/php-symbols.php declared  <verzeichnis>   deklarierte Funktionen
 *   php bin/php-symbols.php called    <verzeichnis>   aufgerufene Funktionen
 *   php bin/php-symbols.php callbacks <verzeichnis>   String-Callbacks aus
 *                                                     add_action/-filter/-shortcode
 *
 * Ausgabe: ein Name pro Zeile, sortiert, ohne Dubletten.
 *
 * @package idt
 */

$mode = $argv[1] ?? '';
$dir  = $argv[2] ?? '';

if ( ! in_array( $mode, array( 'declared', 'called', 'callbacks' ), true ) || ! is_dir( $dir ) ) {
	fwrite( STDERR, "Aufruf: php bin/php-symbols.php declared|called|callbacks <verzeichnis>\n" );
	exit( 2 );
}

/** Hooks, deren erster oder zweiter String-Parameter ein Funktionsname ist. */
const HOOK_FUNCTIONS = array( 'add_action', 'add_filter', 'add_shortcode', 'register_activation_hook' );

$names = array();

$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $files as $file ) {
	if ( 'php' !== strtolower( $file->getExtension() ) ) {
		continue;
	}

	$tokens = token_get_all( file_get_contents( $file->getPathname() ) );

	// Nur die bedeutungstragenden Tokens: Whitespace und Kommentare raus.
	$t = array();
	foreach ( $tokens as $token ) {
		if ( is_array( $token ) && in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$t[] = $token;
	}

	for ( $i = 0, $n = count( $t ); $i < $n; $i++ ) {
		$cur  = $t[ $i ];
		$prev = $t[ $i - 1 ] ?? null;
		$next = $t[ $i + 1 ] ?? null;

		if ( 'declared' === $mode ) {
			// function name( … ) — Closures haben statt des Namens direkt „(“.
			if ( is_array( $cur ) && T_FUNCTION === $cur[0] && is_array( $next ) && T_STRING === $next[0] ) {
				$names[] = $next[1];
			}
			continue;
		}

		if ( 'called' === $mode ) {
			// name( … ), aber keine Deklaration, keine Methode, kein new.
			if ( ! is_array( $cur ) || T_STRING !== $cur[0] || '(' !== $next ) {
				continue;
			}
			if ( is_array( $prev ) && in_array( $prev[0], array( T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NEW ), true ) ) {
				continue;
			}
			if ( is_array( $prev ) && defined( 'T_NULLSAFE_OBJECT_OPERATOR' ) && T_NULLSAFE_OBJECT_OPERATOR === $prev[0] ) {
				continue;
			}
			$names[] = $cur[1];
			continue;
		}

		// callbacks: add_action( 'hook', 'idt_funktion' ) — der Name steckt in
		// einer Zeichenkette, PHP prüft ihn erst beim Auslösen des Hooks.
		if ( ! is_array( $cur ) || T_STRING !== $cur[0] || ! in_array( $cur[1], HOOK_FUNCTIONS, true ) || '(' !== $next ) {
			continue;
		}
		// Argumente durchzählen: Argument 0 ist der Hook- bzw. Shortcode-Name,
		// erst ab Argument 1 steht der Callback. Sonst würde ein eigener Hook
		// wie do_action( 'idt_nach_dem_seed' ) als fehlende Funktion gelten.
		$arg = 0;
		for ( $j = $i + 2, $depth = 1; $j < $n && $depth > 0; $j++ ) {
			if ( '(' === $t[ $j ] || '[' === $t[ $j ] ) {
				$depth++;
			} elseif ( ')' === $t[ $j ] || ']' === $t[ $j ] ) {
				$depth--;
			} elseif ( ',' === $t[ $j ] && 1 === $depth ) {
				$arg++;
			} elseif ( $arg >= 1 && 1 === $depth && is_array( $t[ $j ] ) && T_CONSTANT_ENCAPSED_STRING === $t[ $j ][0] ) {
				$literal = trim( $t[ $j ][1], "'\"" );
				if ( preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $literal ) ) {
					$names[] = $literal;
				}
			}
		}
	}
}

$names = array_unique( $names );
sort( $names );
echo implode( "\n", $names ), "\n";
