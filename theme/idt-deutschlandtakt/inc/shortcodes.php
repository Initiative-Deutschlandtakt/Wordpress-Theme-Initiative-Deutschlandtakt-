<?php
/**
 * Spielerische Stilelemente als Shortcodes.
 *
 * Diese lassen sich direkt im Editor in Texten verwenden, z. B.:
 *   [eyebrow]Über die Initiative[/eyebrow]
 *   Bahnfahren wird [mark]attraktiver[/mark].
 *   [takt count="5"]
 *   [stat number="2008" label="gegründet"]
 *   [pill href="/mitmachen"]Mitglied werden[/pill]
 *   [callout type="cyan"]Wichtiger Hinweis …[/callout]
 *   [diagonal]Großer Aussage-Block auf dunklem Grund.[/diagonal]
 *   Mail: [email]kontakt@initiative-deutschlandtakt.de[/email]
 *   [themenblock bg="ink" title="Unser Plan"]Die Vision | /vision/ | Kurzbeschreibung[/themenblock]
 *   [buttonstack bg="ink"]Die Vision | /vision/ | Kurzbeschreibung[/buttonstack]
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Eyebrow — Mono-Label über einer Überschrift. */
function idt_sc_eyebrow( $atts, $content = '' ) {
	return '<span class="idt-eyebrow">' . wp_kses_post( do_shortcode( $content ) ) . '</span>';
}
add_shortcode( 'eyebrow', 'idt_sc_eyebrow' );

/** Marker — Texthighlight (Gelb / Cyan / Violett). */
function idt_sc_mark( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'color' => 'yellow' ), $atts, 'mark' );
	$class = 'idt-mark';
	if ( 'cyan' === $atts['color'] )   { $class .= ' idt-mark--cyan'; }
	if ( 'violet' === $atts['color'] ) { $class .= ' idt-mark--violet'; }
	return '<span class="' . esc_attr( $class ) . '">' . wp_kses_post( do_shortcode( $content ) ) . '</span>';
}
add_shortcode( 'mark', 'idt_sc_mark' );

/** Lead — hervorgehobener Einleitungsabsatz. */
function idt_sc_lead( $atts, $content = '' ) {
	return '<p class="lead">' . wp_kses_post( do_shortcode( $content ) ) . '</p>';
}
add_shortcode( 'lead', 'idt_sc_lead' );

/** Takt-Rhythmus — dekorative Punkt-/Strich-Sequenz. */
function idt_sc_takt( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 6 ), $atts, 'takt' );
	$count = max( 1, min( 24, (int) $atts['count'] ) );
	$dots  = str_repeat( '<span></span>', $count );
	return '<span class="idt-takt" aria-hidden="true">' . $dots . '</span>';
}
add_shortcode( 'takt', 'idt_sc_takt' );

/** Kennzahl — große Zahl mit Label. */
function idt_sc_stat( $atts ) {
	$atts = shortcode_atts( array( 'number' => '', 'label' => '' ), $atts, 'stat' );
	return '<span class="idt-stat"><span class="idt-stat__num">' . esc_html( $atts['number'] ) .
		'</span><span class="idt-stat__label">' . esc_html( $atts['label'] ) . '</span></span>';
}
add_shortcode( 'stat', 'idt_sc_stat' );

/**
 * Pill-Button. Stile: '' (Outline) | solid | on-ink (helle Outline für dunklen
 * Grund) | violet (violette Outline, Gradient-Rand bei Klick) | beige (gefüllte
 * Papierfläche mit halbrunden Enden — die Variante für Verlaufs- und
 * Bildflächen, s. style.css 6c).
 */
function idt_sc_pill( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'href' => '#', 'style' => '' ), $atts, 'pill' );
	$cls  = 'pill';
	if ( 'solid' === $atts['style'] )  { $cls .= ' pill--solid'; }
	if ( 'on-ink' === $atts['style'] ) { $cls .= ' pill--on-ink'; }
	if ( 'violet' === $atts['style'] ) { $cls .= ' pill--violet'; }
	if ( 'beige' === $atts['style'] )  { $cls .= ' pill--beige'; }
	return '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $atts['href'] ) . '">' . wp_kses_post( do_shortcode( $content ) ) . '</a>';
}
add_shortcode( 'pill', 'idt_sc_pill' );

/**
 * Pill-Button-Stack — vertikal gestapelte Pills in einheitlicher Breite
 * (die breiteste Beschriftung bestimmt die Breite aller Buttons).
 * [pillstack align="center" minwidth="260"][pill href="/a/"]Erster[/pill][pill href="/b/"]Zweiter[/pill][/pillstack]
 */
function idt_sc_pillstack( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'align' => 'center', 'minwidth' => 0 ), $atts, 'pillstack' );
	$align = in_array( $atts['align'], array( 'left', 'center', 'right' ), true ) ? $atts['align'] : 'center';
	$min   = (int) $atts['minwidth'];
	/* min(…, 100%) statt nackter Pixel: Die Mindestbreite gilt, solange sie in
	   die Spalte passt — auf dem Telefon schrumpft der Stack mit, statt über
	   den Rand zu laufen. */
	$style = $min > 0 ? ' style="min-width:min(' . $min . 'px, 100%)"' : '';
	return '<div class="idt-pillstack-outer" style="text-align:' . esc_attr( $align ) . '">' .
		'<span class="idt-pillstack"' . $style . '>' . do_shortcode( $content ) . '</span></div>';
}
add_shortcode( 'pillstack', 'idt_sc_pillstack' );

/** Callout / Hinweisbox. */
function idt_sc_callout( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'type' => '' ), $atts, 'callout' );
	$class = 'idt-callout';
	if ( in_array( $atts['type'], array( 'cyan', 'violet', 'yellow' ), true ) ) {
		$class .= ' idt-callout--' . $atts['type'];
	}
	return '<div class="' . esc_attr( $class ) . '">' . wp_kses_post( do_shortcode( wpautop( $content ) ) ) . '</div>';
}
add_shortcode( 'callout', 'idt_sc_callout' );

/** Diagonal-Akzentblock (Horizont-Motiv auf dunklem Grund). */
function idt_sc_diagonal( $atts, $content = '' ) {
	return '<div class="idt-diagonal">' . wp_kses_post( do_shortcode( wpautop( $content ) ) ) . '</div>';
}
add_shortcode( 'diagonal', 'idt_sc_diagonal' );

/**
 * Entfernt die Absatz-/Umbruch-Reste, die wpautop vor dem Shortcode-Lauf
 * zwischen die Karten eines Container-Shortcodes gesetzt hat. Ohne das
 * entstünden im Raster leere Zellen aus <p></p>.
 */
function idt_strip_autop( $content ) {
	$content = preg_replace( '#<br\s*/?>#', '', (string) $content );
	$content = preg_replace( '#</?p>#', "\n", $content );
	return $content;
}

/**
 * Karten-Raster — Container, der mehrere Karten in ein gleichmäßiges Raster
 * legt (gleiche Breiten, gleiche Höhen, automatischer Umbruch). Ohne ihn
 * behält jede Karte ihre eigene Größe.
 *   [cards cols="3"]
 *   [concept color="ink" title="Die Idee"]Jede Stunde zur selben Minute.[/concept]
 *   [concept color="ink" title="Akteure"]Wer entscheidet was.[/concept]
 *   [/cards]
 * cols: 2 | 3 | 4 | auto (so viele je Zeile, wie bei 280px Breite passen)
 */
function idt_sc_cards( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'cols' => '3' ), $atts, 'cards' );
	$cols = in_array( (string) $atts['cols'], array( '2', '3', '4', 'auto' ), true ) ? (string) $atts['cols'] : '3';
	return '<div class="idt-cards idt-cards--' . esc_attr( $cols ) . '">' . do_shortcode( idt_strip_autop( $content ) ) . '</div>';
}
add_shortcode( 'cards', 'idt_sc_cards' );

/** Karte. */
function idt_sc_card( $atts, $content = '' ) {
	return '<div class="idt-card">' . wp_kses_post( do_shortcode( wpautop( $content ) ) ) . '</div>';
}
add_shortcode( 'card', 'idt_sc_card' );

/** Zerlegt „Beschriftung | Link"-Zeilen in [Beschriftung, URL]-Paare (null bei leerem Text). */
function idt_parse_button_lines( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) { continue; }
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$out[] = array( $parts[0], isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] : '#' );
	}
	return $out ? $out : null;
}

