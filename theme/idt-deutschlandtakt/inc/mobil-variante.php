<?php
/**
 * Mobil-Variante für ausgewählte Bildblöcke (core/image).
 *
 * Manche Grafiken taugen nur in einem Format: Eine 16:9-Grafik mit Beschriftung
 * wird auf dem Telefon so klein, dass man nichts mehr lesen kann. Für solche
 * Bilder kann die Redaktion eine zweite Datei im Hochformat hochladen; auf
 * schmalen Bildschirmen zeigt der Browser dann diese statt der breiten.
 *
 * - Opt-in pro Bildblock über die Klasse `mobil-variante` (Seitenleiste:
 *   Schalter „Mobil-Variante verwenden", oder von Hand unter „Zusätzliche
 *   CSS-Klasse(n)"). Alle anderen Bilder bleiben unberührt. Die Klasse trägt
 *   bewusst keinen idt-Präfix — sie ist ein Redaktionsbegriff, den man auch
 *   eintippen können soll.
 * - Die Mobil-Datei wird über den Namen gefunden: zu `grafik.png` gehört
 *   `grafik-mobil.png` im selben Upload-Ordner (für jedes Format: png, jpg,
 *   webp, svg …). Sie muss als Anhang in der Mediathek liegen.
 * - Nur dann wird das <img> serverseitig in ein <picture> mit einer <source>
 *   für schmale Bildschirme gepackt. Der Browser lädt genau eine der beiden
 *   Dateien — kein Doppel-Download, kein Ausblenden per CSS. Das <img> selbst
 *   bleibt mit allen Attributen stehen (alt, srcset, sizes, width/height,
 *   loading, Klassen) und ist zugleich die Rückfallebene; der Alternativtext
 *   gilt für beide Fassungen.
 *
 * Breakpoint: Konstante IDT_MOBIL_BREAKPOINT (Vorgabe 700 px, in der
 * wp-config.php vorab definierbar) und Filter `idt_mobil_variante_breakpoint`.
 *
 * Die Suche nach dem Anhang (attachment_url_to_postid() — eine Abfrage über
 * ungeindexte Meta-Werte) wird pro Datei als Transient zwischengespeichert;
 * mit Objekt-Cache landet sie dort. Hochladen oder Löschen einer passenden
 * Datei räumt den Eintrag sofort ab, damit eine neue Mobil-Datei nicht erst
 * nach Ablauf des Caches erscheint.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'IDT_MOBIL_BREAKPOINT' ) ) {
	/** Bis zu dieser Viewport-Breite (px) wird die Mobil-Datei gezeigt. */
	define( 'IDT_MOBIL_BREAKPOINT', 700 );
}

/** Klasse am Bildblock, die die Mobil-Variante einschaltet. */
const IDT_MOBIL_CLASS = 'mobil-variante';

/** Namenszusatz der Mobil-Datei vor der Dateiendung. */
const IDT_MOBIL_SUFFIX = '-mobil';

/** Breakpoint in px — Konstante, über `idt_mobil_variante_breakpoint` anpassbar. */
function idt_mobil_breakpoint() {
	return max( 1, (int) apply_filters( 'idt_mobil_variante_breakpoint', IDT_MOBIL_BREAKPOINT ) );
}

/** Trägt der Block die Klasse `mobil-variante`? */
function idt_mobil_is_enabled( $block ) {
	$classes = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	return in_array( IDT_MOBIL_CLASS, preg_split( '/\s+/', $classes, -1, PREG_SPLIT_NO_EMPTY ), true );
}

/**
 * Leitet aus einer Bild-URL die erwartete URL der Mobil-Datei ab.
 *
 * Größenzusätze von WordPress werden vorher entfernt — `grafik-1024x576.png`
 * und `grafik-scaled.jpg` sind beide `grafik`, die Mobil-Datei also
 * `grafik-mobil.png` bzw. `grafik-mobil.jpg`. Query-Strings fallen weg.
 *
 * @return string Leer, wenn die URL keinen Dateinamen mit Endung hat.
 */
function idt_mobil_candidate_url( $url ) {
	$url = strtok( (string) $url, '?#' );
	if ( ! $url || ! preg_match( '~^(.*/)([^/]+?)\.([a-z0-9]+)$~i', $url, $m ) ) {
		return '';
	}
	$name = preg_replace( '/(-\d+x\d+|-scaled)$/', '', $m[2] );
	if ( '' === $name || str_ends_with( $name, IDT_MOBIL_SUFFIX ) ) {
		return ''; // Ein Bild, das selbst schon die Mobil-Datei ist.
	}
	return $m[1] . $name . IDT_MOBIL_SUFFIX . '.' . $m[3];
}

