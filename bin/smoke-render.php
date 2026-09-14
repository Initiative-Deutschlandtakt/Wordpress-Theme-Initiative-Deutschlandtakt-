<?php
/**
 * Rendertest im laufenden WordPress — wird von bin/smoke-test.sh über
 * `wp eval-file` in einer frisch installierten Instanz ausgeführt.
 *
 * Geprüft wird, was statische Analyse nicht sehen kann: ob jeder Block und
 * jeder Shortcode tatsächlich Markup erzeugt, ohne dass PHP dabei eine
 * Warnung, ein Notice oder ein Deprecated wirft. Genau diese Fehler fallen
 * sonst erst der Redaktion auf — im Editor, auf einer weißen Seite.
 *
 * @package idt
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( 1 );
}

$idt_probleme = array();

// Jede Meldung von PHP zählt als Fehler — auch Notices und Deprecated, die im
// Normalbetrieb nur im Log landen würden. Ausgenommen sind Deprecated-Meldungen
// aus WordPress selbst: die kommen mit neuen PHP-Versionen und sind nichts,
// was dieses Theme lösen könnte.
set_error_handler(
	function ( $errno, $errstr, $errfile, $errline ) use ( &$idt_probleme ) {
		$aus_dem_theme = false !== strpos( $errfile, '/themes/idt-deutschlandtakt/' );
		$deprecated    = in_array( $errno, array( E_DEPRECATED, E_USER_DEPRECATED ), true );
		if ( $deprecated && ! $aus_dem_theme ) {
			return true;
		}
		$idt_probleme[] = sprintf( 'PHP: %s (%s:%d)', $errstr, basename( $errfile ), $errline );
		return true;
	}
);

// -----------------------------------------------------------------------------
// 1) Theme aktiv und Version konsistent
$theme = wp_get_theme();
if ( 'idt-deutschlandtakt' !== $theme->get_stylesheet() ) {
	$idt_probleme[] = 'Aktives Theme ist ' . $theme->get_stylesheet() . ', nicht idt-deutschlandtakt';
}
if ( ! defined( 'IDT_VERSION' ) || IDT_VERSION !== $theme->get( 'Version' ) ) {
	$idt_probleme[] = 'IDT_VERSION passt zur Laufzeit nicht zum Theme-Header';
}

// -----------------------------------------------------------------------------
// 2) Blöcke: jeder registrierte idt/-Block rendert serverseitig
$blocks = array();
foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
	if ( 0 === strpos( $name, 'idt/' ) ) {
		$blocks[ $name ] = $type;
	}
}
if ( count( $blocks ) < 2 ) {
	$idt_probleme[] = 'Es sind kaum idt/-Blöcke registriert (' . count( $blocks ) . ') — inc/blocks.php lädt nicht';
}

foreach ( $blocks as $name => $type ) {
	$attrs = array();
	foreach ( (array) $type->attributes as $key => $schema ) {
		if ( isset( $schema['default'] ) ) {
			$attrs[ $key ] = $schema['default'];
		}
	}
	$html = render_block(
		array(
			'blockName'    => $name,
			'attrs'        => $attrs,
			'innerHTML'    => '',
			'innerContent' => array(),
		)
	);
	if ( ! is_string( $html ) ) {
		$idt_probleme[] = "Block $name liefert kein Markup";
	}
}
WP_CLI::line( sprintf( '  Blöcke gerendert: %d', count( $blocks ) ) );

/* Blöcke mit eigener block.json (theme/.../blocks/*) fallen durch die Prüfung
 * von idt_blocks_config() unten hindurch — sie stehen nicht darin. Für sie gilt
 * hier dieselbe Meßlatte: Mit ihren Vorgabewerten müssen sie Markup erzeugen.
 * Ein leeres Ergebnis heißt in aller Regel, dass die Registrierung keinen
 * render_callback bekommen hat; im Editor sieht man davon nichts, die Seite
 * bleibt aber leer. */
foreach ( glob( get_template_directory() . '/blocks/*/block.json' ) as $idt_meta_pfad ) {
	$idt_meta = json_decode( file_get_contents( $idt_meta_pfad ), true );
	$idt_name = $idt_meta['name'] ?? '';
	if ( ! isset( $blocks[ $idt_name ] ) ) {
		$idt_probleme[] = "Block $idt_name aus " . basename( dirname( $idt_meta_pfad ) ) . '/block.json ist nicht registriert';
		continue;
	}
	$idt_attrs = array();
	foreach ( (array) ( $idt_meta['attributes'] ?? array() ) as $idt_key => $idt_schema ) {
		if ( array_key_exists( 'default', $idt_schema ) ) {
			$idt_attrs[ $idt_key ] = $idt_schema['default'];
		}
	}
	$idt_html = render_block(
		array(
			'blockName'    => $idt_name,
			'attrs'        => $idt_attrs,
			'innerHTML'    => '',
			'innerContent' => array(),
		)
	);
	if ( ! is_string( $idt_html ) || '' === trim( $idt_html ) ) {
		$idt_probleme[] = "Block $idt_name rendert mit seinen Vorgabewerten nichts — fehlt der render_callback?";
	}
}