/**
 * Horizont-Splash — der Hero der Startseite (idt_render_splash() aus functions.php).
 * Links frei konfigurierbar, eine Zeile je Button („Beschriftung | Link"):
 *   [splash caption="Mehr Verkehr auf die Schiene."]
 *   Über die Initiative | /ueber-uns/
 *   Mitglied werden | mailto:mail@initiative-deutschlandtakt.de
 *   [/splash]
 * Ohne Inhalt greifen die Standard-Links; ohne caption-Attribut erscheint
 * keine Schlagzeile.
 */
function idt_sc_splash( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'caption' => null ), $atts, 'splash' );
	ob_start();
	idt_render_splash( idt_parse_button_lines( $content ), $atts['caption'] );
	return ob_get_clean();
}
add_shortcode( 'splash', 'idt_sc_splash' );

/**
 * Horizont-Splash v2 ("Zentriert") — fluide Alternative zu [splash], siehe
 * idt_render_splash2() aus functions.php. Gleiche Syntax:
 *   [splash2 caption="Mehr Verkehr auf die Schiene."]
 *   Über die Initiative | /ueber-uns/
 *   Mitglied werden | mailto:mail@initiative-deutschlandtakt.de
 *   [/splash2]
 */
function idt_sc_splash2( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'caption' => null ), $atts, 'splash2' );
	ob_start();
	idt_render_splash2( idt_parse_button_lines( $content ), $atts['caption'] );
	return ob_get_clean();
}
add_shortcode( 'splash2', 'idt_sc_splash2' );

/* =========================================================================
 * Knotendreieck — interaktive Grafik zum Knotenprinzip
 * ====================================================================== */

/**
 * Vorgabewerte des Knotendreiecks.
 *
 * Eine Quelle für beide Oberflächen: der Shortcode fällt hier hinein, der
 * Block liest dieselben Werte über idt_knotendreieck_block_attributes()
 * (inc/blocks.php) als Attribut-Vorgaben. In view.js stehen dieselben Texte
 * noch einmal — dort als letzte Rückfallebene, wenn das Custom Element ohne
 * Attribute im Markup steht.
 */
function idt_knotendreieck_defaults() {
	return array(
		'grafikTitel'      => 'Das Knotenprinzip',
		'subzeile'         => 'Knoten :00 und :30',
		'knotenOben'       => 'Hollerbrück',
		'knotenLinks'      => 'Mardingen',
		'knotenRechts'     => 'Kirchsee',
		'bildunterschrift' => '',
		'zyklusSekunden'   => 13.5,
		'autoplay'         => true,
		'fahrzeugStil'     => 'Striche',
	);
}

/**
 * Knotendreieck rendern — die eine Render-Funktion hinter Shortcode und Block
 * (blocks/knotendreieck/render.php ruft sie auf).
 *
 * Ausgegeben wird nur die Hülle: ein <figure> mit dem Custom Element
 * <idt-knotendreieck>, dem Standbild für den Fall ohne JavaScript und der
 * Bildunterschrift. Gezeichnet wird die Grafik im Browser von
 * blocks/knotendreieck/view.js.
 *
 * Die Bildunterschrift steht bewusst als <figcaption> unter der Grafik und
 * nicht im Bild: dort bleibt sie durchsuchbar und für Screenreader lesbar.
 * Die Überschrift sitzt umgekehrt im Bild, damit sie dabeibleibt, wenn jemand
 * die Grafik in einen Vortrag oder in soziale Medien zieht.
 *
 * @param array  $args    Beschriftungen und Bewegungsoptionen, siehe idt_knotendreieck_defaults().
 * @param string $wrapper Fertige Attributliste für das <figure>. Leer = Klasse
 *                        idt-knotendreieck; der Block reicht hier
 *                        get_block_wrapper_attributes() herein, damit Anker
 *                        und Abstände aus der Seitenleiste ankommen.
 * @return string HTML.
 */
function idt_render_knotendreieck( $args = array(), $wrapper = '' ) {
	$a = wp_parse_args( $args, idt_knotendreieck_defaults() );

	/* Zyklusdauer im Bereich des Editor-Reglers halten: darunter ist die
	   Bewegung nicht mehr zu verfolgen, darüber wartet man auf den Knoten. */
	$zyklus = max( 8.0, min( 24.0, (float) $a['zyklusSekunden'] ) );
	$stil   = 'Punkte' === $a['fahrzeugStil'] ? 'Punkte' : 'Striche';

	$grafik = sprintf(
		'<idt-knotendreieck grafik-titel="%s" subzeile="%s" knoten-oben="%s"'
			. ' knoten-links="%s" knoten-rechts="%s" cycle-seconds="%s"'
			. ' vehicle-style="%s" autoplay="%s"></idt-knotendreieck>',
		esc_attr( $a['grafikTitel'] ),
		esc_attr( $a['subzeile'] ),
		esc_attr( $a['knotenOben'] ),
		esc_attr( $a['knotenLinks'] ),
		esc_attr( $a['knotenRechts'] ),
		esc_attr( (string) $zyklus ),
		esc_attr( $stil ),
		$a['autoplay'] ? 'true' : 'false'
	);

	/* Ein Custom Element ohne JavaScript rendert nichts — das Standbild zur
	   Minute :30 tritt an seine Stelle. Es wird aus view.js gebaut
	   (bin/knotendreieck-standbild.js) und trägt deshalb die Vorgabetexte,
	   nicht die hier eingestellten: ein zweiter Zeichenweg für dasselbe Bild
	   liefe über kurz oder lang auseinander. Die Beschreibung bleibt darum
	   allgemein und beschreibt das Prinzip, nicht die Knotennamen. */
	$standbild = '';
	if ( file_exists( get_template_directory() . '/blocks/knotendreieck/standbild.svg' ) ) {
		$standbild = sprintf(
			'<noscript><img class="idt-knotendreieck__standbild" src="%s" alt="%s" width="1000" height="1000" decoding="async"></noscript>',
			esc_url( get_template_directory_uri() . '/blocks/knotendreieck/standbild.svg' ),
			esc_attr__( 'Modell eines Knotendreiecks: drei Knotenbahnhöfe, drei Linien, Abfahrten zur Minute :02 und :32', 'idt' )
		);
	}

	$caption = trim( (string) $a['bildunterschrift'] );
	$caption = '' !== $caption
		? '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption>'
		: '';

	if ( '' === $wrapper ) {
		$wrapper = 'class="idt-knotendreieck"';
	}

	return '<figure ' . $wrapper . '>' . $grafik . $standbild . $caption . '</figure>';
}

/**
 * Knotendreieck als Shortcode — Rückfallebene für Stellen ohne Block-Editor.
 * Im Editor gehört der Block „Knotendreieck" benutzt, dort stehen dieselben
 * Felder in der Seitenleiste.
 *
 *   [knotendreieck titel="Das Knotenprinzip" oben="Hollerbrück"
 *                  links="Mardingen" rechts="Kirchsee"
 *                  sekunden="13.5" autoplay="ja" fahrzeuge="Striche"
 *                  bildunterschrift="…"]
 *
 * WordPress schreibt Shortcode-Attribute klein — deshalb die kurzen Namen
 * hier statt der camelCase-Schlüssel des Blocks.
 */
function idt_sc_knotendreieck( $atts ) {
	$d    = idt_knotendreieck_defaults();
	$atts = shortcode_atts( array(
		'titel'            => $d['grafikTitel'],
		'unten'            => $d['subzeile'],
		'oben'             => $d['knotenOben'],
		'links'            => $d['knotenLinks'],
		'rechts'           => $d['knotenRechts'],
		'bildunterschrift' => $d['bildunterschrift'],
		'sekunden'         => $d['zyklusSekunden'],
		'autoplay'         => 'ja',
		'fahrzeuge'        => $d['fahrzeugStil'],
	), $atts, 'knotendreieck' );

	return idt_render_knotendreieck( array(
		'grafikTitel'      => $atts['titel'],
		'subzeile'         => $atts['unten'],
		'knotenOben'       => $atts['oben'],
		'knotenLinks'      => $atts['links'],
		'knotenRechts'     => $atts['rechts'],
		'bildunterschrift' => $atts['bildunterschrift'],
		'zyklusSekunden'   => $atts['sekunden'],
		'autoplay'         => in_array( strtolower( (string) $atts['autoplay'] ), array( 'ja', 'true', '1', 'yes' ), true ),
		'fahrzeugStil'     => $atts['fahrzeuge'],
	) );
}
add_shortcode( 'knotendreieck', 'idt_sc_knotendreieck' );

