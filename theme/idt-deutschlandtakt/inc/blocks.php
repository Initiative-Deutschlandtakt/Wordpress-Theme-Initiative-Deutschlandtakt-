<?php
/**
 * Native Gutenberg-Blöcke für die IDT-Stilelemente.
 *
 * Jedes Element ist ein dynamischer (server-gerenderter) Block, der die
 * bestehenden Shortcode-Funktionen wiederverwendet. Dadurch:
 *  - erscheinen die Elemente zuverlässig im „/"-Befehlsmenü und im Inserter,
 *  - sind sie für Nicht-HTML-Menschen über Formularfelder + Live-Vorschau
 *    bearbeitbar (kein roher Shortcode-Text mehr).
 *
 * Single Source of Truth ist idt_blocks_config(); die Feld-Metadaten werden
 * per wp_localize_script an assets/blocks.js übergeben, die Render-Logik
 * bleibt serverseitig (Shortcode-Reuse).
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Auswahlliste für das Feld „Hintergrund" (bg): keine Angabe, die
 * Markenflächen aus idt_color_hex() und die Markenverläufe aus
 * idt_surface_gradients(). Button und Konzept-Karte teilen sich die Liste,
 * damit beide dieselben Flächen anbieten; ein freier Hex-Wert bleibt über die
 * Shortcode-Schreibweise (bg="#RRGGBB") möglich.
 */
function idt_bg_options() {
	return array(
		array( 'label' => __( 'Standard (aus dem Stil)', 'idt' ), 'value' => '' ),
		array( 'label' => __( 'Verlauf Cyan → Violett', 'idt' ), 'value' => 'cyan-violet' ),
		array( 'label' => __( 'Verlauf Violett → Cyan', 'idt' ), 'value' => 'violet-cyan' ),
		array( 'label' => __( 'Violett', 'idt' ), 'value' => 'violet' ),
		array( 'label' => __( 'Cyan', 'idt' ), 'value' => 'cyan' ),
		array( 'label' => __( 'Gelb', 'idt' ), 'value' => 'yellow' ),
		array( 'label' => __( 'Ink (dunkles Teal)', 'idt' ), 'value' => 'ink' ),
		array( 'label' => __( 'Papier', 'idt' ), 'value' => 'paper' ),
		array( 'label' => __( 'Papier 2 (wärmer)', 'idt' ), 'value' => 'paper-2' ),
		array( 'label' => __( 'Papier 3 (kräftiger)', 'idt' ), 'value' => 'paper-3' ),
		array( 'label' => __( 'Violett hell', 'idt' ), 'value' => 'violet-soft' ),
		array( 'label' => __( 'Cyan hell', 'idt' ), 'value' => 'cyan-soft' ),
		array( 'label' => __( 'Gelb hell', 'idt' ), 'value' => 'yellow-soft' ),
		array( 'label' => __( 'Grau', 'idt' ), 'value' => 'gray' ),
	);
}

/**
 * Block-Definitionen: Sidebar-Felder (für die UI) + Render (Shortcode-Reuse).
 * Feldtypen: text | textarea | select | toggle | range | color
 */
