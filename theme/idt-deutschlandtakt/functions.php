<?php
/**
 * IDT Deutschlandtakt — Theme-Setup
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'IDT_VERSION', '2.4.3' );

/* -------------------------------------------------------------------------
 * Theme-Supports & Menüs
 * ---------------------------------------------------------------------- */
/**
 * Die IDT-Markenfarben als Editor-Palette (Name / Slug / Hex). Genutzt für die
 * Farbvorschläge des Editors (add_theme_support) und für die Farbwähler der
 * eigenen Blöcke (inc/blocks.php) — eine Quelle, überall dieselben Farben.
 */
function idt_brand_palette() {
	return array(
		array( 'name' => __( 'Papier', 'idt' ),        'slug' => 'idt-paper',       'color' => '#FFF6F0' ),
		array( 'name' => __( 'Papier warm', 'idt' ),   'slug' => 'idt-paper-2',     'color' => '#FBEDE6' ),
		array( 'name' => __( 'Tinte (Teal)', 'idt' ),  'slug' => 'idt-ink',         'color' => '#00373C' ),
		array( 'name' => __( 'Grau', 'idt' ),          'slug' => 'idt-gray',        'color' => '#585857' ),
		array( 'name' => __( 'Violett', 'idt' ),       'slug' => 'idt-violet',      'color' => '#6E50FA' ),
		array( 'name' => __( 'Cyan', 'idt' ),          'slug' => 'idt-cyan',        'color' => '#00DCFA' ),
		array( 'name' => __( 'Gelb', 'idt' ),          'slug' => 'idt-yellow',      'color' => '#FFFF96' ),
		array( 'name' => __( 'Violett hell', 'idt' ),  'slug' => 'idt-violet-soft', 'color' => '#E8E3FF' ),
		array( 'name' => __( 'Cyan hell', 'idt' ),     'slug' => 'idt-cyan-soft',   'color' => '#D6F8FF' ),
		array( 'name' => __( 'Gelb hell', 'idt' ),     'slug' => 'idt-yellow-soft', 'color' => '#FFFDDB' ),
	);
}