/* =========================================================================
 * Elemente aus der „Example Landing Page" (Design-System) — für den Editor.
 * ====================================================================== */

/** Inline-SVG-Icon (stroke = currentColor). */
function idt_icon( $name, $size = 24 ) {
	$paths = array(
		'arrow'  => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
		'extern' => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>',
		'clock'  => '<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>',
		'rail'   => '<circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/>',
		'netz'   => '<polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3.6 7.2 12 13 20.4 7.2"/>',
		/* Social-Icons — vereinfachte Strichzeichnungen im Stil der übrigen Icons (kein Marken-Logo 1:1). */
		'x'         => '<line x1="4" y1="4" x2="20" y2="20"/><line x1="20" y1="4" x2="4" y2="20"/>',
		'facebook'  => '<path d="M15 4h-2a3 3 0 0 0-3 3v3H7v3h3v7h3v-7h2.5l.5-3H13V7a1 1 0 0 1 1-1h2z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/>',
		'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3"/><line x1="7.5" y1="10" x2="7.5" y2="17"/><circle cx="7.5" cy="6.6" r="1.1" fill="currentColor" stroke="none"/><line x1="11" y1="10" x2="11" y2="17"/><path d="M11 13a2.5 2.5 0 0 1 5 0v4"/>',
		'youtube'   => '<rect x="3" y="6" width="18" height="12" rx="4"/><polygon points="10 9.5 10 14.5 15 12" fill="currentColor" stroke="none"/>',
		'mastodon'  => '<path d="M6 4h12a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-3l-4 4v-4H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3z"/><line x1="8" y1="9" x2="8" y2="12"/><line x1="12" y1="8" x2="12" y2="13"/><line x1="16" y1="9" x2="16" y2="12"/>',
		'bluesky'   => '<path d="M12 8c-2-4-6-5-8-3 1 3 3 5 6 6-3 .5-5 2-6 4 3 2 6 1 8-2 2 3 5 4 8 2-1-2-3-3.5-6-4 3-1 5-3 6-6-2-2-6-1-8 3z"/>',
		'rss'       => '<circle cx="6" cy="18" r="1.5" fill="currentColor" stroke="none"/><path d="M5 11a8 8 0 0 1 8 8"/><path d="M5 5a14 14 0 0 1 14 14"/>',
	);
	$name = isset( $paths[ $name ] ) ? $name : 'arrow';
	$sw   = ( 'arrow' === $name || 'extern' === $name ) ? '2.5' : '2';
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>';
}

/** Akzentfarbe (Name → CSS-Variablen) für Karten/Tags. */
function idt_accent( $color ) {
	switch ( $color ) {
		case 'cyan':   return array( 'border' => 'var(--idt-cyan)',   'icon' => 'var(--idt-cyan)',   'soft' => 'var(--idt-cyan-soft)',   'text' => 'var(--idt-ink)' );
		case 'yellow': return array( 'border' => 'var(--idt-yellow)', 'icon' => '#C9A100',           'soft' => 'var(--idt-yellow-soft)', 'text' => 'var(--idt-ink)' );
		case 'ink':    return array( 'border' => 'var(--idt-ink)',    'icon' => 'var(--idt-ink)',    'soft' => 'var(--idt-paper-2)',     'text' => 'var(--idt-ink)' );
		default:       return array( 'border' => 'var(--idt-violet)', 'icon' => 'var(--idt-violet)', 'soft' => 'var(--idt-violet-soft)', 'text' => 'var(--idt-violet)' );
	}
}

/**
 * Markenfarbe eines Schlagworts (deterministisch über die Term-ID), damit ein
 * Schlagwort überall im Auftritt dieselbe Chip-Farbe trägt.
 */
function idt_tag_accent( $term ) {
	$palette = array( 'violet', 'cyan', 'yellow' );
	return idt_accent( $palette[ (int) $term->term_id % 3 ] );
}

/**
 * Rendert die echten WordPress-Begriffe einer Taxonomie (Schlagwörter oder
 * Kategorien) eines Beitrags als farbige Chips. Anders als der dekorative
 * [tag]-Shortcode spiegeln diese Chips die tatsächlich vergebenen Begriffe
 * wider und sind (optional) mit der jeweiligen Archivseite verlinkt — die
 * Grundlage fürs Filtern.
 *
 * @param int    $post_id  Beitrag (0 = aktueller im Loop).
 * @param string $taxonomy 'post_tag' oder 'category'.
 * @param int    $limit    Maximale Anzahl Chips (0 = alle).
 * @param bool   $link     true = Chips verlinken auf die Archivseite.
 */
function idt_post_terms_html( $post_id = 0, $taxonomy = 'post_tag', $limit = 0, $link = true ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$tags    = get_the_terms( $post_id, $taxonomy );
	/* Die Standardkategorie („Allgemein"/„Uncategorized") ist kein bewusst
	 * vergebenes Merkmal — sie bekommt deshalb auch keinen Chip. */
	if ( 'category' === $taxonomy && ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		$default_cat = (int) get_option( 'default_category' );
		$tags        = array_values( array_filter( $tags, function ( $t ) use ( $default_cat ) {
			return (int) $t->term_id !== $default_cat;
		} ) );
	}
	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return '';
	}
	if ( $limit > 0 ) {
		$tags = array_slice( $tags, 0, $limit );
	}

	$out = '';
	foreach ( $tags as $t ) {
		$a     = idt_tag_accent( $t );
		$style = 'color:' . $a['text'] . ';background:' . $a['soft'] . ';border-color:' . $a['border'];
		$cls   = 'idt-tag' . ( $link ? ' idt-tag--link' : '' );
		if ( $link ) {
			$url = get_term_link( $t );
			if ( is_wp_error( $url ) ) {
				continue;
			}
			$out .= '<a class="' . $cls . '" href="' . esc_url( $url ) . '" style="' . esc_attr( $style ) . '">' . esc_html( $t->name ) . '</a>';
		} else {
			$out .= '<span class="' . $cls . '" style="' . esc_attr( $style ) . '">' . esc_html( $t->name ) . '</span>';
		}
	}
	return $out;
}

/** Rückwärtskompatibler Alias: Chips der Schlagwörter eines Beitrags. */
function idt_post_tags_html( $post_id = 0, $limit = 0, $link = true ) {
	return idt_post_terms_html( $post_id, 'post_tag', $limit, $link );
}

/**
 * Filterleiste aus allen tatsächlich vergebenen Begriffen einer Taxonomie
 * (Schlagwörter oder Kategorien). Jeder Chip verlinkt auf die passende
 * Archivseite; auf der zugehörigen Archivseite wird der aktive Begriff
 * hervorgehoben, ein „Alle"-Chip setzt den Filter zurück. Liefert einen
 * leeren String, wenn noch keine Begriffe vergeben sind.
 *
 * @param string $taxonomy  'post_tag' oder 'category'.
 * @param string $reset_url Ziel des „Alle"-Chips (Standard: Beitragsseite/Start).
 */
function idt_render_taxonomy_filter( $taxonomy = 'post_tag', $reset_url = '' ) {
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
	) );
	/* Standardkategorie ("Allgemein"/"Uncategorized") aus der Filterleiste ausblenden — sie ist kein bewusst vergebenes Filterkriterium. */
	if ( 'category' === $taxonomy && ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$default_cat = (int) get_option( 'default_category' );
		$terms       = array_filter( $terms, function ( $t ) use ( $default_cat ) {
			return (int) $t->term_id !== $default_cat;
		} );
	}
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}

	if ( '' === $reset_url ) {
		$blog      = (int) get_option( 'page_for_posts' );
		$reset_url = $blog ? get_permalink( $blog ) : home_url( '/' );
	}
	$is_archive = ( 'category' === $taxonomy ) ? is_category() : is_tag();
	$active_id  = $is_archive ? (int) get_queried_object_id() : 0;
	$label      = ( 'category' === $taxonomy ) ? __( 'Beiträge nach Kategorie filtern', 'idt' ) : __( 'Beiträge nach Schlagwort filtern', 'idt' );

	ob_start();
	?>
	<nav class="idt-tagfilter" aria-label="<?php echo esc_attr( $label ); ?>">
		<a class="idt-tag idt-tag--link idt-tagfilter__all<?php echo $active_id ? '' : ' is-active'; ?>" href="<?php echo esc_url( $reset_url ); ?>"<?php echo $active_id ? '' : ' aria-current="page"'; ?>><?php esc_html_e( 'Alle', 'idt' ); ?></a>
		<?php
		foreach ( $terms as $t ) :
			$a     = idt_tag_accent( $t );
			$style = 'color:' . $a['text'] . ';background:' . $a['soft'] . ';border-color:' . $a['border'];
			$is    = ( (int) $t->term_id === $active_id );
			$url   = get_term_link( $t );
			if ( is_wp_error( $url ) ) {
				continue;
			}
			?>
			<a class="idt-tag idt-tag--link<?php echo $is ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>" style="<?php echo esc_attr( $style ); ?>"<?php echo $is ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $t->name ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
	return ob_get_clean();
}