function idt_blocks_config() {
	return array(
		'lead' => array(
			'title'    => __( 'Lead-Absatz', 'idt' ),
			'icon'     => 'editor-paragraph',
			'keywords' => array( 'lead', 'einleitung', 'intro', 'dt' ),
			'fields'   => array(
				array( 'key' => 'text', 'label' => __( 'Text', 'idt' ), 'type' => 'textarea', 'default' => 'Ein hervorgehobener Einleitungsabsatz, der den Text eröffnet.' ),
			),
			'render'   => function ( $a ) { return idt_sc_lead( array(), $a['text'] ); },
		),
		'callout' => array(
			'title'    => __( 'Callout / Hinweisbox', 'idt' ),
			'icon'     => 'info',
			'keywords' => array( 'callout', 'hinweis', 'box', 'dt' ),
			'fields'   => array(
				array( 'key' => 'type', 'label' => __( 'Farbe', 'idt' ), 'type' => 'select', 'default' => 'cyan', 'options' => array(
					array( 'label' => 'Cyan', 'value' => 'cyan' ),
					array( 'label' => 'Violett', 'value' => 'violet' ),
					array( 'label' => 'Gelb', 'value' => 'yellow' ),
					array( 'label' => 'Neutral', 'value' => '' ),
				) ),
				array( 'key' => 'text', 'label' => __( 'Text', 'idt' ), 'type' => 'textarea', 'default' => 'Wichtiger Hinweis …' ),
			),
			'render'   => function ( $a ) { return idt_sc_callout( array( 'type' => $a['type'] ), $a['text'] ); },
		),
		'diagonal' => array(
			'title'    => __( 'Diagonal-Aussageblock', 'idt' ),
			'icon'     => 'format-quote',
			'keywords' => array( 'diagonal', 'aussage', 'dt' ),
			'fields'   => array(
				array( 'key' => 'text', 'label' => __( 'Aussage', 'idt' ), 'type' => 'textarea', 'default' => 'Großer Aussage-Block auf dunklem Grund.' ),
			),
			'render'   => function ( $a ) { return idt_sc_diagonal( array(), $a['text'] ); },
		),
		'card' => array(
			'title'    => __( 'Karte', 'idt' ),
			'icon'     => 'index-card',
			'keywords' => array( 'card', 'karte', 'dt' ),
			'fields'   => array(
				array( 'key' => 'text', 'label' => __( 'Inhalt', 'idt' ), 'type' => 'textarea', 'default' => 'Karteninhalt mit Rahmen und Schatten.' ),
			),
			'render'   => function ( $a ) { return idt_sc_card( array(), $a['text'] ); },
		),
		'stat' => array(
			'title'    => __( 'Kennzahl', 'idt' ),
			'icon'     => 'chart-bar',
			'keywords' => array( 'stat', 'kennzahl', 'zahl', 'dt' ),
			'fields'   => array(
				array( 'key' => 'number', 'label' => __( 'Zahl', 'idt' ), 'type' => 'text', 'default' => '2008' ),
				array( 'key' => 'label', 'label' => __( 'Label', 'idt' ), 'type' => 'text', 'default' => 'gegründet' ),
			),
			'render'   => function ( $a ) { return idt_sc_stat( array( 'number' => $a['number'], 'label' => $a['label'] ) ); },
		),
		'takt' => array(
			'title'    => __( 'Takt-Rhythmus', 'idt' ),
			'icon'     => 'marker',
			'keywords' => array( 'takt', 'rhythmus', 'dt' ),
			'fields'   => array(
				array( 'key' => 'count', 'label' => __( 'Anzahl Punkte', 'idt' ), 'type' => 'range', 'default' => 6, 'min' => 1, 'max' => 24 ),
			),
			'render'   => function ( $a ) { return idt_sc_takt( array( 'count' => $a['count'] ) ); },
		),
		'btn' => array(
			'title'    => __( 'Button', 'idt' ),
			'icon'     => 'button',
			'keywords' => array( 'btn', 'button', 'cta', 'dt' ),
			'fields'   => array(
				array( 'key' => 'text', 'label' => __( 'Beschriftung', 'idt' ), 'type' => 'text', 'default' => 'Mehr erfahren' ),
				array( 'key' => 'href', 'label' => __( 'Link (URL)', 'idt' ), 'type' => 'text', 'default' => '#' ),
				array( 'key' => 'variant', 'label' => __( 'Stil', 'idt' ), 'type' => 'select', 'default' => 'primary', 'options' => array(
					array( 'label' => 'Primary (Violett)', 'value' => 'primary' ),
					array( 'label' => 'Sekundär (Cyan)', 'value' => 'secondary' ),
					array( 'label' => 'Outline', 'value' => 'outline' ),
					array( 'label' => 'Gradient-Rahmen', 'value' => 'gradient' ),
				) ),
				array( 'key' => 'bg', 'label' => __( 'Hintergrund (überschreibt den Stil)', 'idt' ), 'type' => 'select', 'default' => '', 'options' => idt_bg_options() ),
				array( 'key' => 'arrow', 'label' => __( 'Pfeil anzeigen', 'idt' ), 'type' => 'toggle', 'default' => false ),
			),
			'render'   => function ( $a ) { return idt_sc_btn( array( 'href' => $a['href'], 'variant' => $a['variant'], 'bg' => $a['bg'], 'arrow' => $a['arrow'] ? 'true' : '' ), $a['text'] ); },
		),
		'pill' => array(
			'title'    => __( 'Pill-Button', 'idt' ),
			'icon'     => 'button',
			'keywords' => array( 'pill', 'button', 'cta', 'dt' ),
			'fields'   => array(
				array( 'key' => 'text', 'label' => __( 'Beschriftung', 'idt' ), 'type' => 'text', 'default' => 'Mitglied werden' ),
				array( 'key' => 'href', 'label' => __( 'Link (URL)', 'idt' ), 'type' => 'text', 'default' => '#' ),
				array( 'key' => 'style', 'label' => __( 'Stil', 'idt' ), 'type' => 'select', 'default' => '', 'options' => array(
					array( 'label' => 'Outline', 'value' => '' ),
					array( 'label' => 'Solid (gefüllt)', 'value' => 'solid' ),
					array( 'label' => 'Violett-Outline (Gradient-Rand bei Klick)', 'value' => 'violet' ),
				) ),
			),
			'render'   => function ( $a ) { return idt_sc_pill( array( 'href' => $a['href'], 'style' => $a['style'] ), $a['text'] ); },
		),
		'splash' => array(
			'title'    => __( 'Horizont-Splash', 'idt' ),
			'icon'     => 'cover-image',
			'keywords' => array( 'splash', 'hero', 'horizont', 'startseite', 'dt' ),
			'fields'   => array(
				array( 'key' => 'buttons', 'label' => __( 'Links — eine Zeile je Button: Beschriftung | Link (leer = Standard-Links)', 'idt' ), 'type' => 'textarea', 'default' => '' ),
				array( 'key' => 'caption', 'label' => __( 'Schlagzeile unter den Buttons (optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
			),
			'render'   => function ( $a ) { return idt_sc_splash( array( 'caption' => $a['caption'] ), $a['buttons'] ); },
		),
		'splash2' => array(
			'title'    => __( 'Horizont-Splash v2 (Zentriert)', 'idt' ),
			'icon'     => 'cover-image',
			'keywords' => array( 'splash', 'hero', 'zentriert', 'startseite', 'dt' ),
			'fields'   => array(
				array( 'key' => 'buttons', 'label' => __( 'Links — eine Zeile je Button: Beschriftung | Link (leer = Standard-Links)', 'idt' ), 'type' => 'textarea', 'default' => '' ),
				array( 'key' => 'caption', 'label' => __( 'Schlagzeile unter den Links (optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
			),
			'render'   => function ( $a ) { return idt_sc_splash2( array( 'caption' => $a['caption'] ), $a['buttons'] ); },
		),
		'pillstack' => array(
			'title'    => __( 'Pill-Button-Stack', 'idt' ),
			'icon'     => 'button',
			'keywords' => array( 'pillstack', 'stack', 'buttons', 'pill', 'dt' ),
			'fields'   => array(
				array( 'key' => 'buttons', 'label' => __( 'Buttons — eine Zeile je Button: Beschriftung | Link', 'idt' ), 'type' => 'textarea', 'default' => "Über die Initiative | #\nMitglied werden | #\nMehr Inhalte | #" ),
				array( 'key' => 'style', 'label' => __( 'Stil', 'idt' ), 'type' => 'select', 'default' => '', 'options' => array(
					array( 'label' => 'Outline', 'value' => '' ),
					array( 'label' => 'Solid (gefüllt)', 'value' => 'solid' ),
					array( 'label' => 'Violett-Outline (Gradient-Rand bei Klick)', 'value' => 'violet' ),
					array( 'label' => 'Outline hell (für dunklen Grund)', 'value' => 'on-ink' ),
				) ),
				array( 'key' => 'align', 'label' => __( 'Ausrichtung', 'idt' ), 'type' => 'select', 'default' => 'center', 'options' => array(
					array( 'label' => 'Zentriert', 'value' => 'center' ),
					array( 'label' => 'Links', 'value' => 'left' ),
					array( 'label' => 'Rechts', 'value' => 'right' ),
				) ),
				array( 'key' => 'minwidth', 'label' => __( 'Mindestbreite (px, 0 = automatisch)', 'idt' ), 'type' => 'range', 'default' => 0, 'min' => 0, 'max' => 480 ),
			),
			'render'   => function ( $a ) {
				$pills = '';
				foreach ( (array) idt_parse_button_lines( $a['buttons'] ) as $pill ) {
					$pills .= idt_sc_pill( array( 'href' => $pill[1], 'style' => $a['style'] ), $pill[0] );
				}
				return idt_sc_pillstack( array( 'align' => $a['align'], 'minwidth' => $a['minwidth'] ), $pills );
			},
		),
		'concept' => array(
			'title'    => __( 'Konzept-Karte', 'idt' ),
			'icon'     => 'screenoptions',
			'keywords' => array( 'concept', 'konzept', 'karte', 'dt' ),
			'fields'   => array(
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Erst der Fahrplan' ),
				array( 'key' => 'color', 'label' => __( 'Farbe', 'idt' ), 'type' => 'select', 'default' => 'violet', 'options' => array(
					array( 'label' => 'Violett', 'value' => 'violet' ),
					array( 'label' => 'Cyan', 'value' => 'cyan' ),
					array( 'label' => 'Gelb', 'value' => 'yellow' ),
					array( 'label' => 'Ink', 'value' => 'ink' ),
				) ),
				array( 'key' => 'icon', 'label' => __( 'Icon', 'idt' ), 'type' => 'select', 'default' => 'clock', 'options' => array(
					array( 'label' => 'Uhr (clock)', 'value' => 'clock' ),
					array( 'label' => 'Schiene (rail)', 'value' => 'rail' ),
					array( 'label' => 'Netz (netz)', 'value' => 'netz' ),
					array( 'label' => 'Ohne', 'value' => '' ),
				) ),
				array( 'key' => 'bg', 'label' => __( 'Hintergrund der Karte', 'idt' ), 'type' => 'select', 'default' => '', 'options' => idt_bg_options() ),
				array( 'key' => 'href', 'label' => __( 'Link (optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
				array( 'key' => 'text', 'label' => __( 'Text', 'idt' ), 'type' => 'textarea', 'default' => 'Kurzer Beschreibungstext zur Konzept-Karte.' ),
			),
			'render'   => function ( $a ) { return idt_sc_concept( array( 'color' => $a['color'], 'icon' => $a['icon'], 'title' => $a['title'], 'href' => $a['href'], 'bg' => $a['bg'] ), $a['text'] ); },
		),
		'einschub' => array(
			'title'    => __( 'Dunkler Einschub', 'idt' ),
			'icon'     => 'align-full-width',
			'keywords' => array( 'einschub', 'band', 'dunkel', 'dt' ),
			'fields'   => array(
				array( 'key' => 'eyebrow', 'label' => __( 'Eyebrow (Label)', 'idt' ), 'type' => 'text', 'default' => 'Das Vorbild' ),
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Überschrift des Einschubs' ),
				array( 'key' => 'text', 'label' => __( 'Text', 'idt' ), 'type' => 'textarea', 'default' => 'Text des dunklen Einschubs.' ),
				array( 'key' => 'stats', 'label' => __( 'Kennzahlen — eine Zeile je Kennzahl: Zahl | Label (leer = keine)', 'idt' ), 'type' => 'textarea', 'default' => '' ),
			),
			'render'   => function ( $a ) {
				$body = $a['text'];
				/* Kennzahlen hängen als eigener Absatz unter dem Text — dieselbe
				   Struktur, die wpautop im Shortcode-Pendant erzeugt. */
				$stats = '';
				/* Gleicher Zeilen-Parser wie bei den Button-Feldern („A | B"); ohne
				   zweiten Teil liefert er das Link-Standardzeichen „#", was hier
				   schlicht „kein Label" bedeutet. */
				foreach ( (array) idt_parse_button_lines( $a['stats'] ) as $stat ) {
					$label  = '#' === $stat[1] ? '' : $stat[1];
					$stats .= idt_sc_stat( array( 'number' => $stat[0], 'label' => $label ) );
				}
				if ( '' !== $stats ) {
					$body .= "\n\n" . $stats;
				}
				return idt_sc_einschub( array( 'eyebrow' => $a['eyebrow'], 'title' => $a['title'] ), $body );
			},
		),
		'themenblock' => array(
			'title'    => __( 'Themenblock mit Linkliste', 'idt' ),
			'icon'     => 'excerpt-view',
			'keywords' => array( 'themenblock', 'linkliste', 'links', 'bereich', 'farbe', 'dt' ),
			'fields'   => array(
				array( 'key' => 'bg', 'label' => __( 'Hintergrundfarbe', 'idt' ), 'type' => 'color', 'default' => '#00373C' ),
				array( 'key' => 'eyebrow', 'label' => __( 'Eyebrow (Label, optional)', 'idt' ), 'type' => 'text', 'default' => 'Bereich 02 · Unsere Stimme' ),
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Unser Plan' ),
				array( 'key' => 'lead', 'label' => __( 'Einleitung (optional)', 'idt' ), 'type' => 'textarea', 'default' => 'Wofür die Initiative eintritt, was im Weg steht, was jetzt ansteht.' ),
				array( 'key' => 'text', 'label' => __( 'Fließtext (optional)', 'idt' ), 'type' => 'textarea', 'default' => '' ),
				array( 'key' => 'links', 'label' => __( 'Links — eine Zeile je Eintrag: Beschriftung | Link | Beschreibung', 'idt' ), 'type' => 'textarea', 'default' => "Die Vision | # | Wie ein verlässliches Angebot 2035 aussieht\nWo es hakt | # | Engpässe, Fristen und offene Entscheidungen\nPositionen | # | Unsere Stellungnahmen zum Umsetzungsprozess" ),
			),
			'render'   => function ( $a ) {
				return idt_sc_themenblock( array(
					'bg'      => $a['bg'],
					'eyebrow' => $a['eyebrow'],
					'title'   => $a['title'],
					'lead'    => $a['lead'],
					'text'    => $a['text'],
				), $a['links'] );
			},
		),
		'buttonstack' => array(
			'title'    => __( 'Button-Stack (Linkliste)', 'idt' ),
			'icon'     => 'menu-alt',
			'keywords' => array( 'buttonstack', 'button', 'stack', 'linkliste', 'links', 'farbe', 'dt' ),
			'fields'   => array(
				array( 'key' => 'bg', 'label' => __( 'Hintergrundfarbe', 'idt' ), 'type' => 'color', 'default' => '#00373C' ),
				array( 'key' => 'links', 'label' => __( 'Links — eine Zeile je Eintrag: Beschriftung | Link | Beschreibung', 'idt' ), 'type' => 'textarea', 'default' => "Die Vision | # | Wie ein verlässliches Angebot 2035 aussieht\nWo es hakt | # | Engpässe, Fristen und offene Entscheidungen\nPositionen | # | Unsere Stellungnahmen zum Umsetzungsprozess" ),
			),
			'render'   => function ( $a ) {
				return idt_sc_buttonstack( array( 'bg' => $a['bg'] ), $a['links'] );
			},
		),
		'newscard' => array(
			'title'    => __( 'News-Karte', 'idt' ),
			'icon'     => 'megaphone',
			'keywords' => array( 'newscard', 'news', 'meldung', 'dt' ),
			'fields'   => array(
				array( 'key' => 'text', 'label' => __( 'Schlagzeile', 'idt' ), 'type' => 'text', 'default' => 'Schlagzeile der Meldung' ),
				array( 'key' => 'tag', 'label' => __( 'Tag', 'idt' ), 'type' => 'text', 'default' => 'Stellungnahme' ),
				array( 'key' => 'color', 'label' => __( 'Farbe', 'idt' ), 'type' => 'select', 'default' => 'violet', 'options' => array(
					array( 'label' => 'Violett', 'value' => 'violet' ),
					array( 'label' => 'Cyan', 'value' => 'cyan' ),
					array( 'label' => 'Gelb', 'value' => 'yellow' ),
				) ),
				array( 'key' => 'date', 'label' => __( 'Datum', 'idt' ), 'type' => 'text', 'default' => '12.02.2026' ),
				array( 'key' => 'href', 'label' => __( 'Link (URL)', 'idt' ), 'type' => 'text', 'default' => '#' ),
			),
			'render'   => function ( $a ) { return idt_sc_newscard( array( 'tag' => $a['tag'], 'color' => $a['color'], 'date' => $a['date'], 'href' => $a['href'] ), $a['text'] ); },
		),
		'social' => array(
			'title'    => __( 'Social-Icon', 'idt' ),
			'icon'     => 'share',
			'keywords' => array( 'social', 'icon', 'x', 'facebook', 'instagram', 'linkedin', 'youtube', 'mastodon', 'bluesky', 'rss', 'dt' ),
			'fields'   => array(
				array( 'key' => 'platform', 'label' => __( 'Plattform', 'idt' ), 'type' => 'select', 'default' => 'x', 'options' => array(
					array( 'label' => 'X (Twitter)', 'value' => 'x' ),
					array( 'label' => 'Facebook', 'value' => 'facebook' ),
					array( 'label' => 'Instagram', 'value' => 'instagram' ),
					array( 'label' => 'LinkedIn', 'value' => 'linkedin' ),
					array( 'label' => 'YouTube', 'value' => 'youtube' ),
					array( 'label' => 'Mastodon', 'value' => 'mastodon' ),
					array( 'label' => 'Bluesky', 'value' => 'bluesky' ),
					array( 'label' => 'RSS', 'value' => 'rss' ),
				) ),
				array( 'key' => 'href', 'label' => __( 'Link (URL)', 'idt' ), 'type' => 'text', 'default' => '#' ),
			),
			'render'   => function ( $a ) { return idt_sc_social( array( 'platform' => $a['platform'], 'href' => $a['href'] ) ); },
		),
		'sociallinks' => array(
			'title'    => __( 'Social-Leiste', 'idt' ),
			'icon'     => 'share',
			'keywords' => array( 'social', 'leiste', 'icons', 'profile', 'dt' ),
			'fields'   => array(
				array(
					'key'     => 'links',
					'label'   => __( 'Icons — eine Zeile je Icon: Plattform | Link', 'idt' ),
					'type'    => 'textarea',
					'default' => "x | #\nfacebook | #\ninstagram | #\nlinkedin | #\nyoutube | #\nmastodon | #\nbluesky | #\nrss | #",
				),
			),
			'render'   => function ( $a ) { return idt_sc_socialrow( array(), $a['links'] ); },
		),
		'neuigkeiten' => array(
			'title'    => __( 'Beiträge-Übersicht (dynamisch)', 'idt' ),
			'icon'     => 'grid-view',
			'keywords' => array( 'neuigkeiten', 'beiträge', 'news', 'dt' ),
			'fields'   => array(
				array( 'key' => 'count', 'label' => __( 'Anzahl Beiträge', 'idt' ), 'type' => 'range', 'default' => 3, 'min' => 1, 'max' => 12 ),
				array( 'key' => 'tag', 'label' => __( 'Schlagwort-Filter (Slug, optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
				array( 'key' => 'category', 'label' => __( 'Kategorie-Filter (Slug, optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
				array( 'key' => 'eyebrow', 'label' => __( 'Eyebrow', 'idt' ), 'type' => 'text', 'default' => 'Aktuelles' ),
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Aus der Initiative' ),
			),
			'render'   => function ( $a ) { return idt_sc_neuigkeiten( array( 'count' => $a['count'], 'tag' => $a['tag'], 'category' => $a['category'], 'eyebrow' => $a['eyebrow'], 'title' => $a['title'] ) ); },
		),
		'beitragsliste' => array(
			'title'    => __( 'Beitragsliste (Zeilen)', 'idt' ),
			'icon'     => 'list-view',
			'keywords' => array( 'beitragsliste', 'aktuelles', 'beiträge', 'liste', 'dt' ),
			'fields'   => array(
				array( 'key' => 'count', 'label' => __( 'Anzahl Beiträge', 'idt' ), 'type' => 'range', 'default' => 3, 'min' => 1, 'max' => 12 ),
				array( 'key' => 'tag', 'label' => __( 'Schlagwort-Filter (Slug, optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
				array( 'key' => 'category', 'label' => __( 'Kategorie-Filter (Slug, optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Aktuelles' ),
				array( 'key' => 'more', 'label' => __( 'Link rechts (leer = keiner)', 'idt' ), 'type' => 'text', 'default' => 'Alle Beiträge' ),
				array( 'key' => 'chips', 'label' => __( 'Chips', 'idt' ), 'type' => 'select', 'default' => 'auto', 'options' => array(
					array( 'label' => __( 'Automatisch (Kategorie, sonst Schlagwort)', 'idt' ), 'value' => 'auto' ),
					array( 'label' => __( 'Kategorien', 'idt' ), 'value' => 'category' ),
					array( 'label' => __( 'Schlagwörter', 'idt' ), 'value' => 'tag' ),
					array( 'label' => __( 'Keine', 'idt' ), 'value' => 'none' ),
				) ),
			),
			'render'   => function ( $a ) { return idt_sc_beitragsliste( array( 'count' => $a['count'], 'tag' => $a['tag'], 'category' => $a['category'], 'title' => $a['title'], 'more' => $a['more'], 'chips' => $a['chips'] ) ); },
		),
	);
}

/** Leitet das WP-Attribut-Schema aus den Feld-Definitionen ab. */
function idt_block_attributes( $fields ) {
	$attributes = array();
	foreach ( $fields as $f ) {
		$type = 'string';
		if ( 'toggle' === $f['type'] ) {
			$type = 'boolean';
		} elseif ( 'range' === $f['type'] ) {
			$type = 'number';
		}
		$attributes[ $f['key'] ] = array( 'type' => $type, 'default' => $f['default'] );
	}
	return $attributes;
}

/** Registriert Editor-Script, Block-Typen und übergibt die Feld-Metadaten an JS. */
function idt_register_blocks() {
	$defs = idt_blocks_config();

	wp_register_script(
		'idt-blocks',
		get_template_directory_uri() . '/assets/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
		IDT_VERSION,
		true
	);

	$js = array();
	foreach ( $defs as $name => $def ) {
		$attributes  = idt_block_attributes( $def['fields'] );
		/* Farbfelder bekommen die Markenpalette als Vorschläge mit (freie
		 * Farbwahl bleibt über den Farbwähler daneben möglich). */
		foreach ( $def['fields'] as $i => $field ) {
			if ( 'color' === $field['type'] ) {
				$def['fields'][ $i ]['palette'] = idt_brand_palette();
			}
		}
		$js[ $name ] = array(
			'title'      => $def['title'],
			'icon'       => $def['icon'],
			'keywords'   => $def['keywords'],
			'fields'     => $def['fields'],
			'attributes' => $attributes,
		);
		register_block_type( 'idt/' . $name, array(
			'api_version'     => 2,
			'attributes'      => $attributes,
			'render_callback' => $def['render'],
			'editor_script'   => 'idt-blocks',
			'category'        => 'idt',
		) );
	}

	wp_localize_script( 'idt-blocks', 'IDT_BLOCKS', $js );

	idt_register_cards_block();
	idt_register_knotendreieck_block();
}

/**
 * Karten-Raster — Container-Block mit InnerBlocks.
 *
 * Anders als die übrigen Elemente ist das kein server-gerenderter Block: Der
 * Block speichert nur seinen Rahmen (<div class="idt-cards …">), die Karten
 * darin bleiben eigenständige Blöcke und werden weiterhin serverseitig
 * gerendert. So lassen sich die Karten im Editor einzeln bearbeiten, liegen
 * aber in einem gemeinsamen Raster (gleiche Breite, gleiche Höhe, Umbruch).
 */
function idt_register_cards_block() {
	wp_localize_script( 'idt-blocks', 'IDT_CARDS', array(
		'title'   => __( 'Karten-Raster', 'idt' ),
		'label'   => __( 'Spalten (ab Desktop)', 'idt' ),
		'hint'    => __( 'Karten mit „+“ einfügen — z. B. Konzept-Karten. Auf schmalen Bildschirmen bricht das Raster automatisch um.', 'idt' ),
		'options' => array(
			array( 'label' => __( '2 Spalten', 'idt' ), 'value' => '2' ),
			array( 'label' => __( '3 Spalten', 'idt' ), 'value' => '3' ),
			array( 'label' => __( '4 Spalten', 'idt' ), 'value' => '4' ),
			array( 'label' => __( 'Automatisch', 'idt' ), 'value' => 'auto' ),
		),
	) );

	register_block_type( 'idt/kartenraster', array(
		'api_version'   => 2,
		'attributes'    => array( 'cols' => array( 'type' => 'string', 'default' => '3' ) ),
		'editor_script' => 'idt-blocks',
		'category'      => 'idt',
	) );
}

/**
 * Knotendreieck — Block mit eigener Oberfläche.
 *
 * Die übrigen Elemente teilen sich assets/blocks.js: Sidebar-Felder aus
 * idt_blocks_config(), Vorschau über ServerSideRender. Das Knotendreieck
 * passt dort nicht hinein, denn seine Vorschau ist kein Stück Markup, sondern
 * eine laufende Grafik — ServerSideRender würde sie bei jedem Tastendruck neu
 * anfordern und dabei zurücksetzen. Es bekommt darum eine eigene, ebenfalls
 * build-freie Oberfläche in blocks/knotendreieck/editor.js, die das Custom
 * Element direkt einsetzt.
 *
 * An Konvention 2 ändert das nichts: das Frontend-Markup kommt aus
 * idt_render_knotendreieck() (inc/shortcodes.php), aufgerufen von
 * blocks/knotendreieck/render.php und vom Shortcode [knotendreieck].
 */
function idt_register_knotendreieck_block() {
	$uri = get_template_directory_uri() . '/blocks/knotendreieck';

	/* block.json verweist mit "script" und "editorScript" auf genau diese
	 * beiden Handles. Ohne sie suchte WordPress neben den Dateien eine
	 * *.asset.php aus einem Build-Schritt — den es hier nicht gibt.
	 *
	 * "script" (statt "viewScript") ist Absicht: WordPress lädt es im
	 * Frontend und im Editor-Rahmen, und nur so zeichnet die Vorschau im
	 * Editor dieselbe Grafik wie die Seite. Dass sie dort nicht von selbst
	 * losläuft, regelt editor.js über autoplay="false" — eine Animation, die
	 * beim Schreiben im Augenwinkel zappelt, will niemand. */
	wp_register_script( 'idt-knotendreieck-view', $uri . '/view.js', array(), IDT_VERSION, true );

	wp_register_script(
		'idt-knotendreieck-editor',
		$uri . '/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		IDT_VERSION,
		true
	);

	/* Attribute, Titel, Kategorie und der Verweis auf render.php stehen in
	 * blocks/knotendreieck/block.json. */
	register_block_type( get_template_directory() . '/blocks/knotendreieck' );
}

add_action( 'init', 'idt_register_blocks' );

/**
 * Öffnendes bzw. schließendes Markup des Karten-Rasters für Vorlagen und
 * Demo-Inhalte (inc/patterns.php, inc/demo-content.php).
 *
 * useBlockProps.save() im Editor-Script schreibt neben den Rasterklassen auch
 * die von WordPress generierte Blockklasse `wp-block-idt-kartenraster` in das
 * gespeicherte Markup. Vorlagen müssen sie deshalb mitliefern: fehlt sie, weicht
 * die Vorlage von dem ab, was save() erzeugt — der Editor hält den Block dann
 * für ungültig und zeigt statt des Rasters die Meldung „Block-Wiederherstellung
 * versuchen". Damit das nicht auseinanderläuft, kommt der Rahmen aus dieser
 * einen Funktion.
 */
function idt_kartenraster_open( $cols = '3' ) {
	return '<!-- wp:idt/kartenraster {"cols":"' . $cols . '"} -->' . "\n"
		. '<div class="wp-block-idt-kartenraster idt-cards idt-cards--' . $cols . '">' . "\n";
}

function idt_kartenraster_close() {
	return "</div>\n" . '<!-- /wp:idt/kartenraster -->';
}

/** Eigene Block-Kategorie „Deutschlandtakt" im Inserter. */
function idt_block_category( $cats ) {
	array_unshift( $cats, array(
		'slug'  => 'idt',
		'title' => __( 'Deutschlandtakt', 'idt' ),
		'icon'  => null,
	) );
	return $cats;
}
add_filter( 'block_categories_all', 'idt_block_category' );

/* -------------------------------------------------------------------------
 * Blockstile für WordPress-Standardblöcke
 * ---------------------------------------------------------------------- */

/**
 * Trenner (core/separator) im Markenlook.
 *
 * Zwei Stile stehen unter „Trenner → Stile" zur Auswahl:
 *  - „Verlauf": Strich im Markenverlauf Violett→Cyan über die volle Textbreite,
 *    ohne dass im Farbbereich erst ein Verlauf gewählt werden muss.
 *  - „Kurzer Strich": kurze Akzentmarke (56 px) am linken Textrand — der
 *    Zwischenstrich zwischen zwei Abschnitten.
 *
 * Gestaltet werden beide in style.css, Abschnitt 7d; dort steht auch, warum
 * ein im Editor gewählter Verlauf ohne diese Regeln grau bleibt.
 */
function idt_register_core_block_styles() {
	register_block_style( 'core/separator', array(
		'name'  => 'idt-verlauf',
		'label' => __( 'Verlauf (Violett → Cyan)', 'idt' ),
	) );

	register_block_style( 'core/separator', array(
		'name'  => 'idt-kurz',
		'label' => __( 'Kurzer Strich', 'idt' ),
	) );
}
add_action( 'init', 'idt_register_core_block_styles' );