/** Cache-Schlüssel für eine Kandidaten-URL (Schema und Host spielen keine Rolle). */
function idt_mobil_cache_key( $candidate ) {
	return 'idt_mobil_' . md5( (string) wp_parse_url( $candidate, PHP_URL_PATH ) );
}

/**
 * Anhang-ID der Mobil-Datei zu einer Kandidaten-URL, 0 wenn es keine gibt.
 *
 * Für große JPEGs legt WordPress beim Hochladen `…-mobil-scaled.jpg` als
 * Hauptdatei an; diese Schreibweise wird nur bei einem Fehltreffer zusätzlich
 * gefragt. Treffer wie Fehltreffer werden gecacht.
 */
function idt_mobil_attachment_id( $candidate ) {
	static $request = array();
	if ( '' === $candidate ) {
		return 0;
	}
	$key = idt_mobil_cache_key( $candidate );
	if ( isset( $request[ $key ] ) ) {
		return $request[ $key ];
	}

	$cached = get_transient( $key );
	if ( is_array( $cached ) && isset( $cached['id'] ) ) {
		return $request[ $key ] = (int) $cached['id'];
	}

	$id = attachment_url_to_postid( $candidate );
	if ( ! $id ) {
		$id = attachment_url_to_postid( preg_replace( '/(\.[a-z0-9]+)$/i', '-scaled$1', $candidate ) );
	}
	if ( $id && ! wp_attachment_is_image( $id ) && 'image/svg+xml' !== get_post_mime_type( $id ) ) {
		$id = 0;
	}

	set_transient( $key, array( 'id' => (int) $id ), DAY_IN_SECONDS );
	return $request[ $key ] = (int) $id;
}

/**
 * Sucht zum Hauptbild eines Bildblocks den Anhang der Mobil-Datei.
 *
 * Maßgeblich ist die Originaldatei des Anhangs (Block-Attribut `id`), sonst
 * die `src` des Bildes — so findet sich die Mobil-Datei auch dann, wenn der
 * Block eine verkleinerte Fassung einbindet.
 *
 * @param int    $id  Anhang-ID des Hauptbildes (0, wenn unbekannt).
 * @param string $url Bild-URL als Rückfall.
 * @return int Anhang-ID der Mobil-Datei oder 0.
 */
function idt_mobil_find( $id, $url ) {
	$source = $id ? wp_get_attachment_url( $id ) : '';
	return idt_mobil_attachment_id( idt_mobil_candidate_url( $source ? $source : $url ) );
}

/**
 * Packt das <img> eines markierten Bildblocks in ein <picture>.
 *
 * Unverändert bleibt alles um das Bild herum — <figure> mit Ausrichtungs- und
 * Stilklassen, ein umschließender Link, die <figcaption>. Ersetzt wird nur das
 * <img>-Tag selbst durch <picture><source …><img …></picture>.
 */
function idt_mobil_render_image( $block_content, $block ) {
	if ( ! idt_mobil_is_enabled( $block ) || false !== stripos( $block_content, '<picture' ) ) {
		return $block_content;
	}
	if ( ! preg_match( '/<img\b[^>]*>/i', $block_content, $img, PREG_OFFSET_CAPTURE ) ) {
		return $block_content;
	}
	$src = preg_match( '/\ssrc=(["\'])(.*?)\1/i', $img[0][0], $s ) ? html_entity_decode( $s[2] ) : '';

	$mobil_id = idt_mobil_find( isset( $block['attrs']['id'] ) ? (int) $block['attrs']['id'] : 0, $src );
	if ( ! $mobil_id ) {
		return $block_content;
	}
	$mobil_url = wp_get_attachment_url( $mobil_id );
	if ( ! $mobil_url ) {
		return $block_content;
	}

	// Rasterbilder bringen ihre Zwischengrößen mit, damit das Telefon nicht das
	// Original lädt; ohne `sizes` nimmt der Browser 100vw an — auf dem Telefon
	// genau richtig. SVG hat keine Größen und bekommt die eine URL.
	$srcset = wp_get_attachment_image_srcset( $mobil_id, 'full' );
	$attrs  = sprintf(
		' media="%s" srcset="%s"',
		esc_attr( sprintf( '(max-width: %dpx)', idt_mobil_breakpoint() ) ),
		$srcset ? esc_attr( $srcset ) : esc_url( $mobil_url )
	);
	$type = get_post_mime_type( $mobil_id );
	if ( $type ) {
		$attrs .= ' type="' . esc_attr( $type ) . '"';
	}
	// Eigene Maße an <source>: Das Hochformat bekommt sein Seitenverhältnis
	// reserviert, statt in die Höhe des 16:9-Bildes gepresst zu werden.
	$meta = wp_get_attachment_metadata( $mobil_id );
	if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
		$attrs .= sprintf( ' width="%d" height="%d"', (int) $meta['width'], (int) $meta['height'] );
	}

	$picture = '<picture class="idt-mobil-variante"><source' . $attrs . '>' . $img[0][0] . '</picture>';
	return substr_replace( $block_content, $picture, $img[0][1], strlen( $img[0][0] ) );
}
add_filter( 'render_block_core/image', 'idt_mobil_render_image', 10, 2 );

