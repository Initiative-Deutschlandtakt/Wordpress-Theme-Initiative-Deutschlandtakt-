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
 * Block-Definitionen: Sidebar-Felder (für die UI) + Render (Shortcode-Reuse).
 * Feldtypen: text | textarea | select | toggle | range
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
				array( 'key' => 'arrow', 'label' => __( 'Pfeil anzeigen', 'idt' ), 'type' => 'toggle', 'default' => false ),
			),
			'render'   => function ( $a ) { return idt_sc_btn( array( 'href' => $a['href'], 'variant' => $a['variant'], 'arrow' => $a['arrow'] ? 'true' : '' ), $a['text'] ); },
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
				array( 'key' => 'href', 'label' => __( 'Link (optional)', 'idt' ), 'type' => 'text', 'default' => '' ),
				array( 'key' => 'text', 'label' => __( 'Text', 'idt' ), 'type' => 'textarea', 'default' => 'Kurzer Beschreibungstext zur Konzept-Karte.' ),
			),
			'render'   => function ( $a ) { return idt_sc_concept( array( 'color' => $a['color'], 'icon' => $a['icon'], 'title' => $a['title'], 'href' => $a['href'] ), $a['text'] ); },
		),
		'einschub' => array(
			'title'    => __( 'Dunkler Einschub', 'idt' ),
			'icon'     => 'align-full-width',
			'keywords' => array( 'einschub', 'band', 'dunkel', 'dt' ),
			'fields'   => array(
				array( 'key' => 'eyebrow', 'label' => __( 'Eyebrow (Label)', 'idt' ), 'type' => 'text', 'default' => 'Das Vorbild' ),
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Überschrift des Einschubs' ),
				array( 'key' => 'text', 'label' => __( 'Text', 'idt' ), 'type' => 'textarea', 'default' => 'Text des dunklen Einschubs.' ),
			),
			'render'   => function ( $a ) { return idt_sc_einschub( array( 'eyebrow' => $a['eyebrow'], 'title' => $a['title'] ), $a['text'] ); },
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
		'neuigkeiten' => array(
			'title'    => __( 'Beiträge-Übersicht (dynamisch)', 'idt' ),
			'icon'     => 'grid-view',
			'keywords' => array( 'neuigkeiten', 'beiträge', 'news', 'dt' ),
			'fields'   => array(
				array( 'key' => 'count', 'label' => __( 'Anzahl Beiträge', 'idt' ), 'type' => 'range', 'default' => 3, 'min' => 1, 'max' => 12 ),
				array( 'key' => 'eyebrow', 'label' => __( 'Eyebrow', 'idt' ), 'type' => 'text', 'default' => 'Aktuelles' ),
				array( 'key' => 'title', 'label' => __( 'Überschrift', 'idt' ), 'type' => 'text', 'default' => 'Aus der Initiative' ),
			),
			'render'   => function ( $a ) { return idt_sc_neuigkeiten( array( 'count' => $a['count'], 'eyebrow' => $a['eyebrow'], 'title' => $a['title'] ) ); },
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
}
add_action( 'init', 'idt_register_blocks' );

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