// -----------------------------------------------------------------------------
// 3) Blockkonfiguration: Sidebar-Felder und Render-Funktion gehören zusammen
if ( function_exists( 'idt_blocks_config' ) ) {
	foreach ( idt_blocks_config() as $name => $config ) {
		if ( empty( $config['render'] ) || ! is_callable( $config['render'] ) ) {
			$idt_probleme[] = "Block idt/$name hat keine aufrufbare Render-Funktion";
			continue;
		}
		$attrs = array();
		foreach ( (array) ( $config['fields'] ?? array() ) as $field ) {
			$attrs[ $field['key'] ] = $field['default'] ?? '';
		}
		$html = call_user_func( $config['render'], $attrs );
		if ( ! is_string( $html ) || '' === trim( $html ) ) {
			$idt_probleme[] = "Block idt/$name rendert mit seinen Standardwerten nichts";
		}
	}
	WP_CLI::line( sprintf( '  Blockkonfigurationen geprüft: %d', count( idt_blocks_config() ) ) );
} else {
	$idt_probleme[] = 'idt_blocks_config() existiert nicht';
}

// -----------------------------------------------------------------------------
// 4) Shortcodes: Fallback-Syntax muss weiter funktionieren
global $shortcode_tags;
$idt_tags = array();
foreach ( (array) $shortcode_tags as $tag => $callback ) {
	if ( is_string( $callback ) && 0 === strpos( $callback, 'idt_sc_' ) ) {
		$idt_tags[] = $tag;
	}
}
if ( count( $idt_tags ) < 10 ) {
	$idt_probleme[] = 'Es sind kaum idt-Shortcodes registriert (' . count( $idt_tags ) . ')';
}
foreach ( $idt_tags as $tag ) {
	foreach ( array( "[$tag]", "[$tag]Beispieltext[/$tag]" ) as $probe ) {
		$html = do_shortcode( $probe );
		if ( ! is_string( $html ) ) {
			$idt_probleme[] = "Shortcode $probe liefert kein Markup";
		}
	}
}
WP_CLI::line( sprintf( '  Shortcodes gerendert: %d', count( $idt_tags ) ) );

// -----------------------------------------------------------------------------
// 5) Patterns: im Inserter sichtbar
if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
	// Ohne Argument: alle registrierten Patterns. Das optionale Argument heißt
	// $outside_init_only und würde genau die ausblenden, die — wie hier — an
	// „init" registriert werden.
	$patterns = array_filter(
		WP_Block_Patterns_Registry::get_instance()->get_all_registered(),
		function ( $pattern ) {
			return isset( $pattern['name'] ) && 0 === strpos( $pattern['name'], 'idt/' );
		}
	);
	if ( count( $patterns ) < 1 ) {
		$idt_probleme[] = 'Keine idt/-Patterns registriert — inc/patterns.php lädt nicht';
	}
	WP_CLI::line( sprintf( '  Patterns registriert: %d', count( $patterns ) ) );
}

// -----------------------------------------------------------------------------
// 6) Demo-Inhalte: das Seeding hat Seiten, Beiträge und ein Menü angelegt
$seiten    = (int) wp_count_posts( 'page' )->publish;
$beitraege = (int) wp_count_posts( 'post' )->publish;
if ( $seiten < 2 ) {
	$idt_probleme[] = "Nur $seiten Seite(n) vorhanden — idt_seed_demo_content() hat nichts angelegt";
}
if ( $beitraege < 1 ) {
	$idt_probleme[] = 'Keine Beiträge vorhanden — die Beitragsliste lässt sich nicht prüfen';
}
WP_CLI::line( sprintf( '  Demo-Inhalte: %d Seiten, %d Beiträge', $seiten, $beitraege ) );

// -----------------------------------------------------------------------------
restore_error_handler();

if ( $idt_probleme ) {
	foreach ( $idt_probleme as $problem ) {
		WP_CLI::warning( $problem );
	}
	WP_CLI::error( sprintf( '%d Problem(e) beim Rendern.', count( $idt_probleme ) ) );
}

WP_CLI::success( 'Blöcke, Shortcodes, Patterns und Demo-Inhalte rendern fehlerfrei.' );