/** Rückwärtskompatibler Alias: Filterleiste für Schlagwörter. */
function idt_render_tag_filter( $reset_url = '' ) {
	return idt_render_taxonomy_filter( 'post_tag', $reset_url );
}

/** Filterleiste für Kategorien. */
function idt_render_category_filter( $reset_url = '' ) {
	return idt_render_taxonomy_filter( 'category', $reset_url );
}

/**
 * Button (eckig, gerahmt) mit Varianten und optionalem Pfeil.
 * [btn href="#" variant="primary" size="lg" arrow="true"]Label[/btn]
 * Varianten: primary | secondary | outline | ghost | inverse | gradient | beige
 * („beige" ist die gefüllte Papierfläche mit halbrunden Enden für Verlaufs-
 * und Bildflächen, s. style.css 6c.)
 *
 * bg ersetzt die Fläche der Variante durch einen frei gewählten Hintergrund:
 * Markenname („violet", „cyan", „ink", …), Hex-Wert oder Markenverlauf
 * („cyan-violet", „violet-cyan"). Die Schriftfarbe ergibt sich aus dem
 * Kontrast zum Untergrund — s. idt_surface_fill().
 *   [btn href="/mitmachen/" bg="cyan-violet" arrow="true"]Mitglied werden[/btn]
 */
function idt_sc_btn( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'href' => '#', 'variant' => 'primary', 'size' => '', 'arrow' => '', 'bg' => '' ), $atts, 'btn' );
	$cls  = 'idt-btn idt-btn--' . preg_replace( '/[^a-z]/', '', $atts['variant'] );
	if ( 'lg' === $atts['size'] ) { $cls .= ' idt-btn--lg'; }

	$style = '';
	$fill  = idt_surface_fill( $atts['bg'] );
	if ( '' !== $fill['fill'] ) {
		$cls  .= ' idt-btn--bg idt-btn--on-' . ( $fill['dark'] ? 'dark' : 'light' );
		$style = ' style="--btn-bg:' . esc_attr( $fill['fill'] ) . '"';
	}

	$arrow = ( 'true' === $atts['arrow'] || '1' === $atts['arrow'] ) ? idt_icon( 'arrow', 16 ) : '';
	return '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $atts['href'] ) . '"' . $style . '>' . wp_kses_post( do_shortcode( $content ) ) . $arrow . '</a>';
}
add_shortcode( 'btn', 'idt_sc_btn' );

/** Kategorie-Tag (farbiger Chip). [tag color="violet"]Stellungnahme[/tag] */
function idt_sc_tag( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'color' => 'violet' ), $atts, 'tag' );
	$a    = idt_accent( $atts['color'] );
	$style = 'color:' . $a['text'] . ';background:' . $a['soft'] . ';border-color:' . $a['border'];
	return '<span class="idt-tag" style="' . esc_attr( $style ) . '">' . wp_kses_post( do_shortcode( $content ) ) . '</span>';
}
add_shortcode( 'tag', 'idt_sc_tag' );

/**
 * Konzept-Karte mit farbiger Oberkante und Icon.
 * [concept color="violet" icon="clock" title="Erst der Fahrplan"]Text[/concept]
 * Icons: clock | rail | netz
 *
 * bg füllt die Karte statt mit Papier mit einer frei gewählten Fläche:
 * Markenname, Hex-Wert oder Markenverlauf („cyan-violet", „violet-cyan").
 * Überschrift, Text und „Mehr erfahren" laufen dann in der Schriftfarbe mit,
 * die auf dem Untergrund den besseren Kontrast hat — s. idt_surface_fill().
 *   [concept bg="cyan-violet" color="ink" title="Die Idee" href="/idee/"]…[/concept]
 */
function idt_sc_concept( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'color' => 'violet', 'icon' => '', 'title' => '', 'href' => '', 'bg' => '' ), $atts, 'concept' );
	$a    = idt_accent( $atts['color'] );
	$icon = $atts['icon'] ? '<div class="idt-concept__ic" style="color:' . esc_attr( $a['icon'] ) . '">' . idt_icon( $atts['icon'], 34 ) . '</div>' : '';
	$head = $atts['title'] ? '<h3>' . esc_html( $atts['title'] ) . '</h3>' : '';
	$body = '<div class="idt-concept__body">' . wp_kses_post( do_shortcode( wpautop( $content ) ) ) . '</div>';

	$cls   = 'idt-concept';
	$style = 'border-top-color:' . $a['border'];
	$fill  = idt_surface_fill( $atts['bg'] );
	if ( '' !== $fill['fill'] ) {
		$cls   .= ' idt-concept--bg idt-concept--on-' . ( $fill['dark'] ? 'dark' : 'light' );
		$style .= ';--card-bg:' . $fill['fill'];
	}

	/* Mit href wird die ganze Karte ein Link (inkl. „Mehr erfahren") — wie auf der Startseite. */
	if ( $atts['href'] ) {
		$more = '<span class="idt-newscard__more">Mehr erfahren ' . idt_icon( 'arrow', 16 ) . '</span>';
		return '<a class="' . esc_attr( $cls . ' idt-concept--link' ) . '" href="' . esc_url( $atts['href'] ) . '" style="' . esc_attr( $style ) . '">' . $icon . $head . $body . $more . '</a>';
	}
	return '<div class="' . esc_attr( $cls ) . '" style="' . esc_attr( $style ) . '">' . $icon . $head . $body . '</div>';
}
add_shortcode( 'concept', 'idt_sc_concept' );

/**
 * Dunkler Einschub (Inverse-Band auf tiefem Teal).
 * [einschub eyebrow="Das Vorbild" title="…"]Text[/einschub]
 */
function idt_sc_einschub( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'eyebrow' => '', 'title' => '' ), $atts, 'einschub' );
	$eb   = $atts['eyebrow'] ? '<span class="idt-eyebrow" style="color:var(--idt-cyan)">' . esc_html( $atts['eyebrow'] ) . '</span>' : '';
	$h    = $atts['title'] ? '<h2>' . esc_html( $atts['title'] ) . '</h2>' : '';
	return '<div class="idt-einschub">' . $eb . $h . wp_kses_post( do_shortcode( wpautop( $content ) ) ) . '</div>';
}
add_shortcode( 'einschub', 'idt_sc_einschub' );

/* =========================================================================
 * Themenblock — farbige Fläche mit Überschrift und Linkliste
 * ====================================================================== */

/**
 * Farbangabe normalisieren: Markenname („ink", „violet", …) oder Hex-Wert
 * (#RGB / #RRGGBB) → #RRGGBB. Unbekannte Werte ergeben einen leeren String,
 * damit der Aufrufer auf seine Vorgabefarbe zurückfallen kann.
 */
function idt_color_hex( $value ) {
	$named = array(
		'ink'         => '#00373C',
		'paper'       => '#FFF6F0',
		'paper-2'     => '#FBEDE6',
		'paper-3'     => '#F3E2DA',
		'violet'      => '#6E50FA',
		'cyan'        => '#00DCFA',
		'yellow'      => '#FFFF96',
		'gray'        => '#585857',
		'violet-soft' => '#E8E3FF',
		'cyan-soft'   => '#D6F8FF',
		'yellow-soft' => '#FFFDDB',
	);
	$value = strtolower( trim( (string) $value ) );
	if ( isset( $named[ $value ] ) ) {
		return $named[ $value ];
	}
	if ( preg_match( '/^#?([0-9a-f]{3}|[0-9a-f]{6})$/', $value, $m ) ) {
		$hex = $m[1];
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return '#' . $hex;
	}
	return '';
}

