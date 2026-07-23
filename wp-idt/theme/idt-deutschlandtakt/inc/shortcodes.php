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

/** Pill-Button. Stile: '' (Outline) | solid | on-ink (helle Outline für dunklen Grund). */
function idt_sc_pill( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'href' => '#', 'style' => '' ), $atts, 'pill' );
	$cls  = 'pill';
	if ( 'solid' === $atts['style'] )  { $cls .= ' pill--solid'; }
	if ( 'on-ink' === $atts['style'] ) { $cls .= ' pill--on-ink'; }
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
	$style = $min > 0 ? ' style="min-width:' . $min . 'px"' : '';
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
 * Button (eckig, gerahmt) mit Varianten und optionalem Pfeil.
 * [btn href="#" variant="primary" size="lg" arrow="true"]Label[/btn]
 * Varianten: primary | secondary | outline | ghost | inverse
 */
function idt_sc_btn( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'href' => '#', 'variant' => 'primary', 'size' => '', 'arrow' => '' ), $atts, 'btn' );
	$cls  = 'idt-btn idt-btn--' . preg_replace( '/[^a-z]/', '', $atts['variant'] );
	if ( 'lg' === $atts['size'] ) { $cls .= ' idt-btn--lg'; }
	$arrow = ( 'true' === $atts['arrow'] || '1' === $atts['arrow'] ) ? idt_icon( 'arrow', 16 ) : '';
	return '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $atts['href'] ) . '">' . wp_kses_post( do_shortcode( $content ) ) . $arrow . '</a>';
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
 */
function idt_sc_concept( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'color' => 'violet', 'icon' => '', 'title' => '', 'href' => '' ), $atts, 'concept' );
	$a    = idt_accent( $atts['color'] );
	$icon = $atts['icon'] ? '<div class="idt-concept__ic" style="color:' . esc_attr( $a['icon'] ) . '">' . idt_icon( $atts['icon'], 34 ) . '</div>' : '';
	$head = $atts['title'] ? '<h3>' . esc_html( $atts['title'] ) . '</h3>' : '';
	$body = '<div class="idt-concept__body">' . wp_kses_post( do_shortcode( wpautop( $content ) ) ) . '</div>';

	/* Mit href wird die ganze Karte ein Link (inkl. „Mehr erfahren") — wie auf der Startseite. */
	if ( $atts['href'] ) {
		$more = '<span class="idt-newscard__more">Mehr erfahren ' . idt_icon( 'arrow', 16 ) . '</span>';
		return '<a class="idt-concept idt-concept--link" href="' . esc_url( $atts['href'] ) . '" style="border-top-color:' . esc_attr( $a['border'] ) . '">' . $icon . $head . $body . $more . '</a>';
	}
	return '<div class="idt-concept" style="border-top-color:' . esc_attr( $a['border'] ) . '">' . $icon . $head . $body . '</div>';
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
 * Social-Icon-Link (rund, im Pill-Stil).
 * [social platform="x" href="https://x.com/…"]
 * Plattformen: x | facebook | instagram | linkedin | youtube | mastodon | bluesky | rss
 */
function idt_sc_social( $atts ) {
	$atts  = shortcode_atts( array( 'platform' => 'x', 'href' => '#' ), $atts, 'social' );
	$label = ucfirst( $atts['platform'] );
	return '<a class="idt-social__icon" href="' . esc_url( $atts['href'] ) . '" aria-label="' . esc_attr( $label ) . '" rel="me noopener">' .
		idt_icon( $atts['platform'], 20 ) . '</a>';
}
add_shortcode( 'social', 'idt_sc_social' );

/**
 * Dynamische Beitragsübersicht als News-Karten-Reihe („Aus der Initiative").
 * Zeigt automatisch die neuesten Beiträge — dieselbe Darstellung wie auf der
 * Startseite, aber als wiederverwendbares Element für jede Seite.
 * [neuigkeiten count="3" eyebrow="Aktuelles" title="Aus der Initiative"]
 */
function idt_sc_neuigkeiten( $atts ) {
	$atts = shortcode_atts( array(
		'count'   => 3,
		'eyebrow' => 'Aktuelles',
		'title'   => 'Aus der Initiative',
	), $atts, 'neuigkeiten' );

	$posts = get_posts( array( 'numberposts' => max( 1, min( 12, (int) $atts['count'] ) ) ) );
	if ( ! $posts ) { return ''; }

	/* Rotierende Tag-Beschriftung/-Farbe wie in front-page.php. */
	$tagmap = array(
		array( 'Stellungnahme', 'violet' ),
		array( 'Gesetzgebung', 'cyan' ),
		array( 'Prognose', 'yellow' ),
	);

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
			$i = 0; foreach ( $posts as $post ) : setup_postdata( $post );
				$tm = $tagmap[ $i % 3 ]; $a = idt_accent( $tm[1] ); $i++; ?>
				<a class="idt-newscard" href="<?php the_permalink(); ?>">
					<div class="idt-newscard__meta">
						<span class="idt-tag" style="color:<?php echo esc_attr( $a['text'] ); ?>;background:<?php echo esc_attr( $a['soft'] ); ?>;border-color:<?php echo esc_attr( $a['border'] ); ?>"><?php echo esc_html( $tm[0] ); ?></span>
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
