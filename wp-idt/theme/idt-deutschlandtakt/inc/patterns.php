<?php
/**
 * Block-Patterns: fertige Bausteine aus den IDT-Stilelementen,
 * im Block-Editor unter der Kategorie "Deutschlandtakt" abrufbar.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function idt_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) { return; }

	register_block_pattern_category( 'idt', array( 'label' => __( 'Deutschlandtakt', 'idt' ) ) );

	register_block_pattern( 'idt/kennzahlen', array(
		'title'      => __( 'Kennzahlen-Reihe', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[stat number=\"2008\" label=\"gegründet\"]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[stat number=\"30 Min.\" label=\"Knotentakt\"]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[stat number=\"100 %\" label=\"Ökostrom\"]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "</div>\n<!-- /wp:columns -->",
	) );

	register_block_pattern( 'idt/diagonal-aussage', array(
		'title'      => __( 'Diagonal-Aussageblock', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:shortcode -->[diagonal]Der Fahrplan wird zur Grundlage aller Entscheidungen — nicht umgekehrt.[/diagonal]<!-- /wp:shortcode -->",
	) );

	register_block_pattern( 'idt/horizont-splash', array(
		'title'      => __( 'Horizont-Splash (Startseiten-Hero)', 'idt' ),
		'description' => __( 'Der Hero der aktuellen Live-Startseite (initiative-deutschlandtakt.de): diagonale Marken-Bühne mit zentriertem Logo, drei Pill-Links und Schlagzeile.', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:shortcode -->[splash]<!-- /wp:shortcode -->",
	) );

	register_block_pattern( 'idt/eyebrow-headline', array(
		'title'      => __( 'Eyebrow + Überschrift', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:shortcode -->[eyebrow]Über die Initiative[/eyebrow]<!-- /wp:shortcode -->\n"
			. "<!-- wp:heading {\"level\":2} --><h2>Mehr Verkehr auf die Schiene</h2><!-- /wp:heading -->",
	) );

	/* --- Lieblingselemente aus der Example Landing Page --- */

	register_block_pattern( 'idt/konzept-karten', array(
		'title'      => __( 'Konzept-Karten (farbige Oberkante)', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[concept color=\"violet\" icon=\"clock\" title=\"Erst der Fahrplan\"]Züge fahren jede Stunde – auf wichtigen Strecken halbstündlich – immer zur selben Minute. Leicht zu merken und verlässlich.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[concept color=\"cyan\" icon=\"rail\" title=\"Dann die Infrastruktur\"]Aus dem Zielfahrplan wird abgeleitet, was gebaut werden muss. Engpässe werden gezielt aufgelöst statt teurer Einzelprojekte.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[concept color=\"yellow\" icon=\"netz\" title=\"Anschluss im ganzen Land\"]In Knotenbahnhöfen treffen sich die Linien und ermöglichen kurze, sichere Umstiege – bis zur entferntesten Regionalbuslinie.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "</div>\n<!-- /wp:columns -->",
	) );

	register_block_pattern( 'idt/dunkler-einschub', array(
		'title'      => __( 'Dunkler Einschub (Vorbild-Band)', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:shortcode -->[einschub eyebrow=\"Das Vorbild\" title=\"Die Schweiz fährt seit Jahrzehnten im Takt.\"]"
			. "Wo es einen landesweiten Taktfahrplan gibt, legen die Menschen einen weit größeren Teil ihrer Wege mit öffentlichen Verkehrsmitteln zurück.\n\n"
			. "[stat number=\"28 %\" label=\"Wegeanteil ÖV · Schweiz\"]  [stat number=\"19 %\" label=\"Wegeanteil ÖV · Deutschland\"]"
			. "[/einschub]<!-- /wp:shortcode -->",
	) );

	register_block_pattern( 'idt/social-links', array(
		'title'      => __( 'Social-Links (Icon-Reihe)', 'idt' ),
		'description' => __( 'Reihe runder Social-Icon-Buttons. Platzhalter-Links (#) — nach Einfügen je Icon über die Seitenleiste die echte Profil-URL eintragen.', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:shortcode -->[social platform=\"x\" href=\"#\"] [social platform=\"facebook\" href=\"#\"] "
			. "[social platform=\"instagram\" href=\"#\"] [social platform=\"linkedin\" href=\"#\"] "
			. "[social platform=\"youtube\" href=\"#\"] [social platform=\"mastodon\" href=\"#\"] "
			. "[social platform=\"bluesky\" href=\"#\"] [social platform=\"rss\" href=\"#\"]<!-- /wp:shortcode -->",
	) );

	register_block_pattern( 'idt/news-karten', array(
		'title'      => __( 'News-Karten (Aus der Initiative)', 'idt' ),
		'categories' => array( 'idt' ),
		'content'    => "<!-- wp:shortcode -->[eyebrow]Aktuell[/eyebrow]<!-- /wp:shortcode -->\n"
			. "<!-- wp:heading {\"level\":2} --><h2>Aus der Initiative</h2><!-- /wp:heading -->\n"
			. "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[newscard tag=\"Stellungnahme\" color=\"violet\" date=\"12.02.2026\" href=\"#\"]Neuberechnung des Deutschlandtakts? Die Initiative widerspricht[/newscard]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[newscard tag=\"Gesetzgebung\" color=\"cyan\" date=\"24.11.2025\" href=\"#\"]Agenda für zufriedene Kunden auf der Schiene[/newscard]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:shortcode -->[newscard tag=\"Prognose\" color=\"yellow\" date=\"24.10.2025\" href=\"#\"]Verkehrsprognose 2040 bestätigt Wirkung des Deutschlandtakts[/newscard]<!-- /wp:shortcode --></div><!-- /wp:column -->"
			. "</div>\n<!-- /wp:columns -->",
	) );

}
add_action( 'init', 'idt_register_patterns' );
