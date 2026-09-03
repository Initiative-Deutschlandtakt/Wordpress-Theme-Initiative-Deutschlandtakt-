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
		'title'       => __( 'Konzept-Karten (Raster, farbige Oberkante)', 'idt' ),
		'description' => __( 'Drei Konzept-Karten in einem Karten-Raster: gleiche Breite, gleiche Höhe, automatischer Umbruch auf schmalen Bildschirmen.', 'idt' ),
		'categories'  => array( 'idt' ),
		'content'     => "<!-- wp:idt/kartenraster {\"cols\":\"3\"} -->\n<div class=\"idt-cards idt-cards--3\">"
			. "<!-- wp:idt/concept {\"title\":\"Erst der Fahrplan\",\"color\":\"violet\",\"icon\":\"clock\",\"text\":\"Züge fahren jede Stunde – auf wichtigen Strecken halbstündlich – immer zur selben Minute. Leicht zu merken und verlässlich.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Dann die Infrastruktur\",\"color\":\"cyan\",\"icon\":\"rail\",\"text\":\"Aus dem Zielfahrplan wird abgeleitet, was gebaut werden muss. Engpässe werden gezielt aufgelöst statt teurer Einzelprojekte.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Anschluss im ganzen Land\",\"color\":\"yellow\",\"icon\":\"netz\",\"text\":\"In Knotenbahnhöfen treffen sich die Linien und ermöglichen kurze, sichere Umstiege – bis zur entferntesten Regionalbuslinie.\"} /-->\n"
			. "</div>\n<!-- /wp:idt/kartenraster -->",
	) );

	register_block_pattern( 'idt/themen-karten', array(
		'title'       => __( 'Themen-Karten (Einstiegs-Raster)', 'idt' ),
		'description' => __( 'Vier gleich große Einstiegskarten ohne Icon (Oberkante in Tinte) — der Einstieg in ein Thema, z. B. Idee, Geschichte, Akteure, Fragen. Karten mit Link werden ganzflächig klickbar.', 'idt' ),
		'categories'  => array( 'idt' ),
		'content'     => "<!-- wp:idt/kartenraster {\"cols\":\"3\"} -->\n<div class=\"idt-cards idt-cards--3\">"
			. "<!-- wp:idt/concept {\"title\":\"Die Idee\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Jede Stunde zur selben Minute — das Prinzip in fünf Minuten Lesezeit.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Geschichte des Deutschlandtakts\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Von der Schweizer Vorlage zum Koalitionsvertrag und zum Zielfahrplan.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Akteure\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Ministerium, Bundestag, Länder, DB InfraGO, Aufgabenträger — wer entscheidet was.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Häufige Fragen\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Kostet das mehr? Kommt es pünktlich? Was heißt das für meine Region?\"} /-->\n"
			. "</div>\n<!-- /wp:idt/kartenraster -->",
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
			. "<!-- wp:idt/kartenraster {\"cols\":\"3\"} -->\n<div class=\"idt-cards idt-cards--3\">"
			. "<!-- wp:idt/newscard {\"text\":\"Neuberechnung des Deutschlandtakts? Die Initiative widerspricht\",\"tag\":\"Stellungnahme\",\"color\":\"violet\",\"date\":\"12.02.2026\",\"href\":\"#\"} /-->\n"
			. "<!-- wp:idt/newscard {\"text\":\"Agenda für zufriedene Kunden auf der Schiene\",\"tag\":\"Gesetzgebung\",\"color\":\"cyan\",\"date\":\"24.11.2025\",\"href\":\"#\"} /-->\n"
			. "<!-- wp:idt/newscard {\"text\":\"Verkehrsprognose 2040 bestätigt Wirkung des Deutschlandtakts\",\"tag\":\"Prognose\",\"color\":\"yellow\",\"date\":\"24.10.2025\",\"href\":\"#\"} /-->\n"
			. "</div>\n<!-- /wp:idt/kartenraster -->",
	) );

}
add_action( 'init', 'idt_register_patterns' );