function idt_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'editor-styles' );
	/* Das Frontend-Stylesheet auch im Editor (und in den Vorlagen-Vorschauen)
	   laden, danach die Editor-Korrekturen — s. assets/editor.css. */
	add_editor_style( array( 'style.css', 'assets/editor.css' ) );

	/* Website-Logo über „Design → Website-Identität" pflegbar (Fallback bleibt
	   das mitgelieferte Marken-PNG, s. header.php). Der dunkle Footer nutzt
	   weiterhin die Inverse-Variante als Theme-Asset. */
	add_theme_support( 'custom-logo', array(
		'height'      => 76,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	/* Vier Menü-Standorte: das Hauptmenü im Kopf, die beiden Linkspalten im
	   Footer und das Social-Media-Menü. Letzteres rendert der Footer nicht als
	   Liste, sondern als Reihe von Icon-Kacheln — welches Zeichen ein Punkt
	   bekommt, leitet sich aus seiner Adresse ab (s. idt_social_platform()). */
	register_nav_menus( array(
		'primary'          => __( 'Hauptmenü', 'idt' ),
		'footer'           => __( 'Footer-Menü', 'idt' ),
		'footer-mitmachen' => __( 'Footer-Menü „Mitmachen“', 'idt' ),
		'social'           => __( 'Social-Media-Menü (Footer)', 'idt' ),
	) );

	/* Farbvorschläge im Editor = IDT-Markenfarben (ersetzt die WP-Standard-
	 * palette). Die zugehörigen .has-…-color-Klassen stehen in style.css. */
	add_theme_support( 'editor-color-palette', idt_brand_palette() );

	/* Verlaufs-Vorschläge ebenso markenkonform (ersetzt die WP-Standards). */
	add_theme_support( 'editor-gradient-presets', array(
		array( 'name' => __( 'Violett → Cyan', 'idt' ), 'slug' => 'idt-verlauf', 'gradient' => 'linear-gradient(90deg, #6E50FA, #00DCFA)' ),
		array( 'name' => __( 'Tinte → Violett', 'idt' ), 'slug' => 'idt-verlauf-dunkel', 'gradient' => 'linear-gradient(131deg, #00373C, #6E50FA)' ),
	) );
}
add_action( 'after_setup_theme', 'idt_setup' );

/* -------------------------------------------------------------------------
 * Widget-Bereiche
 * ----------------------------------------------------------------------
 * Ein pflegbarer Footer-Bereich: Jedes zugewiesene Widget wird als weitere
 * Footer-Spalte (.fcol) neben „Themen" und „Mitmachen" gerendert, der
 * Widget-Titel als Spaltenüberschrift (<h4>). So lassen sich Footer-Inhalte
 * (z. B. eine Social-/Kontakt-Spalte) ohne Code-Änderung ergänzen. */
function idt_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer', 'idt' ),
		'id'            => 'footer',
		'description'   => __( 'Zusätzliche Spalte(n) im Footer. Jedes Widget wird eine eigene Spalte, der Widget-Titel ihre Überschrift.', 'idt' ),
		'before_widget' => '<div class="fcol %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'idt_widgets_init' );

/* -------------------------------------------------------------------------
 * Customizer
 * ---------------------------------------------------------------------- */

/** Vorgabetext für den Footer-Slogan (auch als Fallback in footer.php genutzt). */
function idt_footer_slogan_default() {
	return __( 'Mehr Verkehr auf die Schiene. Bürgerinitiative für einen integralen Taktfahrplan in Deutschland.', 'idt' );
}

/** Vorgabehöhe des Kopfmenü-Logos in px (Fallback = Token --header-logo-h). */
function idt_header_logo_height_default() {
	return 38;
}

/* -------------------------------------------------------------------------
 * Menüform: Menüband oder aufklappbares Menü
 * ----------------------------------------------------------------------
 * Zwei gleichwertige Oberflächen für dasselbe WP-Menü (Position „primary"):
 *
 *   'band'    — die bisherige Leiste: Punkte stehen auf dem Desktop offen
 *               nebeneinander, erst unter 900 px klappt ein Hamburger sie auf.
 *   'overlay' — das Menü liegt auf jeder Breite hinter drei Strichen; auf dem
 *               Desktop fächern die Punkte waagerecht daraus auf, auf dem
 *               Telefon nimmt eine Tafel den ganzen Bildschirm ein. Der Kopf
 *               trägt dabei keinen Balken, und die Suche steht als letzter
 *               Punkt im Menü statt als Lupe daneben.
 *
 * Gewählt wird unter „Design → Customizer → Website-Identität"; Vorgabe bleibt
 * das Menüband, damit bestehende Seiten sich ohne Zutun nicht verändern.
 * Gerendert wird bis auf zwei Stellen dasselbe Markup (header.php: die
 * Inverse-Fassung des Logos und der Platz der Suche); den Rest machen
 * Body-Klasse und Stylesheet-Abschnitt (style.css 5c).
 */

/** Vorgabe der Menüform. */
function idt_nav_style_default() {
	return 'band';
}

/** Auswahlliste der Menüformen (Schlüssel → Beschriftung im Customizer). */
function idt_nav_style_choices() {
	return array(
		'band'    => __( 'Menüband — Punkte stehen offen nebeneinander', 'idt' ),
		'overlay' => __( 'Aufklappbar — Punkte liegen hinter drei Strichen', 'idt' ),
	);
}

/** Nur bekannte Menüformen zulassen, sonst die Vorgabe. */
function idt_sanitize_nav_style( $value ) {
	return array_key_exists( $value, idt_nav_style_choices() ) ? $value : idt_nav_style_default();
}

/** Die aktuell eingestellte Menüform ('band' oder 'overlay'). */
function idt_nav_style() {
	return idt_sanitize_nav_style( get_theme_mod( 'idt_nav_style', idt_nav_style_default() ) );
}

/* -------------------------------------------------------------------------
 * Logo-Position im Kopf
 * ----------------------------------------------------------------------
 * Das Logo steht im Auslieferungszustand links oben — der Normalfall, und
 * die Stelle, an der man eine Marke zuerst sucht. Für Seiten, die wie ein
 * Plakat auftreten (Kampagne, Veranstaltung, Linkseite), ist das nicht
 * zwingend die richtige Stelle: Dort trägt die Mitte, und das Menü darf
 * darunter rücken. Drei Stellungen stehen zur Wahl:
 *
 *   'links'  — wie bisher: Marke am linken Rand, Menü und Aktionsleiste
 *              rechts daneben.
 *   'mitte'  — die Marke steht mittig im Kopf, das Menü zentriert in einer
 *              zweiten Zeile darunter. Lupe und Striche bleiben rechts.
 *   'rechts' — die gespiegelte Fassung von 'links': Marke am rechten Rand,
 *              Menü und Aktionsleiste links.
 *
 * Gewählt wird für die ganze Seite unter „Design → Customizer →
 * Website-Identität"; eine einzelne Seite kann über die Vorlage „Logo mittig"
 * (page-logo-mitte.php) davon abweichen — s. idt_logo_pos(). Vorgabe bleibt
 * links, damit bestehende Seiten sich ohne Zutun nicht verändern.
 *
 * Gerendert wird in allen drei Fällen dasselbe Markup (header.php bleibt
 * unberührt); den Unterschied machen Body-Klasse und Stylesheet-Abschnitt
 * (style.css 5d).
 */

/** Vorgabe der Logo-Position im Kopf. */
function idt_logo_pos_default() {
	return 'links';
}

/** Auswahlliste der Logo-Positionen (Schlüssel → Beschriftung im Customizer). */
function idt_logo_pos_choices() {
	return array(
		'links'  => __( 'Links — Marke am linken Rand, Menü rechts daneben', 'idt' ),
		'mitte'  => __( 'Mittig — Marke in der Mitte, Menü zentriert darunter', 'idt' ),
		'rechts' => __( 'Rechts — Marke am rechten Rand, Menü links daneben', 'idt' ),
	);
}

/** Nur bekannte Positionen zulassen, sonst die Vorgabe. */
function idt_sanitize_logo_pos( $value ) {
	return array_key_exists( $value, idt_logo_pos_choices() ) ? $value : idt_logo_pos_default();
}

/**
 * Die für die aktuelle Seite geltende Logo-Position.
 *
 * Die Seitenvorlage schlägt dabei die Customizer-Einstellung: Wer einer
 * einzelnen Seite „Logo mittig" gibt, meint genau diese Seite und nicht den
 * ganzen Auftritt.
 */
function idt_logo_pos() {
	/* Ohne Marke im Kopf ist die Frage gegenstandslos — die Vorlage „Menü ohne
	   Logo" blendet den Block ganz aus (header.php). Ohne diesen Zweig trüge
	   der Kopf bei 'mitte' eine leere erste Zeile über dem Menü. */
	if ( idt_page_template_is( 'page-no-logo.php' ) ) {
		return idt_logo_pos_default();
	}
	if ( idt_page_template_is( 'page-logo-mitte.php' ) ) {
		return 'mitte';
	}
	return idt_sanitize_logo_pos( get_theme_mod( 'idt_logo_pos', idt_logo_pos_default() ) );
}

/** Höhe auf einen sinnvollen Bereich begrenzen (Kopfmenü bleibt ein Menüband). */
function idt_sanitize_header_logo_height( $value ) {
	$value = absint( $value );
	if ( $value < 20 ) { $value = 20; }
	if ( $value > 120 ) { $value = 120; }
	return $value;
}

/* Slogan neben dem Footer-Logo über „Design → Customizer → Footer" pflegbar,
 * statt fest im Template zu stehen — analog zur Footer-Spalte „Mitmachen“,
 * die ebenfalls ohne Code-Änderung im Backend anpassbar ist. Dazu die
 * Darstellung des Kopfmenü-Logos: Das Bild selbst kommt aus dem WordPress-
 * Standard „Website-Identität → Logo"; hier kommt nur die Höhe dazu, damit
 * ein neu hochgeladenes Logo ohne CSS-Änderung passend sitzt. */
function idt_customize_register( $wp_customize ) {
	/* ---- Website-Identität: Höhe des Kopfmenü-Logos ---------------------- */
	$wp_customize->add_setting( 'idt_header_logo_height', array(
		'default'           => idt_header_logo_height_default(),
		'sanitize_callback' => 'idt_sanitize_header_logo_height',
		'transport'         => 'postMessage', /* Live-Vorschau, s. assets/customize-preview.js */
	) );

	$wp_customize->add_control( 'idt_header_logo_height', array(
		'type'        => 'number',
		'section'     => 'title_tagline', /* WP-Standardbereich „Website-Identität", direkt unter dem Logo. */
		'priority'    => 9,
		'label'       => __( 'Logo-Höhe im Kopfmenü (px)', 'idt' ),
		'description' => __( 'Wie hoch das Logo oben im Menüband dargestellt wird. Die Breite ergibt sich aus dem Seitenverhältnis. Vorgabe: 38.', 'idt' ),
		'input_attrs' => array( 'min' => 20, 'max' => 120, 'step' => 1 ),
	) );

	/* ---- Website-Identität: Stellung des Logos im Kopf ------------------- */
	$wp_customize->add_setting( 'idt_logo_pos', array(
		'default'           => idt_logo_pos_default(),
		'sanitize_callback' => 'idt_sanitize_logo_pos',
		'transport'         => 'refresh', /* Body-Klasse und damit das ganze Kopf-Layout hängen daran. */
	) );

	$wp_customize->add_control( 'idt_logo_pos', array(
		'type'        => 'radio',
		'section'     => 'title_tagline',
		'priority'    => 9, /* direkt hinter der Logo-Höhe, vor der Menüform */
		'label'       => __( 'Stellung des Logos im Kopf', 'idt' ),
		'description' => __( 'Wo die Marke im Kopfmenü steht. Mittig rückt das Menü in eine zweite Zeile darunter; Lupe und Striche bleiben in allen Fällen rechts. Einzelne Seiten können über die Seitenvorlage „Logo mittig" abweichen.', 'idt' ),
		'choices'     => idt_logo_pos_choices(),
	) );

	/* ---- Website-Identität: Form des Hauptmenüs -------------------------- */
	$wp_customize->add_setting( 'idt_nav_style', array(
		'default'           => idt_nav_style_default(),
		'sanitize_callback' => 'idt_sanitize_nav_style',
		'transport'         => 'refresh', /* Markup und Skript hängen daran — kein postMessage. */
	) );

	$wp_customize->add_control( 'idt_nav_style', array(
		'type'        => 'radio',
		'section'     => 'title_tagline',
		'priority'    => 10,
		'label'       => __( 'Form des Hauptmenüs', 'idt' ),
		'description' => __( 'Entweder stehen die Menüpunkte auf dem Desktop offen im Menüband, oder sie liegen auf jeder Breite hinter drei Strichen und fahren beim Klick auf (auf dem Telefon als Tafel über den ganzen Bildschirm). Bei der aufklappbaren Form trägt der Kopf keinen Balken, und die Suche steht als letzter Punkt im Menü statt als Lupe daneben.', 'idt' ),
		'choices'     => idt_nav_style_choices(),
	) );

	$wp_customize->add_section( 'idt_footer', array(
		'title'    => __( 'Footer', 'idt' ),
		'priority' => 160,
	) );

	$wp_customize->add_setting( 'idt_footer_slogan', array(
		'default'           => idt_footer_slogan_default(),
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	) );

	$wp_customize->add_control( 'idt_footer_slogan', array(
		'type'        => 'textarea',
		'section'     => 'idt_footer',
		'label'       => __( 'Slogan', 'idt' ),
		'description' => __( 'Text neben dem Logo in der Fußleiste.', 'idt' ),
	) );
}
add_action( 'customize_register', 'idt_customize_register' );

/* Die eingestellte Logo-Höhe als Inline-Override des Tokens --header-logo-h
 * (siehe style.css). Nur ausgeben, wenn sie von der Vorgabe abweicht — sonst
 * bleibt das Stylesheet allein zuständig. */
function idt_header_logo_css() {
	$height = idt_sanitize_header_logo_height( get_theme_mod( 'idt_header_logo_height', idt_header_logo_height_default() ) );
	if ( idt_header_logo_height_default() === $height ) {
		return;
	}
	wp_add_inline_style( 'idt-style', sprintf( ':root { --header-logo-h: %dpx; }', $height ) );
}
add_action( 'wp_enqueue_scripts', 'idt_header_logo_css', 20 );

/* Live-Vorschau im Customizer: Höhe ohne Neuladen anwenden. */
function idt_customize_preview_assets() {
	wp_enqueue_script(
		'idt-customize-preview',
		get_template_directory_uri() . '/assets/customize-preview.js',
		array( 'customize-preview' ),
		IDT_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'idt_customize_preview_assets' );

/* -------------------------------------------------------------------------
 * Seitenvorlagen
 * ----------------------------------------------------------------------
 * Die Sondervorlagen des Themes blenden Teile des Gerüsts aus (Logo, Menüband,
 * Footer). Geprüft wird dafür der Template-Slug der Seite und nicht das gerade
 * aktive Template-File: So greift die Wahl auch für die statische Startseite,
 * die WordPress über front-page.php rendert, während in der Seiten-Einstellung
 * weiterhin die gewählte Vorlage steht. */

/** Trägt die aktuelle Seite diese Vorlage? (z. B. 'page-verlauf.php') */
function idt_page_template_is( $slug ) {
	return is_singular( 'page' ) && $slug === get_page_template_slug();
}

/** Läuft die aktuelle Seite auf der Vorlage „Verlaufsseite"? */
function idt_is_verlauf_page() {
	return idt_page_template_is( 'page-verlauf.php' );
}

/* Die Verlaufsfläche hängt an einer Body-Klasse, damit sie die ganze Seite
 * einfärbt (style.css 6c) und nicht nur den Inhaltsbereich. */
function idt_body_class( $classes ) {
	if ( idt_is_verlauf_page() ) {
		$classes[] = 'idt-verlauf';
	}
	/* Logo-Position als Klasse: style.css 5d hängt an .idt-logo-mitte bzw.
	   .idt-logo-rechts; „links" ist der Normalfall und bleibt klassenlos. */
	$idt_logo_pos = idt_logo_pos();
	if ( idt_logo_pos_default() !== $idt_logo_pos ) {
		$classes[] = 'idt-logo-' . $idt_logo_pos;
	}
	/* Menüform als Klasse: style.css 5c hängt vollständig an .idt-nav-overlay,
	   das Menüband braucht keine eigene Klasse (es ist der Normalfall). */
	if ( 'overlay' === idt_nav_style() ) {
		$classes[] = 'idt-nav-overlay';
	}
	return $classes;
}
add_filter( 'body_class', 'idt_body_class' );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */
function idt_assets() {
	wp_enqueue_style( 'idt-style', get_stylesheet_uri(), array(), IDT_VERSION );
	wp_enqueue_script( 'idt-scale', get_template_directory_uri() . '/assets/scale.js', array(), IDT_VERSION, true );

	/* Hauptmenü (Hamburger, aufklappbare Tafel) — beide Menüformen. */
	wp_enqueue_script( 'idt-nav', get_template_directory_uri() . '/assets/nav.js', array(), IDT_VERSION, true );
	wp_localize_script( 'idt-nav', 'idtNav', array(
		'style' => idt_nav_style(),
		'i18n'  => array(
			'open'  => __( 'Menü öffnen', 'idt' ),
			'close' => __( 'Menü schließen', 'idt' ),
			/* Hinweis auf das Tastaturkürzel; assets/nav.js hängt ihn als
			   title an die drei Striche, sobald das Kürzel wirklich greift. */
			'key'   => __( 'Taste M', 'idt' ),
		),
	) );

	/* Such-Overlay (Lupe im Menüband) inkl. Live-Vorschau der Treffer. */
	wp_enqueue_script( 'idt-search', get_template_directory_uri() . '/assets/search.js', array(), IDT_VERSION, true );
	wp_localize_script( 'idt-search', 'idtSearch', array(
		'endpoint' => esc_url_raw( rest_url( 'idt/v1/suche' ) ),
		'minChars' => 2,
		'i18n'     => array(
			'open'    => __( 'Suche öffnen', 'idt' ),
			'close'   => __( 'Suche schließen', 'idt' ),
			'loading' => __( 'Suche läuft …', 'idt' ),
			'error'   => __( 'Die Vorschau ist gerade nicht erreichbar — mit Eingabe geht es zur vollständigen Ergebnisseite.', 'idt' ),
			/* translators: %s = Anzahl der Treffer */
			'more'    => __( 'Alle %s Treffer anzeigen', 'idt' ),
			'hits'    => __( 'Treffer', 'idt' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'idt_assets' );

/* Kennzeichen „JavaScript läuft" früh im <head>.
 *
 * Das aufklappbare Menü (style.css 5c) versteckt die Menüpunkte — aufklappen
 * kann sie nur assets/nav.js. Ohne diese Kennung bliebe das Menü bei
 * abgeschaltetem JavaScript unerreichbar, deshalb hängt das Verstecken an
 * .idt-js: Fehlt die Klasse, stehen die Punkte wie im Menüband offen da.
 * Bewusst inline und nicht als Datei — die Klasse muss vor dem ersten Aufbau
 * der Seite stehen, sonst blitzt das offene Menü kurz auf. */
function idt_nav_js_flag() {
	if ( 'overlay' !== idt_nav_style() ) {
		return;
	}
	echo '<script>document.documentElement.classList.add("idt-js");</script>' . "\n";
}
add_action( 'wp_head', 'idt_nav_js_flag', 0 );

/* Inline-Stilelemente (Marker, Eyebrow, Tag) als RichText-Formate im Editor. */
function idt_editor_assets() {
	wp_enqueue_script(
		'idt-editor-formats',
		get_template_directory_uri() . '/assets/editor-formats.js',
		array( 'wp-rich-text', 'wp-block-editor', 'wp-element', 'wp-i18n', 'wp-blocks', 'wp-dom-ready' ),
		IDT_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'idt_editor_assets' );

/* -------------------------------------------------------------------------
 * Bausteine (Shortcodes, Block-Patterns, Demo-Inhalte)
 * ---------------------------------------------------------------------- */
require get_template_directory() . '/inc/shortcodes.php';
require get_template_directory() . '/inc/blocks.php';
require get_template_directory() . '/inc/patterns.php';
require get_template_directory() . '/inc/search.php';

/* Demo-/Erstinhalte: nur im Dev-Stack vorhanden — das Production-Zip enthält
 * diese Datei bewusst nicht, damit eine Aktivierung auf einer bestehenden
 * Seite keinerlei Inhalte anlegt oder Optionen ändert. Das Seeding läuft
 * ausschließlich explizit über wp-cli (siehe wp-cli/init.sh), nicht mehr
 * automatisch bei Theme-Aktivierung. */
$idt_demo_content = get_template_directory() . '/inc/demo-content.php';
if ( file_exists( $idt_demo_content ) ) {
	require $idt_demo_content;
}

/* -------------------------------------------------------------------------
 * Kommentare & Pingbacks sitewide deaktivieren
 * ----------------------------------------------------------------------
 * Vereinsseite ohne Kommentarfunktion: Diskussion findet nicht auf der
 * Website statt. single.php rendert bewusst kein comments_template();
 * diese Filter stellen sicher, dass auch sonst nirgends Kommentare oder
 * Pingbacks möglich sind bzw. angezeigt werden. */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array' );

/* -------------------------------------------------------------------------
 * Hilfsfunktion: Splash-Hero rendern (Front-Page)
 * ---------------------------------------------------------------------- */
/**
 * @param array|null  $pills   Liste von [Beschriftung, URL]-Paaren; null = Standard-Links.
 * @param string|null $caption Optionale Schlagzeile unter den Buttons; leer/null = keine.
 */
function idt_render_splash( $pills = null, $caption = null ) {
	$logo = get_template_directory_uri() . '/assets/logo-idt-transparent.png';

	if ( null === $pills ) {
		$pills = array(
			array( 'Über die Initiative', idt_page_url( 'ueber-die-initiative', 'https://initiative-deutschlandtakt.de/ueber-uns/' ) ),
			array( 'Mitglied werden', 'mailto:mail@initiative-deutschlandtakt.de?subject=Mitglied%20werden' ),
			array( 'Mehr Inhalte', idt_page_url( 'aktuelles', '#aktuelles' ) ),
		);
	}
	$caption = (string) $caption;
	?>
	<div class="stage">
		<div class="canvas" id="idt-canvas">
			<div class="band"></div>
			<div class="layer top"><span class="bar b1a"></span><span class="bar b1b"></span><span class="bar b2"></span></div>
			<div class="layer bottom"><span class="bar b1a"></span><span class="bar b1b"></span><span class="bar b2"></span></div>

			<img class="splash-logo" src="<?php echo esc_url( $logo ); ?>" alt="Initiative Deutschlandtakt">

			<?php /* Wrapper ist auf Desktop unsichtbar (display:contents) und wird
			         auf Mobil zum dunklen Diagonal-Band mit Pills in voller Größe. */ ?>
			<div class="splash-bottom">
				<nav class="splash-links" aria-label="<?php esc_attr_e( 'Hauptlinks', 'idt' ); ?>">
					<?php foreach ( $pills as $pill ) : ?>
						<a class="pill pill--on-ink" href="<?php echo esc_url( $pill[1] ); ?>"><?php echo esc_html( $pill[0] ); ?></a>
					<?php endforeach; ?>
				</nav>

				<?php if ( '' !== $caption ) : ?>
					<p class="splash-caption"><?php echo esc_html( $caption ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Horizont-Splash v2 ("Zentriert") — fluide Alternative zum starren
 * 1280×800-Horizont-Splash: Logo + Link-Stack mittig per Flexbox in einer
 * 100svh-Bühne, ohne Fixmaße und ohne Scaling-Script (passt sich jedem
 * Seitenverhältnis an, kein Abschneiden/Letterboxing).
 *
 * @param array|null  $pills   Liste von [Beschriftung, URL]-Paaren; null = Standard-Links.
 * @param string|null $caption Optionale Schlagzeile unter den Links; leer/null = keine.
 */
function idt_render_splash2( $pills = null, $caption = null ) {
	$logo = get_template_directory_uri() . '/assets/logo-idt-transparent.png';

	if ( null === $pills ) {
		$pills = array(
			array( 'Über die Initiative', idt_page_url( 'ueber-die-initiative', 'https://initiative-deutschlandtakt.de/ueber-uns/' ) ),
			array( 'Mitglied werden', 'mailto:mail@initiative-deutschlandtakt.de?subject=Mitglied%20werden' ),
			array( 'Mehr Inhalte', idt_page_url( 'aktuelles', '#aktuelles' ) ),
		);
	}
	$caption = (string) $caption;
	?>
	<div class="stage2">
		<div class="stage2__center">
			<img class="stage2__logo" src="<?php echo esc_url( $logo ); ?>" alt="Initiative Deutschlandtakt">

			<nav class="stage2__menu" aria-label="<?php esc_attr_e( 'Hauptlinks', 'idt' ); ?>">
				<?php foreach ( $pills as $pill ) : ?>
					<a class="stage2__row" href="<?php echo esc_url( $pill[1] ); ?>"><?php echo esc_html( $pill[0] ); ?></a>
				<?php endforeach; ?>
			</nav>

			<?php if ( '' !== $caption ) : ?>
				<p class="stage2__caption"><?php echo esc_html( $caption ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/* Liefert die Permalink-URL einer Seite nach Slug, mit Fallback. */
function idt_page_url( $slug, $fallback = '#' ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : $fallback;
}

/* Standard-Links für die Footer-Spalte „Mitmachen“, solange dem Standort
 * „Footer-Menü ‚Mitmachen‘“ kein Menü zugewiesen ist. Sobald im Backend
 * (Design → Menüs) ein Menü an diesen Standort gehängt wird, ersetzt es
 * diese Ausgabe — die Spalte ist damit ebenso pflegbar wie „Themen“. */
function idt_footer_mitmachen_fallback() {
	$items = array(
		array( 'mailto:mail@initiative-deutschlandtakt.de?subject=Mitglied%20werden', 'Mitglied werden' ),
		array( 'https://initiative-deutschlandtakt.de/pressekontakt/', 'Pressekontakt' ),
		array( 'https://initiative-deutschlandtakt.de/downloads/', 'Downloads' ),
	);
	echo '<ul>';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $item[0], array( 'http', 'https', 'mailto' ) ),
			esc_html( $item[1] )
		);
	}
	echo '</ul>';
}

/*
 * Liefert die URL der Beitrags-Übersicht („Aktuelles"). Bevorzugt die in
 * WordPress konfigurierte Beitragsseite (Einstellungen → Lesen → Beitragsseite),
 * damit der Link auch dann korrekt ist, wenn die Übersichtsseite einen anderen
 * Slug als „aktuelles" hat. Fällt sonst auf die Slug-Suche und die Startseite
 * zurück.
 */
function idt_blog_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page && 'publish' === get_post_status( $posts_page ) ) {
		return get_permalink( $posts_page );
	}
	return idt_page_url( 'aktuelles', home_url( '/' ) );
}

/*
 * Übernimmt einmalig die Beitragsseite („Aktuelles") in die WordPress-
 * Einstellungen (Einstellungen → Lesen → Beitragsseite), falls dort keine
 * gesetzt ist. Damit funktioniert der „← Alle Beiträge"-Link auch auf
 * Bestandsinstallationen ohne manuelles Nachpflegen. Läuft genau einmal
 * (Option-Guard idt_posts_page_adopted); eine bewusste spätere Änderung
 * durch die Redaktion (z. B. Beitragsseite absichtlich leeren) wird dadurch
 * nicht wieder überschrieben. Ergänzt das einmalige Seeding (idt_seeded),
 * das bei Bestandsinstallationen bereits gelaufen sein kann.
 */
function idt_adopt_posts_page() {
	if ( get_option( 'idt_posts_page_adopted' ) ) {
		return;
	}
	if ( ! (int) get_option( 'page_for_posts' ) ) {
		$page = get_page_by_path( 'aktuelles' );
		if ( $page && 'publish' === $page->post_status ) {
			update_option( 'page_for_posts', $page->ID );
		}
	}
	update_option( 'idt_posts_page_adopted', 1 );
}
add_action( 'after_setup_theme', 'idt_adopt_posts_page' );

/* Lesezeit-Helfer für Beiträge. */
function idt_reading_time( $content ) {
	$words = str_word_count( wp_strip_all_tags( $content ) );
	$mins  = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d = Minuten */
	return sprintf( _n( '%d Min. Lesezeit', '%d Min. Lesezeit', $mins, 'idt' ), $mins );
}
