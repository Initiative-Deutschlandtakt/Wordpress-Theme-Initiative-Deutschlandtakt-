<?php
/**
 * Suche — Lupe im Menüband, Overlay über die ganze Seite, Live-Ergebnisse.
 *
 * Die Kopfleiste zeigt statt eines Suchfeldes nur noch eine Lupe. Ein Klick
 * öffnet ein Overlay über die gesamte Seite (siehe idt_render_search_overlay(),
 * eingebunden in header.php), das die Treffer schon während des Tippens
 * anzeigt — gerendert vom REST-Endpunkt `idt/v1/suche`, der dieselben
 * Ergebnis-Bausteine ausgibt wie die vollständige Ergebnisseite (search.php).
 * Dadurch sehen Live-Vorschau und Ergebnisseite identisch aus.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Anzahl der Treffer in der Live-Vorschau des Overlays. */
const IDT_SEARCH_PREVIEW_COUNT = 5;

/* -------------------------------------------------------------------------
 * Markup: Suchfeld, Overlay, Trefferliste
 * ---------------------------------------------------------------------- */

/**
 * Das große Suchfeld (Overlay und Ergebnisseite nutzen dasselbe Formular).
 *
 * @param string $id    ID des Eingabefeldes (muss auf der Seite eindeutig sein).
 * @param string $value Vorbelegung, z. B. der aktuelle Suchbegriff.
 */