/** Relative Leuchtdichte (WCAG 2.1) einer #RRGGBB-Farbe. */
function idt_color_luminance( $hex ) {
	$lin = array();
	foreach ( array( 1, 3, 5 ) as $offset ) {
		$channel = hexdec( substr( $hex, $offset, 2 ) ) / 255;
		$lin[]   = ( $channel <= 0.03928 ) ? $channel / 12.92 : pow( ( $channel + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
}

/** Kontrastverhältnis (WCAG 2.1) zweier #RRGGBB-Farben. */
function idt_color_contrast( $a, $b ) {
	$la = idt_color_luminance( $a );
	$lb = idt_color_luminance( $b );
	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
}

/**
 * Braucht diese Fläche helle Schrift? Verglichen wird der Kontrast der Farbe
 * zu Papier (hell) und zu Tinte (dunkel); es gewinnt die Schriftfarbe mit dem
 * besseren Kontrast. So stimmt die Lesbarkeit auch bei frei gewählten Farben.
 *
 * Für Verläufe darf statt einer Farbe eine Liste der Stützfarben übergeben
 * werden: dann zählt das schwächste Ende — es gewinnt die Schriftfarbe, deren
 * schlechtester Kontrast über den ganzen Verlauf hinweg noch der bessere ist.
 */
function idt_surface_is_dark( $hex ) {
	$light = INF;
	$dark  = INF;
	foreach ( (array) $hex as $stop ) {
		$light = min( $light, idt_color_contrast( $stop, '#FFF6F0' ) );
		$dark  = min( $dark, idt_color_contrast( $stop, '#00373C' ) );
	}
	return $light >= $dark;
}

/**
 * Markenverläufe, die als Flächenfüllung wählbar sind. „css" ist der Wert für
 * background (Token aus style.css Abschnitt 2), „stops" sind die Endfarben —
 * aus ihnen ergibt sich die Schriftfarbe. Wer hier einen Verlauf ergänzt,
 * ergänzt das Token in style.css und den Eintrag in idt_bg_options()
 * (inc/blocks.php) mit.
 */
function idt_surface_gradients() {
	return array(
		'cyan-violet' => array( 'css' => 'var(--idt-grad-cyan-violet)', 'stops' => array( '#00DCFA', '#6E50FA' ) ),
		'violet-cyan' => array( 'css' => 'var(--idt-grad-violet-cyan)', 'stops' => array( '#6E50FA', '#00DCFA' ) ),
	);
}

/**
 * Eine bg-Angabe in eine Flächenfüllung übersetzen — Markenname, freier
 * Hex-Wert oder Markenverlauf. Zurück kommt
 *   'fill' — der CSS-Wert für background; '' heißt „keine gültige Angabe",
 *            der Aufrufer bleibt dann bei seiner Vorgabe,
 *   'dark' — true, wenn die Fläche helle Schrift braucht.
 */
function idt_surface_fill( $value ) {
	$value = strtolower( trim( (string) $value ) );
	$grads = idt_surface_gradients();
	if ( isset( $grads[ $value ] ) ) {
		return array( 'fill' => $grads[ $value ]['css'], 'dark' => idt_surface_is_dark( $grads[ $value ]['stops'] ) );
	}
	$hex = idt_color_hex( $value );
	return array( 'fill' => $hex, 'dark' => '' !== $hex && idt_surface_is_dark( $hex ) );
}

/**
 * Zerlegt „Beschriftung | Link | Beschreibung"-Zeilen in Link-Datensätze.
 * Link und Beschreibung sind optional; leere Zeilen werden übersprungen.
 */
function idt_parse_link_lines( $text ) {
	$links = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) { continue; }
		$parts = array_map( 'trim', explode( '|', $line, 3 ) );
		$links[] = array(
			'label' => $parts[0],
			'href'  => ( isset( $parts[1] ) && '' !== $parts[1] ) ? $parts[1] : '#',
			'desc'  => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
	return $links;
}

/**
 * Linkliste als Reihen — Titel, Kurzbeschreibung und Pfeil, ganzflächig
 * klickbar, getrennt durch Haarlinien.
 *
 * Gemeinsames Markup von [themenblock] und [buttonstack]: beide setzen die
 * Liste in eine Fläche, deren Farbschema (--tb-*) sie erben, damit Schrift
 * und Linien zum Untergrund passen.
 */
function idt_link_list_html( $links, $label = '' ) {
	if ( ! $links ) { return ''; }
	$label = '' !== $label ? $label : __( 'Weiterführende Links', 'idt' );
	$out   = '<nav class="idt-themenblock__list" aria-label="' . esc_attr( $label ) . '">';
	foreach ( $links as $link ) {
		$desc = '' !== $link['desc'] ? '<span class="idt-themenblock__rowdesc">' . esc_html( $link['desc'] ) . '</span>' : '';
		$out .= '<a class="idt-themenblock__row" href="' . esc_url( $link['href'] ) . '">' .
			'<span class="idt-themenblock__rowtext">' .
			'<span class="idt-themenblock__rowtitle">' . esc_html( $link['label'] ) . '</span>' . $desc .
			'</span><span class="idt-themenblock__arrow">' . idt_icon( 'arrow', 20 ) . '</span></a>';
	}
	return $out . '</nav>';
}

/**
 * Themenblock — farbige Fläche mit optionalem Eyebrow, Überschrift, Texten
 * und einer beliebig langen Linkliste.
 *
 *   [themenblock bg="ink" eyebrow="Bereich 02 · Unsere Stimme" title="Unser Plan"
 *                lead="Wofür die Initiative eintritt."]
 *   Freier Fließtext (optional, mehrere Absätze möglich).
 *   ---
 *   Die Vision | /vision/ | Wie ein verlässliches Angebot 2035 aussieht
 *   Wo es hakt | /engpaesse/ | Engpässe, Fristen und offene Entscheidungen
 *   [/themenblock]
 *
 * Alles vor der Trennzeile („---") ist Fließtext, alles danach die Linkliste
 * („Beschriftung | Link | Beschreibung", eine Zeile je Eintrag). Ohne
 * Trennzeile gilt der ganze Inhalt als Linkliste.
 *
 * bg nimmt einen Markennamen (ink, paper, paper-2, violet, cyan, yellow,
 * gray, violet-soft, cyan-soft, yellow-soft) oder einen freien Hex-Wert;
 * die Schriftfarbe (hell/dunkel) ergibt sich automatisch aus dem Kontrast.
 */
function idt_sc_themenblock( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'bg'      => 'ink',
		'eyebrow' => '',
		'title'   => '',
		'lead'    => '',
		'text'    => '',
		'level'   => 2,
	), $atts, 'themenblock' );

	/* Inhalt in Fließtext und Linkliste trennen (wpautop-Reste zuvor entfernen,
	 * sonst stecken <p>/<br> in den Zeilen der Liste). */
	$content = idt_strip_autop( $content );
	$parts   = preg_split( '/^\s*-{3,}\s*$/m', $content, 2 );
	$body    = ( count( $parts ) > 1 ) ? $parts[0] : '';
	$lines   = ( count( $parts ) > 1 ) ? $parts[1] : $parts[0];
	$body    = trim( $atts['text'] . "\n\n" . $body );

	$hex   = idt_color_hex( $atts['bg'] );
	if ( '' === $hex ) { $hex = '#00373C'; }
	$class = 'idt-themenblock idt-themenblock--' . ( idt_surface_is_dark( $hex ) ? 'dark' : 'light' );
	$level = min( 4, max( 2, (int) $atts['level'] ) );

	$out  = '<div class="' . esc_attr( $class ) . '" style="--tb-bg:' . esc_attr( $hex ) . '">';
	$out .= '<div class="idt-themenblock__head">';
	if ( '' !== $atts['eyebrow'] ) {
		$out .= '<span class="idt-themenblock__eyebrow">' . esc_html( $atts['eyebrow'] ) . '</span>';
	}
	if ( '' !== $atts['title'] ) {
		$out .= '<h' . $level . ' class="idt-themenblock__title">' . esc_html( $atts['title'] ) . '</h' . $level . '>';
	}
	if ( '' !== $atts['lead'] ) {
		$out .= '<p class="idt-themenblock__lead">' . wp_kses_post( do_shortcode( $atts['lead'] ) ) . '</p>';
	}
	if ( '' !== $body ) {
		$out .= '<div class="idt-themenblock__text">' . wp_kses_post( do_shortcode( wpautop( $body ) ) ) . '</div>';
	}
	$out .= '</div>';

	$links = idt_parse_link_lines( $lines );
	if ( $links ) {
		$out .= idt_link_list_html( $links, $atts['title'] );
	}

	return $out . '</div>';
}
add_shortcode( 'themenblock', 'idt_sc_themenblock' );

/**
 * Button-Stack — der Themenblock, reduziert auf seine Linkliste.
 *
 *   [buttonstack bg="ink"]
 *   Die Vision | /vision/ | Wie ein verlässliches Angebot 2035 aussieht
 *   Wo es hakt | /engpaesse/ | Engpässe, Fristen und offene Entscheidungen
 *   [/buttonstack]
 *
 * Gleiche Reihen wie im Themenblock (Titel, Kurzbeschreibung, Pfeil,
 * Haarlinien), nur ohne Eyebrow, Überschrift und Vortext: eine
 * farbige Fläche, die nur aus den Links besteht. Die Kurzbeschreibung (drittes
 * Feld) ist wie dort optional.
 *
 * bg und Schriftfarbe verhalten sich identisch zum Themenblock: Markenname
 * oder Hex-Wert, das Farbschema (hell/dunkel) ergibt sich aus dem Kontrast.
 * label setzt die Vorlesebeschriftung der Liste (aria-label).
 */
function idt_sc_buttonstack( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'bg'    => 'ink',
		'label' => '',
	), $atts, 'buttonstack' );

	$links = idt_parse_link_lines( idt_strip_autop( $content ) );
	if ( ! $links ) { return ''; }

	$hex   = idt_color_hex( $atts['bg'] );
	if ( '' === $hex ) { $hex = '#00373C'; }
	$class = 'idt-themenblock idt-buttonstack idt-themenblock--' . ( idt_surface_is_dark( $hex ) ? 'dark' : 'light' );

	return '<div class="' . esc_attr( $class ) . '" style="--tb-bg:' . esc_attr( $hex ) . '">' .
		idt_link_list_html( $links, $atts['label'] ) . '</div>';
}
add_shortcode( 'buttonstack', 'idt_sc_buttonstack' );

