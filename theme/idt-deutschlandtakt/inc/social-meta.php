<?php
/**
 * Linkvorschau — Open-Graph- und Twitter-Card-Tags im <head>.
 *
 * Wird ein Link geteilt (LinkedIn, Mastodon, Bluesky, Facebook, Messenger wie
 * Signal, WhatsApp, Telegram), holt der Dienst die Seite ab und baut aus
 * diesen Tags die Vorschaukarte: Titel, Kurztext, Bild. Fehlen sie, rät jeder
 * Dienst selbst — mal das erste Bild der Seite, mal ein Menütext, mal gar
 * nichts. Das Theme gibt die Tags deshalb selbst aus:
 *
 * - Titel:  Beitrags-/Seitentitel, auf der Startseite der Website-Titel.
 * - Text:   der Textauszug (Seitenleiste „Textauszug", für Seiten hier
 *           eingeschaltet), sonst ein Anfang des Inhalts, sonst die
 *           Standardbeschreibung aus dem Customizer, sonst der Untertitel.
 * - Bild:   das Beitragsbild, sonst das Standardbild aus dem Customizer,
 *           sonst das mitgelieferte assets/social-preview.png (1200×630).
 *
 * Ist ein SEO-Plugin aktiv, das dieselben Tags schreibt (Yoast, Rank Math,
 * SEOPress, All in One SEO, The SEO Framework, Jetpack), hält sich das Theme
 * zurück — doppelte og:-Tags führen dazu, dass Dienste willkürlich einen davon
 * nehmen. Per Filter `idt_social_meta_enabled` lässt sich die Ausgabe auch
 * von Hand abschalten.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Länge des automatisch gebildeten Kurztexts (Zeichen). */
const IDT_SOCIAL_DESC_LENGTH = 200;

/* Textauszug auch für Seiten: Die meisten Inhalte der Initiative sind Seiten,
 * nicht Beiträge — ohne das Feld ließe sich der Vorschautext dort nicht
 * gezielt setzen. Es erscheint in der Seitenleiste des Editors. */
function idt_social_page_excerpt() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'idt_social_page_excerpt' );

/**
 * Schreibt schon ein Plugin Open-Graph-Tags? Dann bleibt das Theme still.
 */
function idt_social_meta_enabled() {
	$plugin = defined( 'WPSEO_VERSION' )                /* Yoast SEO */
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'AIOSEO_VERSION' )                  /* All in One SEO */
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' )
		|| ( class_exists( 'Jetpack' ) && apply_filters( 'jetpack_enable_open_graph', false ) );

	/**
	 * Filtert, ob das Theme die Linkvorschau-Tags ausgibt.
	 *
	 * @param bool $enabled true, solange kein bekanntes SEO-Plugin aktiv ist.
	 */
	return (bool) apply_filters( 'idt_social_meta_enabled', ! $plugin );
}

/** Text säubern: Blöcke, Shortcodes, Tags, Mehrfach-Leerraum raus, kürzen. */
function idt_social_clean_text( $text, $length = IDT_SOCIAL_DESC_LENGTH ) {
	$text = excerpt_remove_blocks( (string) $text );
	$text = strip_shortcodes( $text );
	$text = wp_strip_all_tags( $text, true );
	$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
	if ( $length && mb_strlen( $text ) > $length ) {
		/* Am letzten Wortende vor der Grenze schneiden, nicht mitten im Wort. */
		$cut  = mb_substr( $text, 0, $length );
		$cut  = preg_replace( '/\s+\S*$/u', '', $cut );
		$text = preg_replace( '/[\s,;:–-]+$/u', '', $cut ) . ' …';
	}
	return $text;
}

/** Standardbeschreibung: Customizer-Feld, sonst der Untertitel der Website. */
function idt_social_default_description() {
	$desc = trim( (string) get_theme_mod( 'idt_social_description', '' ) );
	return '' !== $desc ? $desc : get_bloginfo( 'description' );
}

/**
 * Das Vorschaubild als array( url, breite, höhe, alt ).
 *
 * @param int $post_id Beitrag/Seite, deren Beitragsbild Vorrang hat (0 = keins).
 */
function idt_social_image( $post_id = 0 ) {
	$attachment = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;
	if ( ! $attachment ) {
		$attachment = absint( get_theme_mod( 'idt_social_image', 0 ) );
	}
	if ( $attachment ) {
		/* „large" reicht allen Diensten (≥ 1200 px sind ideal, 600 px genügen)
		   und hält die Datei klein genug für Messenger-Vorschauen. */
		$src = wp_get_attachment_image_src( $attachment, 'large' );
		if ( $src ) {
			$alt = trim( (string) get_post_meta( $attachment, '_wp_attachment_image_alt', true ) );
			return array( $src[0], (int) $src[1], (int) $src[2], $alt );
		}
	}
	return array(
		get_template_directory_uri() . '/assets/social-preview.png',
		1200,
		630,
		get_bloginfo( 'name' ),
	);
}

/**
 * Sammelt die Angaben für die aktuelle Anfrage.
 *
 * @return array Schlüssel: type, title, description, url, image, published, modified.
 */