function idt_search_form( $id = 'idt-search-field', $value = '' ) {
	?>
	<form role="search" method="get" class="idt-searchfield" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<span class="idt-searchfield__icon" aria-hidden="true"><?php echo idt_icon( 'search', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Suche nach:', 'idt' ); ?></label>
		<input type="search" id="<?php echo esc_attr( $id ); ?>" class="idt-searchfield__input" name="s"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php esc_attr_e( 'Wonach suchen Sie?', 'idt' ); ?>"
			autocomplete="off" spellcheck="false">
		<button type="submit" class="idt-btn idt-btn--primary idt-searchfield__submit"><?php esc_html_e( 'Suchen', 'idt' ); ?></button>
	</form>
	<?php
}

/**
 * Das seitenfüllende Such-Overlay. Wird von header.php einmal pro Seite
 * ausgegeben (versteckt) und von assets/search.js geöffnet/geschlossen.
 */
function idt_render_search_overlay() {
	?>
	<div class="idt-searchbox" id="idt-searchbox" hidden>
		<div class="idt-searchbox__sheet" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Suche', 'idt' ); ?>">
			<button type="button" class="idt-searchbox__close" aria-label="<?php esc_attr_e( 'Suche schließen', 'idt' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>
			<div class="idt-searchbox__inner">
				<span class="idt-eyebrow"><?php esc_html_e( 'Suche', 'idt' ); ?></span>
				<?php idt_search_form( 'idt-searchbox-field' ); ?>
				<p class="idt-searchbox__hint"><?php esc_html_e( 'Treffer erscheinen beim Tippen — mit Eingabe zur vollständigen Ergebnisseite, mit Esc schließen.', 'idt' ); ?></p>
				<div class="idt-searchbox__results" id="idt-searchbox-results" aria-live="polite" aria-busy="false"></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Hebt den Suchbegriff im Text hervor. Arbeitet auf dem bereits escapten
 * Text, damit die Hervorhebung kein Markup einschleusen kann.
 *
 * @param string $text Klartext (unescaped).
 * @param string $q    Suchbegriff.
 * @return string Escaptes HTML, Treffer in <mark> gefasst.
 */
function idt_search_highlight( $text, $q ) {
	$escaped = esc_html( $text );
	$words   = preg_split( '/\s+/u', trim( (string) $q ) );
	$terms   = array();

	foreach ( (array) $words as $word ) {
		/* Kurze Wörter (Artikel, Präpositionen) nicht hervorheben — sonst ist
		   die halbe Zeile markiert. Der vollständige Suchbegriff bleibt drin. */
		if ( '' !== $word && mb_strlen( $word ) >= 3 ) {
			$terms[] = $word;
		}
	}
	$full = trim( (string) $q );
	if ( '' !== $full && ! in_array( $full, $terms, true ) && mb_strlen( $full ) >= 3 ) {
		$terms[] = $full;
	}
	if ( ! $terms ) {
		return $escaped;
	}

	/* Längste Begriffe zuerst, damit der ganze Suchbegriff vor seinen
	   Einzelwörtern greift. */
	usort( $terms, function ( $a, $b ) {
		return mb_strlen( $b ) - mb_strlen( $a );
	} );

	$patterns = array();
	foreach ( array_unique( $terms ) as $term ) {
		$patterns[] = preg_quote( esc_html( $term ), '/' );
	}

	$result = preg_replace(
		'/(' . implode( '|', $patterns ) . ')/iu',
		'<mark class="idt-searchhit">$1</mark>',
		$escaped
	);

	return null === $result ? $escaped : $result;
}

/** Kurzer Anrisstext eines Treffers (Auszug, sonst Anfang des Inhalts). */
function idt_search_excerpt( $post, $words = 30 ) {
	$raw = '' !== trim( (string) $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
	$raw = wp_strip_all_tags( strip_shortcodes( $raw ) );
	return wp_trim_words( $raw, $words, '…' );
}

/** Beschriftung des Inhaltstyps eines Treffers („Beitrag“, „Seite“ …). */
function idt_search_type_label( $post ) {
	$obj = get_post_type_object( get_post_type( $post ) );
	if ( $obj && isset( $obj->labels->singular_name ) ) {
		return $obj->labels->singular_name;
	}
	return __( 'Inhalt', 'idt' );
}

/**
 * Ein Treffer als Karte — identisch im Overlay und auf der Ergebnisseite.
 *
 * @param WP_Post $post Treffer.
 * @param string  $q    Suchbegriff (für die Hervorhebung).
 */
function idt_search_result_item( $post, $q = '' ) {
	$tags = '';
	if ( 'post' === get_post_type( $post ) && function_exists( 'idt_post_tags_html' ) ) {
		$tags = idt_post_tags_html( $post->ID, 3, false );
	}

	$html  = '<a class="idt-result" href="' . esc_url( get_permalink( $post ) ) . '">';
	$html .= '<span class="idt-result__meta">';
	$html .= '<span class="idt-result__type">' . esc_html( idt_search_type_label( $post ) ) . '</span>';
	if ( 'post' === get_post_type( $post ) ) {
		$html .= '<span class="idt-result__date">' . esc_html( get_the_date( '', $post ) ) . '</span>';
	}
	$html .= '</span>';
	$html .= '<span class="idt-result__title">' . idt_search_highlight( get_the_title( $post ), $q ) . '</span>';

	$excerpt = idt_search_excerpt( $post );
	if ( '' !== $excerpt ) {
		$html .= '<span class="idt-result__text">' . idt_search_highlight( $excerpt, $q ) . '</span>';
	}
	if ( $tags ) {
		$html .= '<span class="idt-result__tags">' . $tags . '</span>';
	}
	$html .= '<span class="idt-result__more">' . esc_html__( 'Öffnen', 'idt' ) . ' →</span>';
	$html .= '</a>';

	return $html;
}

/**
 * Trefferliste aus einer Beitrags-Sammlung.
 *
 * @param WP_Post[] $posts Treffer.
 * @param string    $q     Suchbegriff.
 */
function idt_search_results_list( $posts, $q = '' ) {
	if ( ! $posts ) {
		return '';
	}
	$html = '<div class="idt-results">';
	foreach ( $posts as $post ) {
		$html .= idt_search_result_item( $post, $q );
	}
	return $html . '</div>';
}

/** Hinweisbox „nichts gefunden“ — im Overlay wie auf der Ergebnisseite. */
function idt_search_empty_notice( $q ) {
	return '<p class="idt-search-empty">'
		. sprintf(
			/* translators: %s = Suchbegriff */
			esc_html__( 'Keine Treffer für „%s“. Andere oder kürzere Suchbegriffe führen oft weiter.', 'idt' ),
			esc_html( $q )
		)
		. '</p>';
}

/* -------------------------------------------------------------------------
 * REST-Endpunkt für die Live-Vorschau im Overlay
 * ----------------------------------------------------------------------
 * Öffentlich lesbar (die Suche selbst ist es auch) und liefert ausschließlich
 * veröffentlichte Inhalte — gerendert als fertiges Markup, damit Vorschau und
 * Ergebnisseite garantiert dieselben Bausteine nutzen.
 */
function idt_register_search_route() {
	register_rest_route( 'idt/v1', '/suche', array(
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => '__return_true',
		'callback'            => 'idt_rest_search',
		'args'                => array(
			'q' => array(
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	) );
}
add_action( 'rest_api_init', 'idt_register_search_route' );

function idt_rest_search( $request ) {
	$q = trim( (string) $request->get_param( 'q' ) );

	if ( mb_strlen( $q ) < 2 ) {
		return rest_ensure_response( array(
			'q'     => $q,
			'total' => 0,
			'html'  => '',
			'more'  => '',
		) );
	}

	$query = new WP_Query( array(
		's'                   => $q,
		'post_type'           => array( 'post', 'page' ),
		'post_status'         => 'publish',
		'posts_per_page'      => IDT_SEARCH_PREVIEW_COUNT,
		'ignore_sticky_posts' => true,
	) );

	$total = (int) $query->found_posts;
	$html  = $total ? idt_search_results_list( $query->posts, $q ) : idt_search_empty_notice( $q );

	return rest_ensure_response( array(
		'q'     => $q,
		'total' => $total,
		'shown' => count( $query->posts ),
		'html'  => $html,
		'more'  => esc_url_raw( home_url( '/?s=' . rawurlencode( $q ) ) ),
	) );
}
