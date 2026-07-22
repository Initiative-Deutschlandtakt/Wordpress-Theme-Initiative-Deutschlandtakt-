<?php
/**
 * IDT Deutschlandtakt — Theme-Setup
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'IDT_VERSION', '2.0.8' );

/* -------------------------------------------------------------------------
 * Theme-Supports & Menüs
 * ---------------------------------------------------------------------- */
function idt_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );

	register_nav_menus( array(
		'primary' => __( 'Hauptmenü', 'idt' ),
		'footer'  => __( 'Footer-Menü', 'idt' ),
	) );

	/* Farbvorschläge im Editor = IDT-Markenfarben (ersetzt die WP-Standard-
	 * palette). Die zugehörigen .has-…-color-Klassen stehen in style.css. */
	add_theme_support( 'editor-color-palette', array(
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
	) );

	/* Verlaufs-Vorschläge ebenso markenkonform (ersetzt die WP-Standards). */
	add_theme_support( 'editor-gradient-presets', array(
		array( 'name' => __( 'Violett → Cyan', 'idt' ), 'slug' => 'idt-verlauf', 'gradient' => 'linear-gradient(90deg, #6E50FA, #00DCFA)' ),
		array( 'name' => __( 'Tinte → Violett', 'idt' ), 'slug' => 'idt-verlauf-dunkel', 'gradient' => 'linear-gradient(131deg, #00373C, #6E50FA)' ),
	) );
}
add_action( 'after_setup_theme', 'idt_setup' );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */
function idt_assets() {
	wp_enqueue_style( 'idt-style', get_stylesheet_uri(), array(), IDT_VERSION );
	wp_enqueue_script( 'idt-scale', get_template_directory_uri() . '/assets/scale.js', array(), IDT_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'idt_assets' );

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

/* Liefert die Permalink-URL einer Seite nach Slug, mit Fallback. */
function idt_page_url( $slug, $fallback = '#' ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : $fallback;
}

/* Lesezeit-Helfer für Beiträge. */
function idt_reading_time( $content ) {
	$words = str_word_count( wp_strip_all_tags( $content ) );
	$mins  = max( 1, (int) ceil( $words / 200 ) );
	/* translators: %d = Minuten */
	return sprintf( _n( '%d Min. Lesezeit', '%d Min. Lesezeit', $mins, 'idt' ), $mins );
}