/**
 * Cache leeren, sobald eine Datei hochgeladen oder gelöscht wird, deren Name
 * auf `-mobil` endet — ihr Kandidatenschlüssel ist dann ihre eigene URL.
 */
function idt_mobil_flush_attachment( $post_id ) {
	$url = wp_get_attachment_url( $post_id );
	if ( ! $url ) {
		return;
	}
	$path = preg_replace( '/-scaled(\.[a-z0-9]+)$/i', '$1', strtok( $url, '?#' ) );
	if ( preg_match( '/' . preg_quote( IDT_MOBIL_SUFFIX, '/' ) . '\.[a-z0-9]+$/i', $path ) ) {
		delete_transient( idt_mobil_cache_key( $path ) );
	}
}
add_action( 'add_attachment', 'idt_mobil_flush_attachment' );
add_action( 'delete_attachment', 'idt_mobil_flush_attachment' );

/* -------------------------------------------------------------------------
 * Editor: Schalter in der Seitenleiste des Bildblocks
 * ----------------------------------------------------------------------
 * assets/mobil-variante.js setzt bzw. entfernt die Klasse und fragt über den
 * REST-Endpunkt unten, ob die Mobil-Datei existiert — mit derselben Suche wie
 * das Frontend, damit Hinweis und Ausgabe nie auseinanderlaufen.
 */
function idt_mobil_editor_assets() {
	wp_enqueue_script(
		'idt-mobil-variante',
		get_template_directory_uri() . '/assets/mobil-variante.js',
		array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-api-fetch', 'wp-url', 'wp-i18n' ),
		IDT_VERSION,
		true
	);
	wp_localize_script( 'idt-mobil-variante', 'IDT_MOBIL', array(
		'className'  => IDT_MOBIL_CLASS,
		'suffix'     => IDT_MOBIL_SUFFIX,
		'breakpoint' => idt_mobil_breakpoint(),
	) );
}
add_action( 'enqueue_block_editor_assets', 'idt_mobil_editor_assets' );

function idt_mobil_register_route() {
	register_rest_route( 'idt/v1', '/mobil-variante', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => 'idt_mobil_rest_permission',
		'callback'            => 'idt_mobil_rest_lookup',
		'args'                => array(
			'id'  => array( 'type' => 'integer', 'default' => 0 ),
			'url' => array( 'type' => 'string', 'default' => '', 'sanitize_callback' => 'esc_url_raw' ),
		),
	) );
}
add_action( 'rest_api_init', 'idt_mobil_register_route' );

/** Nur für Menschen, die Beiträge bearbeiten dürfen — es ist ein Editor-Hinweis. */
function idt_mobil_rest_permission() {
	return current_user_can( 'edit_posts' );
}

function idt_mobil_rest_lookup( $request ) {
	$id        = (int) $request['id'];
	$source    = $id ? wp_get_attachment_url( $id ) : '';
	$candidate = idt_mobil_candidate_url( $source ? $source : $request['url'] );
	$mobil_id  = idt_mobil_attachment_id( $candidate );

	return rest_ensure_response( array(
		'expected' => $candidate ? wp_basename( $candidate ) : '',
		'found'    => (bool) $mobil_id,
		'url'      => $mobil_id ? wp_get_attachment_url( $mobil_id ) : '',
	) );
}