/**
 * News-Karte („Aus der Initiative").
 * [newscard tag="Stellungnahme" color="violet" date="12.02.2026" href="#"]Schlagzeile[/newscard]
 */
function idt_sc_newscard( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'tag' => '', 'color' => 'violet', 'date' => '', 'href' => '#' ), $atts, 'newscard' );
	$a    = idt_accent( $atts['color'] );
	$tag  = $atts['tag'] ? '<span class="idt-tag" style="color:' . $a['text'] . ';background:' . $a['soft'] . ';border-color:' . $a['border'] . '">' . esc_html( $atts['tag'] ) . '</span>' : '';
	$date = $atts['date'] ? '<span class="idt-newscard__date">' . esc_html( $atts['date'] ) . '</span>' : '';
	return '<a class="idt-newscard" href="' . esc_url( $atts['href'] ) . '">' .
		'<div class="idt-newscard__meta">' . $tag . $date . '</div>' .
		'<h3>' . wp_kses_post( do_shortcode( $content ) ) . '</h3>' .
		'<span class="idt-newscard__more">Weiterlesen ' . idt_icon( 'arrow', 16 ) . '</span></a>';
}
add_shortcode( 'newscard', 'idt_sc_newscard' );

/**
 * Social-Icon-Link (quadratische Kachel im Pill-Stil).
 * [social platform="x" href="https://x.com/…" style="beige"]
 * Plattformen: x | facebook | instagram | linkedin | youtube | mastodon | bluesky | rss
 * Stile: '' (Outline) | beige (gefüllte Papierkachel mit cyanem Zeichen — die
 * Variante für Verlaufs- und Bildflächen, s. style.css 6c).
 */
function idt_sc_social( $atts ) {
	$atts  = shortcode_atts( array( 'platform' => 'x', 'href' => '#', 'style' => '' ), $atts, 'social' );
	$label = ucfirst( $atts['platform'] );
	$cls   = 'idt-social__icon';
	if ( 'beige' === $atts['style'] ) { $cls .= ' idt-social__icon--beige'; }
	return '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $atts['href'] ) . '" aria-label="' . esc_attr( $label ) . '" rel="me noopener">' .
		idt_icon( $atts['platform'], 20 ) . '</a>';
}
add_shortcode( 'social', 'idt_sc_social' );

/**
 * Social-Leiste — eine Reihe von Social-Icons.
 * Inhalt: eine Zeile je Icon, „Plattform | Link" (dieselbe Schreibweise wie
 * bei [pillstack]):
 *   [socialrow style="beige" align="center"]
 *   x | https://x.com/…
 *   mastodon | https://…
 *   [/socialrow]
 * style gibt den Stil an alle Icons weiter, align richtet die Reihe aus.
 */
function idt_sc_socialrow( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'style' => '', 'align' => 'left' ), $atts, 'socialrow' );
	$align = in_array( $atts['align'], array( 'left', 'center', 'right' ), true ) ? $atts['align'] : 'left';
	$icons = '';
	foreach ( (array) idt_parse_button_lines( $content ) as $line ) {
		$icons .= idt_sc_social( array( 'platform' => $line[0], 'href' => $line[1], 'style' => $atts['style'] ) );
	}
	$cls = 'idt-socialrow idt-socialrow--' . $align;
	if ( 'beige' === $atts['style'] ) { $cls .= ' idt-socialrow--beige'; }
	return '<div class="' . esc_attr( $cls ) . '">' . $icons . '</div>';
}
add_shortcode( 'socialrow', 'idt_sc_socialrow' );

/**
 * Mail-Link — macht aus einer Adresse den fertigen mailto-Link.
 *   [email]kontakt@nextgen-deutschlandtakt.de[/email]
 *   [email address="kontakt@nextgen-deutschlandtakt.de"]Schreib uns[/email]
 *   [email address="…" subject="Mitgliedschaft" icon="false"]Mitglied werden[/email]
 *
 * Ohne address steht die Adresse im Inhalt und wird auch angezeigt — der
 * Redaktionsweg für Impressum und Kontaktseite („Mail: [email]…[/email]").
 * Mit address ist der Inhalt die Beschriftung; bleibt er leer, zeigt der Link
 * wieder die Adresse.
 *
 * subject füllt die Betreffzeile des Mailprogramms vor, icon="false" lässt das
 * Briefzeichen weg (für Links mitten im Satz).
 *
 * Die Adresse läuft durch antispambot(): WordPress schreibt sie in
 * HTML-Entities, sodass im Quelltext kein zusammenhängendes „name@domain"
 * steht, das Adress-Sammler einlesen können. Der Browser setzt sie beim
 * Anzeigen wieder zusammen — für Besucher ändert sich nichts.
 *
 * Deshalb ist der href hier auch mit esc_attr() abgesichert und nicht mit
 * esc_url(): esc_url() würde das „&" der Entities zu „&#038;" machen und den
 * Link damit zerlegen. esc_attr() lässt bestehende Entities stehen (kein
 * Doppel-Kodieren) und maskiert alles, was aus dem Attribut ausbrechen könnte.
 * Die Adresse selbst hat sanitize_email() vorher schon eingegrenzt.
 */
function idt_sc_email( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'address' => '', 'subject' => '', 'icon' => 'true' ), $atts, 'email' );

	/* Adresse aus dem Attribut — oder, im häufigeren Fall, aus dem Inhalt. */
	$from_att = '' !== trim( (string) $atts['address'] );
	$address  = sanitize_email( trim( wp_strip_all_tags( $from_att ? $atts['address'] : $content ) ) );

	/* sanitize_email() gibt einen leeren String zurück, wenn nichts Gültiges
	   übrig bleibt. Dann bleibt der Text stehen, statt einen tauben Link zu
	   bauen — so sieht die Redaktion im Frontend, dass etwas fehlt. */
	if ( '' === $address ) {
		return wp_kses_post( do_shortcode( $content ) );
	}

	/* Beschriftung: bei gesetztem address der Inhalt, sonst die Adresse selbst
	   (ebenfalls verschleiert — sie steht ja sichtbar auf der Seite). */
	$label = $from_att ? trim( $content ) : '';
	$label = '' !== $label ? wp_kses_post( do_shortcode( $label ) ) : antispambot( $address );

	$href = 'mailto:' . antispambot( $address );
	if ( '' !== trim( (string) $atts['subject'] ) ) {
		$href .= '?subject=' . rawurlencode( $atts['subject'] );
	}

	$icon = in_array( strtolower( (string) $atts['icon'] ), array( 'false', '0', 'no' ), true ) ? '' : idt_icon( 'mail', 18 );

	return '<a class="idt-email" href="' . esc_attr( $href ) . '">' . $icon . '<span>' . $label . '</span></a>';
}
add_shortcode( 'email', 'idt_sc_email' );
/* „Mail" als zweite Schreibweise — dieselbe Funktion, damit auch
   [mail]…[/mail] trägt (der Fuß der Seite schreibt „Mail:", nicht „E-Mail:"). */
