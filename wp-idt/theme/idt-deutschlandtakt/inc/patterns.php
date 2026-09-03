<?php
/**
 * Block-Patterns: fertige Bausteine aus den IDT-Stilelementen,
 * im Block-Editor unter der Kategorie "Deutschlandtakt" abrufbar.
 *
 * Drei Regeln, damit die Vorschau in der Vorlagen-Seitenleiste taugt:
 *
 * 1. Keine [shortcode]-Blöcke, sondern native idt/*-Blöcke bzw. — wo es ein
 *    Inline-Format gibt — WordPress-Standardblöcke mit dem fertigen Markup.
 *    Der WordPress-Block „Shortcode" zeigt im Editor und damit auch in der
 *    Vorlagen-Vorschau nur seinen rohen Text in einem Eingabefeld; eine
 *    Vorlage aus Shortcodes sieht dort aus wie ein Formular, nicht wie das
 *    fertige Element.
 * 2. Der Rahmen des Karten-Rasters kommt aus idt_kartenraster_open() (siehe
 *    inc/blocks.php): Er muss exakt dem entsprechen, was der Block beim
 *    Speichern erzeugt — inklusive der generierten Blockklasse.
 * 3. viewportWidth setzen. Die Vorschau rendert die Vorlage zuerst in dieser
 *    Breite und skaliert sie dann in den schmalen Vorschau-Rahmen. Ohne Angabe
 *    nimmt WordPress 700px an — ein dreispaltiges Karten-Raster (ab 960px)
 *    zeigt dort ein zweispaltiges Layout, das im Frontend nie vorkommt.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function idt_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) { return; }

	register_block_pattern_category( 'idt', array( 'label' => __( 'Deutschlandtakt', 'idt' ) ) );

	register_block_pattern( 'idt/kennzahlen', array(
		'title'         => __( 'Kennzahlen-Reihe', 'idt' ),
		'description'   => __( 'Drei Kennzahlen nebeneinander — große Zahl mit Mono-Label darunter.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 900,
		'content'       => "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:idt/stat {\"number\":\"2008\",\"label\":\"gegründet\"} /--></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:idt/stat {\"number\":\"30 Min.\",\"label\":\"Knotentakt\"} /--></div><!-- /wp:column -->"
			. "<!-- wp:column --><div class=\"wp-block-column\"><!-- wp:idt/stat {\"number\":\"100 %\",\"label\":\"Ökostrom\"} /--></div><!-- /wp:column -->"
			. "</div>\n<!-- /wp:columns -->",
	) );

	register_block_pattern( 'idt/diagonal-aussage', array(
		'title'         => __( 'Diagonal-Aussageblock', 'idt' ),
		'description'   => __( 'Eine große Aussage auf dunklem Grund mit diagonalen Marken-Streifen.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 900,
		'content'       => "<!-- wp:idt/diagonal {\"text\":\"Der Fahrplan wird zur Grundlage aller Entscheidungen — nicht umgekehrt.\"} /-->",
	) );

	register_block_pattern( 'idt/horizont-splash', array(
		'title'         => __( 'Horizont-Splash (Startseiten-Hero)', 'idt' ),
		'description'   => __( 'Der Hero der aktuellen Live-Startseite (initiative-deutschlandtakt.de): diagonale Marken-Bühne mit zentriertem Logo, drei Pill-Links und Schlagzeile.', 'idt' ),
		'categories'    => array( 'idt' ),
		/* Die Bühne ist auf 1280×800 gebaut — in dieser Breite zeigt die
		   Vorschau genau das Frontend-Layout. */
		'viewportWidth' => 1280,
		'content'       => "<!-- wp:idt/splash /-->",
	) );

	register_block_pattern( 'idt/eyebrow-headline', array(
		'title'         => __( 'Eyebrow + Überschrift', 'idt' ),
		'description'   => __( 'Mono-Label als Dachzeile über einer Überschrift — der Standard-Einstieg in einen Abschnitt.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 800,
		/* Das Eyebrow ist ein Inline-Format (assets/editor-formats.js), kein Block:
		   als Absatz-Markup rendert die Vorschau es sofort und der Text bleibt
		   direkt im Editor bearbeitbar. */
		'content'       => "<!-- wp:paragraph --><p><span class=\"idt-eyebrow\">Über die Initiative</span></p><!-- /wp:paragraph -->\n"
			. "<!-- wp:heading {\"level\":2} --><h2>Mehr Verkehr auf die Schiene</h2><!-- /wp:heading -->",
	) );

	/* --- Lieblingselemente aus der Example Landing Page --- */

	register_block_pattern( 'idt/konzept-karten', array(
		'title'         => __( 'Konzept-Karten (Raster, farbige Oberkante)', 'idt' ),
		'description'   => __( 'Drei Konzept-Karten in einem Karten-Raster: gleiche Breite, gleiche Höhe, automatischer Umbruch auf schmalen Bildschirmen.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 1100,
		'content'       => idt_kartenraster_open( '3' )
			. "<!-- wp:idt/concept {\"title\":\"Erst der Fahrplan\",\"color\":\"violet\",\"icon\":\"clock\",\"text\":\"Züge fahren jede Stunde – auf wichtigen Strecken halbstündlich – immer zur selben Minute. Leicht zu merken und verlässlich.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Dann die Infrastruktur\",\"color\":\"cyan\",\"icon\":\"rail\",\"text\":\"Aus dem Zielfahrplan wird abgeleitet, was gebaut werden muss. Engpässe werden gezielt aufgelöst statt teurer Einzelprojekte.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Anschluss im ganzen Land\",\"color\":\"yellow\",\"icon\":\"netz\",\"text\":\"In Knotenbahnhöfen treffen sich die Linien und ermöglichen kurze, sichere Umstiege – bis zur entferntesten Regionalbuslinie.\"} /-->\n"
			. idt_kartenraster_close(),
	) );

	register_block_pattern( 'idt/themen-karten', array(
		'title'         => __( 'Themen-Karten (Einstiegs-Raster)', 'idt' ),
		'description'   => __( 'Vier gleich große Einstiegskarten ohne Icon (Oberkante in Tinte) — der Einstieg in ein Thema, z. B. Idee, Geschichte, Akteure, Fragen. Karten mit Link werden ganzflächig klickbar.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 1100,
		'content'       => idt_kartenraster_open( '3' )
			. "<!-- wp:idt/concept {\"title\":\"Die Idee\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Jede Stunde zur selben Minute — das Prinzip in fünf Minuten Lesezeit.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Geschichte des Deutschlandtakts\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Von der Schweizer Vorlage zum Koalitionsvertrag und zum Zielfahrplan.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Akteure\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Ministerium, Bundestag, Länder, DB InfraGO, Aufgabenträger — wer entscheidet was.\"} /-->\n"
			. "<!-- wp:idt/concept {\"title\":\"Häufige Fragen\",\"color\":\"ink\",\"icon\":\"\",\"href\":\"\",\"text\":\"Kostet das mehr? Kommt es pünktlich? Was heißt das für meine Region?\"} /-->\n"
			. idt_kartenraster_close(),
	) );

	register_block_pattern( 'idt/dunkler-einschub', array(
		'title'         => __( 'Dunkler Einschub (Vorbild-Band)', 'idt' ),
		'description'   => __( 'Breites Band auf tiefem Teal: Eyebrow, große Überschrift, Fließtext und zwei Kennzahlen.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 1000,
		'content'       => "<!-- wp:idt/einschub {\"eyebrow\":\"Das Vorbild\","
			. "\"title\":\"Die Schweiz fährt seit Jahrzehnten im Takt.\","
			. "\"text\":\"Wo es einen landesweiten Taktfahrplan gibt, legen die Menschen einen weit größeren Teil ihrer Wege mit öffentlichen Verkehrsmitteln zurück.\","
			. "\"stats\":\"28 % | Wegeanteil ÖV · Schweiz\\n19 % | Wegeanteil ÖV · Deutschland\"} /-->",
	) );

	register_block_pattern( 'idt/themenblock', array(
		'title'       => __( 'Themenblock mit Linkliste', 'idt' ),
		'description' => __( 'Farbige Fläche mit Eyebrow, Überschrift, Einleitung und einer beliebig langen Linkliste — der Einstieg in einen Themenbereich. Hintergrundfarbe in der Seitenleiste frei wählbar; die Schriftfarbe stellt sich passend zum Kontrast ein.', 'idt' ),
		'categories'  => array( 'idt' ),
		'content'     => "<!-- wp:idt/themenblock {\"bg\":\"#00373C\",\"eyebrow\":\"Bereich 02 · Unsere Stimme\",\"title\":\"Unser Plan\","
			. "\"lead\":\"Wofür die Initiative eintritt, was im Weg steht, was jetzt ansteht.\","
			. "\"text\":\"Kein Konkurrenzkonzept, sondern Qualitätssicherung am laufenden Prozess: wir prüfen, benennen Lücken und schlagen vor, was die Etappe 2035 noch braucht.\","
			. "\"links\":\"Die Vision | # | Wie ein verlässliches Angebot 2035 aussieht\\nWo es hakt | # | Engpässe, Fristen und offene Entscheidungen\\nPositionen | # | Unsere Stellungnahmen zum Umsetzungsprozess\\nAusblick | # | Was in dieser Legislaturperiode ansteht\"} /-->",
	) );

	register_block_pattern( 'idt/social-links', array(
		'title'         => __( 'Social-Links (Icon-Reihe)', 'idt' ),
		'description'   => __( 'Reihe runder Social-Icon-Buttons. Platzhalter-Links (#) — nach dem Einfügen in der Seitenleiste je Zeile die echte Profil-URL eintragen.', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 700,
		'content'       => "<!-- wp:idt/sociallinks {\"links\":\"x | #\\nfacebook | #\\ninstagram | #\\nlinkedin | #\\nyoutube | #\\nmastodon | #\\nbluesky | #\\nrss | #\"} /-->",
	) );

	register_block_pattern( 'idt/news-karten', array(
		'title'         => __( 'News-Karten (Aus der Initiative)', 'idt' ),
		'description'   => __( 'Abschnitts-Kopf plus drei News-Karten mit Schlagwort-Chip, Datum und Weiterlesen-Link — für handverlesene Meldungen. Automatisch gefüllt: Block „Beiträge-Übersicht".', 'idt' ),
		'categories'    => array( 'idt' ),
		'viewportWidth' => 1100,
		'content'       => "<!-- wp:paragraph --><p><span class=\"idt-eyebrow\">Aktuell</span></p><!-- /wp:paragraph -->\n"
			. "<!-- wp:heading {\"level\":2} --><h2>Aus der Initiative</h2><!-- /wp:heading -->\n"
			. idt_kartenraster_open( '3' )
			. "<!-- wp:idt/newscard {\"text\":\"Neuberechnung des Deutschlandtakts? Die Initiative widerspricht\",\"tag\":\"Stellungnahme\",\"color\":\"violet\",\"date\":\"12.02.2026\",\"href\":\"#\"} /-->\n"
			. "<!-- wp:idt/newscard {\"text\":\"Agenda für zufriedene Kunden auf der Schiene\",\"tag\":\"Gesetzgebung\",\"color\":\"cyan\",\"date\":\"24.11.2025\",\"href\":\"#\"} /-->\n"
			. "<!-- wp:idt/newscard {\"text\":\"Verkehrsprognose 2040 bestätigt Wirkung des Deutschlandtakts\",\"tag\":\"Prognose\",\"color\":\"yellow\",\"date\":\"24.10.2025\",\"href\":\"#\"} /-->\n"
			. idt_kartenraster_close(),
	) );

}
add_action( 'init', 'idt_register_patterns' );