function idt_social_data() {
	$site = get_bloginfo( 'name' );
	$data = array(
		'type'        => 'website',
		'title'       => $site,
		'description' => idt_social_default_description(),
		'url'         => '',
		'image'       => idt_social_image(),
		'published'   => '',
		'modified'    => '',
	);

	if ( is_front_page() ) {
		$data['url'] = home_url( '/' );
		/* Eine statische Startseite darf mit Textauszug und Beitragsbild
		   überschreiben, der Titel bleibt aber der Name der Initiative —
		   „Startseite" wäre als Karten-Überschrift nichtssagend. */
		$front = is_page() ? get_queried_object_id() : 0;
		if ( $front ) {
			if ( has_excerpt( $front ) ) {
				$data['description'] = idt_social_clean_text( get_the_excerpt( $front ) );
			}
			$data['image'] = idt_social_image( $front );
		}
	} elseif ( is_singular() ) {
		$post          = get_queried_object();
		$data['title'] = wp_strip_all_tags( get_the_title( $post ), true );
		$data['url']   = wp_get_canonical_url( $post );
		$data['image'] = idt_social_image( $post->ID );

		if ( has_excerpt( $post ) ) {
			$desc = $post->post_excerpt;
		} elseif ( ! post_password_required( $post ) ) {
			$desc = $post->post_content;
		} else {
			$desc = '';
		}
		$desc = idt_social_clean_text( $desc );
		if ( '' !== $desc ) {
			$data['description'] = $desc;
		}

		if ( 'post' === $post->post_type ) {
			$data['type']      = 'article';
			$data['published'] = get_post_time( 'c', true, $post );
			$data['modified']  = get_post_modified_time( 'c', true, $post );
		}
	} elseif ( is_home() ) {
		$page          = (int) get_option( 'page_for_posts' );
		$data['title'] = $page ? wp_strip_all_tags( get_the_title( $page ), true ) : $site;
		$data['url']   = $page ? get_permalink( $page ) : home_url( '/' );
		if ( $page ) {
			$data['image'] = idt_social_image( $page );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term          = get_queried_object();
		$data['title'] = single_term_title( '', false );
		$data['url']   = get_term_link( $term );
		$desc          = idt_social_clean_text( term_description( $term ) );
		if ( '' !== $desc ) {
			$data['description'] = $desc;
		}
	} elseif ( is_archive() ) {
		$data['title'] = wp_strip_all_tags( get_the_archive_title(), true );
	}

	/**
	 * Filtert die Angaben der Linkvorschau, bevor sie ausgegeben werden.
	 *
	 * @param array $data Siehe idt_social_data().
	 */
	return apply_filters( 'idt_social_data', $data );
}

/** Die Tags selbst — früh im <head>, damit Crawler sie sicher finden. */
function idt_social_meta() {
	if ( ! idt_social_meta_enabled() || is_search() || is_404() ) {
		return;
	}
	$d = idt_social_data();
	list( $img, $img_w, $img_h, $img_alt ) = $d['image'];

	$tags = array(
		array( 'name', 'description', $d['description'] ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:locale', str_replace( '-', '_', get_bloginfo( 'language' ) ) ),
		array( 'property', 'og:type', $d['type'] ),
		array( 'property', 'og:title', $d['title'] ),
		array( 'property', 'og:description', $d['description'] ),
		array( 'property', 'og:url', is_wp_error( $d['url'] ) ? '' : $d['url'] ),
		array( 'property', 'og:image', $img ),
		array( 'property', 'og:image:width', $img_w ? (string) $img_w : '' ),
		array( 'property', 'og:image:height', $img_h ? (string) $img_h : '' ),
		array( 'property', 'og:image:alt', $img_alt ),
		array( 'property', 'article:published_time', $d['published'] ),
		array( 'property', 'article:modified_time', $d['modified'] ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $d['title'] ),
		array( 'name', 'twitter:description', $d['description'] ),
		array( 'name', 'twitter:image', $img ),
		array( 'name', 'twitter:image:alt', $img_alt ),
	);

	echo "\n<!-- Linkvorschau (Theme idt-deutschlandtakt) -->\n";
	foreach ( $tags as $tag ) {
		list( $attr, $key, $value ) = $tag;
		if ( '' === (string) $value ) {
			continue;
		}
		$is_url = in_array( $key, array( 'og:url', 'og:image', 'twitter:image' ), true );
		printf(
			'<meta %s="%s" content="%s">' . "\n",
			$attr,
			esc_attr( $key ),
			$is_url ? esc_url( $value ) : esc_attr( $value )
		);
	}
}
add_action( 'wp_head', 'idt_social_meta', 5 );

/* ---- Customizer: Standardtext und -bild ------------------------------- */
function idt_social_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'idt_social', array(
		'title'       => __( 'Linkvorschau (Social Media)', 'idt' ),
		'priority'    => 161, /* direkt hinter „Footer" */
		'description' => __( 'Was LinkedIn, Mastodon, Messenger & Co. zeigen, wenn ein Link auf diese Website geteilt wird. Einzelne Seiten und Beiträge überschreiben das mit ihrem Textauszug und Beitragsbild (Seitenleiste im Editor).', 'idt' ),
	) );

	$wp_customize->add_setting( 'idt_social_description', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'idt_social_description', array(
		'type'        => 'textarea',
		'section'     => 'idt_social',
		'label'       => __( 'Standardbeschreibung', 'idt' ),
		'description' => __( 'Ein bis zwei Sätze, höchstens etwa 200 Zeichen. Gilt für die Startseite und alle Seiten ohne eigenen Text. Leer = Untertitel der Website.', 'idt' ),
	) );

	$wp_customize->add_setting( 'idt_social_image', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'idt_social_image', array(
		'section'     => 'idt_social',
		'mime_type'   => 'image',
		'label'       => __( 'Standardbild', 'idt' ),
		'description' => __( 'Querformat 1200 × 630 px, wichtige Inhalte mittig (manche Dienste schneiden quadratisch zu). Leer = Logo auf Papierfläche.', 'idt' ),
	) ) );
}
add_action( 'customize_register', 'idt_social_customize_register' );