add_shortcode( 'mail', 'idt_sc_email' );

/**
 * Neueste Beiträge, optional auf Schlagwörter und/oder Kategorien eingegrenzt
 * (Slugs, kommagetrennt). Sind beide gesetzt, müssen Beiträge zu beiden passen
 * (UND-Verknüpfung). Gemeinsame Grundlage von [neuigkeiten] und [beitragsliste].
 *
 * @param int    $count    Anzahl Beiträge (1–12).
 * @param string $tag      Schlagwort-Slugs, kommagetrennt.
 * @param string $category Kategorie-Slugs, kommagetrennt.
 * @return WP_Post[]
 */
function idt_query_posts_by_terms( $count = 3, $tag = '', $category = '' ) {
	$query = array(
		'numberposts'      => max( 1, min( 12, (int) $count ) ),
		'suppress_filters' => false,
	);
	/* Optionaler Schlagwort-Filter: kommagetrennte Slugs (oder Namen). */
	if ( '' !== trim( (string) $tag ) ) {
		$slugs = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $tag ) ) ) );
		if ( $slugs ) {
			$query['tag'] = implode( ',', $slugs );
		}
	}
	/* Optionaler Kategorie-Filter: kommagetrennte Slugs, in Term-IDs aufgelöst. */
	if ( '' !== trim( (string) $category ) ) {
		$cat_slugs = array_filter( array_map( 'sanitize_title', array_map( 'trim', explode( ',', $category ) ) ) );
		$cat_ids   = array();
		foreach ( $cat_slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				$cat_ids[] = (int) $term->term_id;
			}
		}
		if ( $cat_ids ) {
			$query['category__in'] = $cat_ids;
		}
	}
	return get_posts( $query );
}

/**
 * Dynamische Beitragsübersicht als News-Karten-Reihe („Aus der Initiative").
 * Zeigt automatisch die neuesten Beiträge — dieselbe Darstellung wie auf der
 * Startseite, aber als wiederverwendbares Element für jede Seite. Mit tag=""
 * bzw. category="" lässt sich die Auswahl auf ein oder mehrere Schlagwörter
 * bzw. Kategorien (Slugs, kommagetrennt) eingrenzen; sind beide gesetzt,
 * müssen Beiträge zu beiden passen (UND-Verknüpfung). Die Chips auf den
 * Karten zeigen die echten Schlagwörter des Beitrags.
 * [neuigkeiten count="3" tag="klimaschutz" category="pressemitteilungen" eyebrow="Aktuelles" title="Aus der Initiative"]
 */
function idt_sc_neuigkeiten( $atts ) {
	$atts = shortcode_atts( array(
		'count'    => 3,
		'tag'      => '',
		'category' => '',
		'eyebrow'  => 'Aktuelles',
		'title'    => 'Aus der Initiative',
	), $atts, 'neuigkeiten' );

	$posts = idt_query_posts_by_terms( (int) $atts['count'], $atts['tag'], $atts['category'] );
	if ( ! $posts ) { return ''; }

	ob_start();
	?>
	<div class="idt-neuigkeiten">
		<?php if ( $atts['eyebrow'] || $atts['title'] ) : ?>
		<div class="idt-neuigkeiten__head">
			<?php if ( $atts['eyebrow'] ) : ?><span class="idt-eyebrow"><?php echo esc_html( $atts['eyebrow'] ); ?></span><?php endif; ?>
			<?php if ( $atts['title'] ) : ?><h2><?php echo esc_html( $atts['title'] ); ?></h2><?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="idt-neuigkeiten__grid">
			<?php
			/* $post muss das globale WP-Post-Objekt überschreiben, damit
			 * setup_postdata()/the_title()/the_permalink() den jeweiligen
			 * Beitrag sehen — sonst zeigen alle Karten die aktuelle Seite. */
			global $post;
			foreach ( $posts as $post ) : setup_postdata( $post );
				/* Echte Schlagwörter des Beitrags — hier nicht verlinkt, weil die
				 * ganze Karte bereits ein <a> ist (kein verschachteltes <a>).
				 * Gefiltert wird über die Filterleiste im Archiv bzw. tag="". */
				$card_tags = idt_post_tags_html( $post->ID, 2, false );
				?>
				<a class="idt-newscard" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<span class="idt-newscard__img"><?php the_post_thumbnail( 'medium_large' ); ?></span>
					<?php endif; ?>
					<div class="idt-newscard__meta">
						<?php echo $card_tags; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="idt-newscard__date"><?php echo esc_html( get_the_date() ); ?></span>
					</div>
					<h3><?php the_title(); ?></h3>
					<span class="idt-newscard__more">Weiterlesen <?php echo idt_icon( 'arrow', 16 ); // phpcs:ignore ?></span>
				</a>
			<?php endforeach; wp_reset_postdata(); ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'neuigkeiten', 'idt_sc_neuigkeiten' );

/**
 * Chips eines Beitrags für die Beitragsliste — je nach Pflegepraxis der
 * Redaktion Kategorien oder Schlagwörter.
 *
 * @param int    $post_id  Beitrag.
 * @param string $terms    'auto' (Kategorien, sonst Schlagwörter) | 'category' | 'tag' | 'none'.
 * @param int    $limit    Maximale Anzahl Chips.
 * @param bool   $link     Chips verlinken.
 */
function idt_postlist_chips( $post_id, $terms = 'auto', $limit = 2, $link = true ) {
	if ( 'none' === $terms ) {
		return '';
	}
	if ( 'tag' === $terms ) {
		return idt_post_terms_html( $post_id, 'post_tag', $limit, $link );
	}
	$cats = idt_post_terms_html( $post_id, 'category', $limit, $link );
	if ( 'category' === $terms || '' !== $cats ) {
		return $cats;
	}
	/* 'auto': Kategorien haben Vorrang, weil sie die Beitragsart benennen
	 * („Position", „Pressemitteilung", „Verein"); ohne vergebene Kategorie
	 * fällt die Liste auf die Schlagwörter zurück. */
	return idt_post_terms_html( $post_id, 'post_tag', $limit, $link );
}

/**
 * Beitragsliste im Zeilen-Layout („Aktuelles").
 *
 * Eine ruhige, textbetonte Übersicht: links Datum und Chip(s), rechts Titel und
 * Anriss, dazwischen Haarlinien. Anders als die News-Karten ([neuigkeiten])
 * verträgt sie beliebig viele Beiträge, ohne unruhig zu wirken — deshalb ist
 * sie die Darstellung der Beitragsübersicht (index.php) und über den Shortcode
 * [beitragsliste] zugleich als Abschnitt für beliebige Seiten einsetzbar.
 *
 * @param WP_Post[] $posts Beiträge (z. B. $wp_query->posts).
 * @param array     $args  title, more_url, more_label, terms, excerpt_words, level.
 */
function idt_render_postlist( $posts, $args = array() ) {
	if ( empty( $posts ) ) {
		return '';
	}
	$args = wp_parse_args( $args, array(
		'title'         => '',        /* Kopfzeile links (leer = kein Kopf) */
		'more_url'      => '',        /* Ziel des Links rechts im Kopf */
		'more_label'    => __( 'Alle Beiträge', 'idt' ),
		'terms'         => 'auto',    /* auto | category | tag | none */
		'excerpt_words' => 26,
		/* Überschriftenebene der Beitragstitel: mit Kopfzeile (h2) eine Stufe
		 * tiefer, ohne Kopfzeile direkt unter der Seitenüberschrift (h1). */
		'level'         => '',
	) );
	$level = $args['level'] ? $args['level'] : ( $args['title'] ? 'h3' : 'h2' );
	$level = in_array( $level, array( 'h2', 'h3', 'h4' ), true ) ? $level : 'h3';

	ob_start();
	?>
	<section class="idt-postlist">
		<?php if ( $args['title'] || $args['more_url'] ) : ?>
			<div class="idt-postlist__head">
				<?php if ( $args['title'] ) : ?>
					<h2 class="idt-postlist__title"><?php echo esc_html( $args['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $args['more_url'] ) : ?>
					<a class="idt-postlist__more" href="<?php echo esc_url( $args['more_url'] ); ?>"><?php echo esc_html( $args['more_label'] ); ?> <?php echo idt_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<ul class="idt-postlist__items">
			<?php foreach ( $posts as $p ) :
				$chips   = idt_postlist_chips( $p->ID, $args['terms'] );
				$excerpt = wp_trim_words( get_the_excerpt( $p ), (int) $args['excerpt_words'], '…' );
				?>
				<li class="idt-postlist__item">
					<div class="idt-postlist__aside">
						<time class="idt-postlist__date" datetime="<?php echo esc_attr( get_the_date( 'c', $p ) ); ?>"><?php echo esc_html( get_the_date( '', $p ) ); ?></time>
						<?php if ( $chips ) : ?>
							<div class="idt-postlist__chips"><?php echo $chips; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php endif; ?>
						<?php if ( has_post_thumbnail( $p ) ) : ?>
							<a class="idt-postlist__thumb" href="<?php echo esc_url( get_permalink( $p ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo get_the_post_thumbnail( $p, 'medium' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php endif; ?>
					</div>
					<div class="idt-postlist__body">
						<<?php echo $level; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="idt-postlist__headline"><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></<?php echo $level; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php if ( $excerpt ) : ?>
							<p class="idt-postlist__excerpt"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Beitragsliste als Abschnitt für beliebige Seiten — dieselbe Darstellung wie
 * die Beitragsübersicht, mit Kopfzeile („Aktuelles") und Link auf alle Beiträge.
 * Auswahl wie bei [neuigkeiten] über tag=""/category="" (Slugs, kommagetrennt).
 * [beitragsliste count="3" title="Aktuelles" more="Alle Beiträge" tag="" category="" chips="auto"]
 */
function idt_sc_beitragsliste( $atts ) {
	$atts = shortcode_atts( array(
		'count'    => 3,
		'tag'      => '',
		'category' => '',
		'title'    => 'Aktuelles',
		'more'     => 'Alle Beiträge',
		'more_url' => '',
		'chips'    => 'auto',
	), $atts, 'beitragsliste' );

	$posts = idt_query_posts_by_terms( (int) $atts['count'], $atts['tag'], $atts['category'] );
	if ( ! $posts ) {
		return '';
	}

	return idt_render_postlist( $posts, array(
		'title'      => $atts['title'],
		'more_label' => $atts['more'],
		'more_url'   => '' !== $atts['more'] ? ( $atts['more_url'] ? $atts['more_url'] : idt_blog_url() ) : '',
		'terms'      => $atts['chips'],
	) );
}
add_shortcode( 'beitragsliste', 'idt_sc_beitragsliste' );

/* =========================================================================
 * Logo-Sperrsatz — Wortmarke links, Signet rechts
 * ====================================================================== */

/**
 * Vorgaben des Logo-Sperrsatzes — eine Stelle für Shortcode und Block.
 *
 * size  = Durchmesser des Signets in px; alles andere (Schriftgrad,
 *         Zeilenabstand, Abstand zur Wortmarke) rechnet sich daraus.
 * color = '' erbt die Schriftfarbe der Umgebung (auf der Verlaufsseite also
 *         Papier), 'paper'/'ink' setzen sie fest.
 */
function idt_logo_defaults() {
	return array(
		'size'  => 96,
		'color' => '',
		'href'  => '',
		'align' => 'center',
	);
}

/**
 * Das Signet als Inline-SVG: das Marken-Raster im Kreis.
 *
 * Die Geometrie steht in einem 148×148-Koordinatensystem (Kreisdurchmesser =
 * Kantenlänge), damit sie unabhängig von der Darstellungsgröße bleibt: die
 * dunkle Scheibe, darüber die beiden Marken-Balken mit halbrunden Enden im
 * Verlauf Cyan→Violett, darüber die vier Papierlinien des Rasters. Alles
 * Überstehende beschneidet der Kreis (clipPath) — die Balken laufen deshalb
 * bewusst über den Rand hinaus.
 *
 * Inline statt als Bilddatei, damit die Marke die Farbtoken des Themes nutzt
 * (Konvention 4) und in jeder Größe scharf bleibt. Die IDs müssen je Aufruf
 * eindeutig sein, sonst greift bei mehreren Logos auf einer Seite das erste
 * Verlaufs-/Clip-Element für alle.
 */
function idt_logo_signet() {
	static $count = 0;
	++$count;
	$grad = 'idt-logo-verlauf-' . $count;
	$clip = 'idt-logo-kreis-' . $count;

	return '<svg class="idt-logo__signet" viewBox="0 0 148 148" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
		. '<defs>'
		. '<linearGradient id="' . $grad . '" x1="0" y1="0" x2="148" y2="0" gradientUnits="userSpaceOnUse">'
		/* Bis knapp zur Mitte reines Cyan, danach der Übergang ins Violett —
		   so trifft der Verlauf die Marke, statt über die ganze Breite zu
		   mitteln. */
		. '<stop offset="0" stop-color="var(--idt-cyan, #00DCFA)"/>'
		. '<stop offset=".47" stop-color="var(--idt-cyan, #00DCFA)"/>'
		. '<stop offset=".9" stop-color="var(--idt-violet, #6E50FA)"/>'
		. '<stop offset="1" stop-color="var(--idt-violet, #6E50FA)"/>'
		. '</linearGradient>'
		. '<clipPath id="' . $clip . '"><circle cx="74" cy="74" r="74"/></clipPath>'
		. '</defs>'
		. '<g clip-path="url(#' . $clip . ')">'
		. '<circle cx="74" cy="74" r="74" fill="var(--idt-ink, #00373C)"/>'
		/* Langer, leicht ansteigender Balken (der „Horizont") und die kurze
		   Pille darunter — beide mit halbrunden Enden wie die Wortmarke. */
		. '<line x1="-12" y1="53.8" x2="160" y2="44" stroke="url(#' . $grad . ')" stroke-width="24" stroke-linecap="round"/>'
		. '<line x1="79" y1="94.5" x2="160" y2="94.5" stroke="url(#' . $grad . ')" stroke-width="26" stroke-linecap="round"/>'
		/* Die vier Senkrechten liegen oben auf und queren die Balken. */
		. '<g fill="var(--idt-paper, #FFF6F0)">'
		. '<rect x="32" y="0" width="4" height="148"/>'
		. '<rect x="56" y="0" width="4" height="148"/>'
		. '<rect x="76.5" y="0" width="4" height="148"/>'
		. '<rect x="111.5" y="0" width="4" height="148"/>'
		. '</g>'
		. '</g></svg>';
}

/**
 * Logo-Sperrsatz — dreizeilige Wortmarke, rechts daneben das Signet.
 *
 * Die Variante der Marke für dunkle und farbige Flächen (Verlaufsseite,
 * Einschub, Bild): Die Schrift ist echter Text in der Theme-Schrift Inter,
 * kein Bild — sie bleibt dadurch scharf, vorlesbar und nimmt die Schriftfarbe
 * ihrer Umgebung an.
 *
 * @param array $args size (px), color ('' | paper | ink), href, align.
 */
function idt_render_logo( $args = array() ) {
	$args  = wp_parse_args( $args, idt_logo_defaults() );
	$size  = max( 32, min( 400, (int) $args['size'] ) );
	$align = in_array( $args['align'], array( 'left', 'center', 'right' ), true ) ? $args['align'] : 'center';

	$class = 'idt-logo';
	if ( in_array( $args['color'], array( 'paper', 'ink' ), true ) ) {
		$class .= ' idt-logo--' . $args['color'];
	}

	$mark = '<span class="idt-logo__wordmark">'
		. '<span class="idt-logo__line">Initiative</span>'
		. '<span class="idt-logo__line">Deutschland</span>'
		. '<span class="idt-logo__line idt-logo__line--takt">Takt</span>'
		. '</span>' . idt_logo_signet();

	$attr = ' class="' . esc_attr( $class ) . '" style="--logo-size:' . $size . 'px"';
	$inner = '' !== $args['href']
		? '<a href="' . esc_url( $args['href'] ) . '"' . $attr . ' rel="home">' . $mark . '</a>'
		: '<span' . $attr . '>' . $mark . '</span>';

	return '<div class="idt-logo-outer" style="text-align:' . esc_attr( $align ) . '">' . $inner . '</div>';
}

/**
 * Logo-Sperrsatz als Shortcode.
 * [logo size="120" color="paper" href="/" align="center"]
 */
function idt_sc_logo( $atts ) {
	$atts = shortcode_atts( idt_logo_defaults(), $atts, 'logo' );
	return idt_render_logo( $atts );
}
add_shortcode( 'logo', 'idt_sc_logo' );
