<?php
/**
 * Einmalige Demo-Inhalte: Seiten, Beiträge, Menüs und Front-Page-Einstellung.
 * Läuft beim Aktivieren des Themes (after_switch_theme) — durch eine Option
 * abgesichert, sodass nichts doppelt angelegt wird.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Legt eine Seite/Beitrag an, falls noch nicht vorhanden, und liefert die ID. */
function idt_upsert( $slug, $args ) {
	$existing = get_page_by_path( $slug, OBJECT, $args['post_type'] ?? 'page' );
	if ( $existing ) {
		return $existing->ID;
	}
	return wp_insert_post( wp_parse_args( $args, array(
		'post_name'   => $slug,
		'post_status' => 'publish',
		'post_type'   => 'page',
	) ) );
}

function idt_seed_demo_content() {
	if ( get_option( 'idt_seeded' ) ) {
		return;
	}

	/* ---------------------------------------------------------------------
	 * 1) Seiten
	 * ------------------------------------------------------------------ */

	$home_id = idt_upsert( 'startseite', array(
		'post_title'   => 'Startseite',
		'post_content' => '<!-- wp:paragraph --><p>Willkommen bei der Initiative Deutschlandtakt.</p><!-- /wp:paragraph -->',
	) );

	$ueber_id = idt_upsert( 'ueber-die-initiative', array(
		'post_title'   => 'Über die Initiative',
		'post_content' => idt_content_ueber(),
	) );

	$revolution_id = idt_upsert( 'revolution-der-planung', array(
		'post_title'   => 'Revolution der Planung',
		'post_content' => idt_content_revolution(),
	) );

	$klima_id = idt_upsert( 'deutschlandtakt-und-klimaschutz', array(
		'post_title'   => 'Deutschlandtakt und Klimaschutz',
		'post_content' => idt_content_klima(),
	) );

	$etappen_id = idt_upsert( 'in-etappen-zum-deutschlandtakt', array(
		'post_title'   => 'In Etappen zum Deutschlandtakt',
		'post_content' => idt_content_etappen(),
	) );

	$stil_id = idt_upsert( 'stilelemente', array(
		'post_title'   => 'Stilelemente',
		'post_content' => idt_content_stilelemente(),
	) );

	$aktuell_id = idt_upsert( 'aktuelles', array(
		'post_title'   => 'Aktuelles',
		'post_content' => '<!-- wp:paragraph --><p>Neuigkeiten aus der Initiative.</p><!-- /wp:paragraph -->',
	) );

	/* Rechtsseiten — Gerüst mit markierten Platzhaltern; die verbindlichen
	 * Rechtstexte pflegt der Verein im Editor. Der Footer verlinkt per Slug. */
	$impressum_id = idt_upsert( 'impressum', array(
		'post_title'   => 'Impressum',
		'post_content' => idt_content_impressum(),
	) );

	$datenschutz_id = idt_upsert( 'datenschutz', array(
		'post_title'   => 'Datenschutzerklärung',
		'post_content' => idt_content_datenschutz(),
	) );

	/* ---------------------------------------------------------------------
	 * 2) Beiträge (Aktuelles)
	 * ------------------------------------------------------------------ */
	foreach ( idt_demo_posts() as $slug => $p ) {
		$post_id = idt_upsert( $slug, array(
			'post_type'    => 'post',
			'post_title'   => $p['title'],
			'post_excerpt' => $p['excerpt'],
			'post_content' => $p['content'],
		) );
		/* Echte Schlagwörter (post_tag) zuweisen — Grundlage fürs Filtern der
		 * Beitragsansicht. Bestehende Zuordnungen werden nicht überschrieben,
		 * da idt_upsert vorhandene Beiträge unangetastet lässt. */
		if ( $post_id && ! is_wp_error( $post_id ) && ! empty( $p['tags'] ) ) {
			wp_set_post_tags( $post_id, $p['tags'] );
		}
	}

	/* Startseite jetzt mit dem editierbaren Homepage-Design befüllen — erst hier,
	 * da die verlinkten Unterseiten und Beiträge bestehen müssen. */
	wp_update_post( array( 'ID' => $home_id, 'post_content' => idt_content_startseite() ) );

	/* ---------------------------------------------------------------------
	 * 3) Front-Page-Konfiguration
	 * ------------------------------------------------------------------ */
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	update_option( 'page_for_posts', $aktuell_id );
	update_option( 'wp_page_for_privacy_policy', $datenschutz_id );
	update_option( 'blogname', 'Initiative Deutschlandtakt' );
	update_option( 'blogdescription', 'Mehr Verkehr auf die Schiene.' );

	/* "Hallo Welt"-Standardbeitrag & "Beispiel-Seite" entfernen */
	if ( $sample = get_page_by_path( 'beispiel-seite' ) ) { wp_delete_post( $sample->ID, true ); }
	if ( $sample = get_page_by_path( 'sample-page' ) )    { wp_delete_post( $sample->ID, true ); }
	if ( $hello = get_page_by_path( 'hallo-welt', OBJECT, 'post' ) ) { wp_delete_post( $hello->ID, true ); }
	if ( $hello = get_page_by_path( 'hello-world', OBJECT, 'post' ) ) { wp_delete_post( $hello->ID, true ); }

	/* ---------------------------------------------------------------------
	 * 4) Menüs
	 * ------------------------------------------------------------------ */
	/* Bewusst kurze Labels, damit das Menü auf Desktop in einer Zeile bleibt. */
	idt_build_menu( 'Hauptmenü', 'primary', array(
		array( 'page', $ueber_id,      'Initiative' ),
		array( 'page', $revolution_id, 'Planung' ),
		array( 'page', $klima_id,      'Klimaschutz' ),
		array( 'page', $etappen_id,    'Etappen' ),
		array( 'page', $aktuell_id,    'Aktuelles' ),
	) );

	idt_build_menu( 'Footer-Menü', 'footer', array(
		array( 'page', $revolution_id ),
		array( 'page', $klima_id ),
		array( 'page', $etappen_id ),
		array( 'page', $ueber_id ),
	) );

	update_option( 'idt_seeded', 1 );
	flush_rewrite_rules();
}

/** Baut ein Menü aus Seiten-IDs und weist es einem Theme-Standort zu. */
function idt_build_menu( $name, $location, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
	} else {
		$menu_id = $menu->term_id;
		/* vorhandene Items leeren, um Dubletten zu vermeiden */
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $it ) { wp_delete_post( $it->ID, true ); }
	}

	foreach ( $items as $item ) {
		list( $type, $object_id ) = $item;
		$label = isset( $item[2] ) ? $item[2] : get_the_title( $object_id );
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => $object_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-title'     => $label,
		) );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/* =========================================================================
 * Inhalts-Bausteine (German). Dummy-Texte mit echtem Kontext, angelehnt an
 * initiative-deutschlandtakt.de.
 * ====================================================================== */

/**
 * Editierbares Homepage-Design als Block-/Shortcode-Komposition.
 * Wird in die „Startseite"-Seite geschrieben und von front-page.php via
 * the_content() gerendert — dadurch ist die Homepage im Editor bearbeitbar.
 */
function idt_content_startseite() {
	$ueber = idt_page_url( 'ueber-die-initiative' );
	$rev   = idt_page_url( 'revolution-der-planung' );
	$klima = idt_page_url( 'deutschlandtakt-und-klimaschutz' );
	$etapp = idt_page_url( 'in-etappen-zum-deutschlandtakt' );

	return <<<HTML
<!-- wp:shortcode -->[splash]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[eyebrow]Bürger für ein effizienteres Schienennetz[/eyebrow]<!-- /wp:shortcode -->

<!-- wp:heading {"level":1} --><h1>Mehr Verkehr <mark class="idt-mark">auf die Schiene</mark>.</h1><!-- /wp:heading -->

<!-- wp:shortcode -->[lead]Der Deutschlandtakt ist ein integraler Taktfahrplan für den gesamten öffentlichen Verkehr: Züge sind so aufeinander abgestimmt, dass Anschlüsse in den Knotenbahnhöfen kurz und in alle Richtungen gewährleistet sind.[/lead]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[btn href="$ueber" variant="primary" arrow="true"]Über die Initiative[/btn] [btn href="mailto:mail@initiative-deutschlandtakt.de?subject=Mitglied%20werden" variant="outline"]Mitglied werden[/btn]<!-- /wp:shortcode -->

<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="2008" label="gegründet"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="30 Min." label="Knotentakt"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="2023" label="im Gesetz verankert"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="100 %" label="Ökostrom möglich"]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->

<!-- wp:shortcode -->[eyebrow]Schwerpunkte[/eyebrow]<!-- /wp:shortcode -->
<!-- wp:heading {"level":2} --><h2>Worum es uns geht</h2><!-- /wp:heading -->

<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="violet" icon="clock" title="Revolution der Planung" href="$rev"]Nicht die Politik gibt das Ziel vor — der Fahrplan wird zur Grundlage aller Infrastrukturentscheidungen.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="cyan" icon="netz" title="Klimaschutz" href="$klima"]Der Deutschlandtakt verlagert Verkehr von der Straße auf die Schiene und spart so Energie, Rohstoffe und Fläche.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="yellow" icon="rail" title="In Etappen" href="$etapp"]Der Takt entsteht nicht über Nacht: ein realistischer Stufenplan führt Schritt für Schritt zum Ziel.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->

<!-- wp:shortcode -->[einschub eyebrow="Das Vorbild" title="Die Schweiz fährt seit Jahrzehnten im Takt."]Wo es einen landesweiten Taktfahrplan gibt, legen die Menschen einen weit größeren Teil ihrer Wege mit öffentlichen Verkehrsmitteln zurück. Abgestimmte Angebote führen zu nachhaltig steigenden Fahrgastzahlen.

[stat number="28 %" label="Wegeanteil ÖV · Schweiz"]  [stat number="19 %" label="Wegeanteil ÖV · Deutschland"][/einschub]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[neuigkeiten count="3" eyebrow="Aktuelles" title="Aus der Initiative"]<!-- /wp:shortcode -->
HTML;
}

function idt_content_ueber() {
	return <<<HTML
<!-- wp:shortcode -->[lead]Wir sind eine unabhängige Bürgerinitiative für einen attraktiven Bahnverkehr für alle — den Deutschlandtakt. Unabhängig von Einflüssen aus Politik und Unternehmen.[/lead]<!-- /wp:shortcode -->

<!-- wp:paragraph --><p>Im Frühjahr 2008 haben sechs Personen aus dem Verkehrsbereich die Initiative Deutschlandtakt gegründet, im Laufe der Zeit sind weitere Bürgerinnen und Bürger hinzugekommen. Uns verbindet ein Ziel: Bahnfahren soll attraktiver werden, damit mehr Menschen dieses umweltfreundliche Verkehrsmittel nutzen.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>Wir lassen uns dabei von den positiven Erfahrungen im Schienenpersonennahverkehr leiten, wo abgestimmte Verkehrsangebote zu deutlichen Steigerungen der Fahrgastzahlen geführt haben. Was regional funktioniert, lässt sich auf das ganze Land übertragen.</p><!-- /wp:paragraph -->

<!-- wp:shortcode -->[callout type="cyan"]Unser zentrales Ziel ist die Einführung eines <strong>integralen Taktfahrplans</strong> für den gesamten öffentlichen Verkehr in Deutschland. Der Güterverkehr wird dabei als integrierter Bestandteil des Verkehrsnetzes gesehen.[/callout]<!-- /wp:shortcode -->

<!-- wp:heading {"level":2} --><h2>Was wir fordern</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Als unmittelbar nächsten Schritt fordern wir eine vorbehaltlose und qualifizierte Prüfung über die Machbarkeit eines Deutschlandtaktes durch die Bundesregierung und die Bundesländer.</p><!-- /wp:paragraph -->

<!-- wp:list --><ul>
<li>Ein durchgehender, aufeinander abgestimmter Taktfahrplan für ganz Deutschland.</li>
<li>Kurze Anschlüsse in allen Knotenbahnhöfen — in jede Richtung.</li>
<li>Infrastruktur, die sich am Fahrplan orientiert, nicht umgekehrt.</li>
<li>Der Güterverkehr als gleichberechtigter Teil des Gesamtnetzes.</li>
</ul><!-- /wp:list -->

<!-- wp:paragraph --><p>Zusammengefasst: ein <span class="idt-mark">attraktiver Bahnverkehr für alle</span> — koordiniert auf nationaler Ebene, Schritt für Schritt umgesetzt.</p><!-- /wp:paragraph -->
HTML;
}

function idt_content_revolution() {
	return <<<HTML
<!-- wp:shortcode -->[lead]Der Deutschlandtakt ist mehr als ein Fahrplan — er ist eine Revolution der Planung. Statt von politischen Zielen auszugehen, wird der Fahrplan zur Grundlage aller Entscheidungen.[/lead]<!-- /wp:shortcode -->

<!-- wp:paragraph --><p>Historisch betrachtet basierten Bauprojekte wie die Strecke Hannover–Würzburg oder die Verbindung Köln–Frankfurt auf Zielfahrzeiten für den Personenfernverkehr — nicht jedoch auf fahrplantechnischen Überlegungen für das Gesamtnetz. Der Bundesverkehrswegeplan 2003 orientierte sich an Nachfrageprognosen, ohne konkrete Zugfrequenzen zu definieren.</p><!-- /wp:paragraph -->

<!-- wp:shortcode -->[diagonal]Erst der Fahrplan, dann der Beton: Die Infrastruktur ergibt sich aus dem Takt — nicht der Takt aus der zufällig vorhandenen Infrastruktur.[/diagonal]<!-- /wp:shortcode -->

<!-- wp:paragraph --><p>Die Machbarkeitsstudie von 2015 durchbrach dieses Muster erstmals: Sie stellte ein Linienkonzept mit definierten Zugfrequenzen dar und bewies, dass eine fahrplanbasierte Analyse des Bedarfs an Neubauten möglich ist. Nachfolgende Zielfahrpläne verfeinerten diese Logik, integrierten den Güterverkehr und bestimmten die Infrastruktur auf Basis realistischer Fahrpläne statt umgekehrt.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Vom Ziel zur Schiene</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Mit dem Beschleunigungsgesetz 2023 wurde diese revolutionäre Planungsmethode schließlich rechtlich verankert. Damit ist der Zielfahrplan nicht länger eine Idee, sondern Maßstab für den Ausbau des Netzes.</p><!-- /wp:paragraph -->

<!-- wp:shortcode -->[callout type="violet"]<strong>Kurz gesagt:</strong> Wir fragen zuerst, wann welche Züge fahren sollen — und leiten daraus ab, welche Strecken ausgebaut werden müssen, damit die Anschlüsse passen.[/callout]<!-- /wp:shortcode -->

<!-- wp:heading {"level":2} --><h2>In drei Schritten gedacht</h2><!-- /wp:heading -->
<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="violet" icon="clock" title="Erst der Fahrplan"]Züge fahren jede Stunde – auf wichtigen Strecken halbstündlich – immer zur selben Minute. Leicht zu merken und verlässlich.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="cyan" icon="rail" title="Dann die Infrastruktur"]Aus dem Zielfahrplan wird abgeleitet, was gebaut werden muss. Engpässe werden gezielt aufgelöst statt teurer Einzelprojekte.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="yellow" icon="netz" title="Anschluss im ganzen Land"]In Knotenbahnhöfen treffen sich die Linien und ermöglichen kurze, sichere Umstiege – bis zur entferntesten Regionalbuslinie.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
HTML;
}

function idt_content_klima() {
	return <<<HTML
<!-- wp:shortcode -->[lead]Der Deutschlandtakt trägt erheblich zum Klima- und Umweltschutz bei. Er schafft die Voraussetzungen dafür, weniger energieeffiziente Verkehrsmittel zurückzudrängen.[/lead]<!-- /wp:shortcode -->

<!-- wp:paragraph --><p>Die Bahn hat den Wechsel zu einem lokal emissionsfreien Antrieb weitgehend vollzogen. Der Deutschlandtakt ist zugleich eine Voraussetzung für die Energiewende: Wird Verkehr von der Straße auf die Schiene verlagert, lassen sich erhebliche Energiemengen einsparen.</p><!-- /wp:paragraph -->

<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="100 %" label="mit Ökostrom fahrbar"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="Energie" label="Rohstoffe & Fläche gespart"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="Güter" label="auf die Schiene"]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->

<!-- wp:paragraph --><p>Hochgeschwindigkeitszüge fahren mit Grünstrom genauso klimaneutral wie langsamere Züge. Die Verlagerung des Autoverkehrs auf die Schiene spart Ressourcen — Energie, Rohstoffe und Fläche —, und der Nutzen steigt mit der Geschwindigkeit der Bahn.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>Auch der Neubau von Strecken erzeugt zunächst CO₂, etwa bei der Betonproduktion. Diese Investition rechtfertigt sich jedoch durch die höhere Verlagerung weniger effizienter und ökologisch ungünstigerer Verkehrsmittel — besonders dann, wenn der Fernverkehr von bestehenden auf neue Gleise wechselt.</p><!-- /wp:paragraph -->

<!-- wp:shortcode -->[callout type="yellow"]Im Güterverkehr bleibt die Schiene selbst künftigem emissionsfreiem Straßengüterverkehr im Energieverbrauch überlegen. Der Deutschlandtakt verstärkt diesen Vorteil gezielt.[/callout]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[einschub eyebrow="Wirkung" title="Jeder verlagerte Weg zählt."]Wo Bahnfahren attraktiv ist, steigen die Menschen um. Das spart Energie, Rohstoffe und Fläche — und der Nutzen wächst mit der Geschwindigkeit der Bahn.

[stat number="100 %" label="Fahrt mit Ökostrom möglich"]  [stat number="Faktor 6" label="Energievorteil Schiene ggü. Straße"][/einschub]<!-- /wp:shortcode -->
HTML;
}

function idt_content_etappen() {
	return <<<HTML
<!-- wp:shortcode -->[lead]Der Deutschlandtakt entsteht nicht über Nacht. Ein realistischer Stufenplan führt Schritt für Schritt zum bundesweiten Takt — mit spürbaren Verbesserungen schon in jeder Etappe.[/lead]<!-- /wp:shortcode -->

<!-- wp:paragraph --><p>Jede Ausbaustufe folgt demselben Prinzip: Zuerst wird der Zielfahrplan definiert, dann werden die dafür nötigen Maßnahmen abgeleitet. So entsteht in jeder Etappe ein in sich stimmiges Angebot — und kein Flickwerk.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Der Takt in Stufen</h2><!-- /wp:heading -->
<!-- wp:list --><ul>
<li><strong>Etappe 1 — Knoten zuerst:</strong> Die wichtigsten Knotenbahnhöfe werden auf kurze, symmetrische Anschlüsse ausgerichtet.</li>
<li><strong>Etappe 2 — Linien verdichten:</strong> Auf stark nachgefragten Achsen wird der Takt verdichtet, Engpässe werden gezielt beseitigt.</li>
<li><strong>Etappe 3 — Netz schließen:</strong> Neu- und Ausbaustrecken schließen die letzten Lücken im bundesweiten Taktgefüge.</li>
</ul><!-- /wp:list -->

<!-- wp:paragraph --><p>Der Rhythmus des Taktes <span class="idt-takt" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></span> wird mit jeder Etappe dichter und verlässlicher.</p><!-- /wp:paragraph -->

<!-- wp:shortcode -->[callout type="cyan"]Wichtig: Schon kleine Maßnahmen können große Wirkung entfalten, wenn sie konsequent am Zielfahrplan ausgerichtet sind. Nicht das teuerste Projekt zuerst, sondern das wirksamste.[/callout]<!-- /wp:shortcode -->
HTML;
}

function idt_content_stilelemente() {
	return <<<HTML
<!-- wp:shortcode -->[lead]Diese Seite zeigt die spielerischen Stilelemente des Themes, mit denen sich Texte gestalten lassen. Alle Elemente stehen als Shortcodes zur Verfügung und folgen dem IDT-Design.[/lead]<!-- /wp:shortcode -->

<!-- wp:heading {"level":2} --><h2>Eyebrow &amp; Marker</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[eyebrow]Über die Initiative[/eyebrow]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p>Bahnfahren soll [mark]attraktiver[/mark] werden — mit kurzen [mark color="cyan"]Anschlüssen[/mark] und einem verlässlichen [mark color="violet"]Takt[/mark]. <code>[mark]…[/mark]</code>, <code>[eyebrow]…[/eyebrow]</code></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Takt-Rhythmus</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Der visuelle Takt: [takt count="8"] — <code>[takt count="8"]</code></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Kennzahlen</h2><!-- /wp:heading -->
<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="2008" label="gegründet"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="30 Min." label="Knotentakt"]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[stat number="2023" label="im Gesetz"]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
<!-- wp:paragraph --><p><code>[stat number="2008" label="gegründet"]</code></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Pill-Buttons</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[pill href="#"]Outline-Button[/pill] [pill href="#" style="solid"]Solid-Button[/pill] [pill href="#" style="violet"]Violett-Outline[/pill]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p><code>[pill href="…"]Text[/pill]</code>, <code>[pill style="solid"]…[/pill]</code>, <code>[pill style="violet"]…[/pill]</code> (violette Outline, Gradient-Rand bei Klick)</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Callouts</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[callout type="cyan"]Cyan-Hinweis — <code>[callout type="cyan"]…[/callout]</code>[/callout]<!-- /wp:shortcode -->
<!-- wp:shortcode -->[callout type="violet"]Violetter Hinweis — <code>[callout type="violet"]…[/callout]</code>[/callout]<!-- /wp:shortcode -->
<!-- wp:shortcode -->[callout type="yellow"]Gelber Hinweis — <code>[callout type="yellow"]…[/callout]</code>[/callout]<!-- /wp:shortcode -->

<!-- wp:heading {"level":2} --><h2>Diagonal-Aussageblock</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[diagonal]Erst der Fahrplan, dann der Beton. <code>[diagonal]…[/diagonal]</code>[/diagonal]<!-- /wp:shortcode -->

<!-- wp:heading {"level":2} --><h2>Karte</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[card]<strong>Karte</strong> mit Schatten und Rahmen. <code>[card]…[/card]</code>[/card]<!-- /wp:shortcode -->

<!-- wp:separator --><hr class="wp-block-separator"/><!-- /wp:separator -->
<!-- wp:heading --><h2>Aus der „Example Landing Page"</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Die folgenden Elemente sind aus dem Design-System übernommen und stehen jetzt im Editor bereit — auch als Block-Patterns unter der Kategorie <strong>Deutschlandtakt</strong>.</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Konzept-Karten mit farbiger Oberkante</h2><!-- /wp:heading -->
<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="violet" icon="clock" title="Erst der Fahrplan"]Züge fahren jede Stunde, immer zur selben Minute – leicht zu merken und verlässlich.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="cyan" icon="rail" title="Dann die Infrastruktur"]Aus dem Zielfahrplan wird abgeleitet, was gebaut werden muss.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[concept color="yellow" icon="netz" title="Anschluss im ganzen Land"]In Knotenbahnhöfen treffen sich die Linien für kurze, sichere Umstiege.[/concept]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
<!-- wp:paragraph --><p><code>[concept color="violet" icon="clock" title="…"]Text[/concept]</code> — Farben: violet/cyan/yellow, Icons: clock/rail/netz</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Dunkler Einschub</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[einschub eyebrow="Das Vorbild" title="Die Schweiz fährt seit Jahrzehnten im Takt."]Wo es einen landesweiten Taktfahrplan gibt, nutzen die Menschen die Bahn deutlich häufiger.

[stat number="28 %" label="Wegeanteil ÖV · Schweiz"]  [stat number="19 %" label="Wegeanteil ÖV · Deutschland"][/einschub]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p><code>[einschub eyebrow="…" title="…"]Text[/einschub]</code></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>News-Karten – „Aus der Initiative"</h2><!-- /wp:heading -->
<!-- wp:columns --><div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[newscard tag="Stellungnahme" color="violet" date="12.02.2026" href="#"]Neuberechnung des Deutschlandtakts? Die Initiative widerspricht[/newscard]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[newscard tag="Gesetzgebung" color="cyan" date="24.11.2025" href="#"]Agenda für zufriedene Kunden auf der Schiene[/newscard]<!-- /wp:shortcode --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:shortcode -->[newscard tag="Prognose" color="yellow" date="24.10.2025" href="#"]Verkehrsprognose 2040 bestätigt Wirkung des Deutschlandtakts[/newscard]<!-- /wp:shortcode --></div><!-- /wp:column -->
</div><!-- /wp:columns -->
<!-- wp:paragraph --><p><code>[newscard tag="…" color="violet" date="…" href="…"]Schlagzeile[/newscard]</code></p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Buttons &amp; Tags</h2><!-- /wp:heading -->
<!-- wp:shortcode -->[btn href="#" variant="primary" arrow="true"]Mitglied werden[/btn] [btn href="#" variant="outline"]Mehr erfahren[/btn] [btn href="#" variant="secondary" arrow="true"]Alle Neuigkeiten[/btn]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p>[tag color="violet"]Stellungnahme[/tag] [tag color="cyan"]Gesetzgebung[/tag] [tag color="yellow"]Prognose[/tag]</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><code>[btn variant="primary" arrow="true"]…[/btn]</code> · <code>[tag color="cyan"]…[/tag]</code></p><!-- /wp:paragraph -->
HTML;
}

/**
 * Impressum — Gerüst nach § 5 DDG mit deutlich markierten Platzhaltern.
 * Kein erfundener Rechtstext: die Angaben in [Klammern] muss der Verein
 * vor Veröffentlichung ersetzen.
 */
function idt_content_impressum() {
	return <<<HTML
<!-- wp:shortcode -->[callout type="yellow"]<strong>Platzhalter:</strong> Die Angaben in [Klammern] müssen vor Veröffentlichung durch die verbindlichen Daten des Vereins ersetzt werden.[/callout]<!-- /wp:shortcode -->

<!-- wp:heading {"level":2} --><h2>Angaben gemäß § 5 DDG</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Initiative Deutschlandtakt<br>[Rechtsform, z. B. e. V. / GbR]<br>[Straße Hausnummer]<br>[PLZ Ort]</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Vertreten durch</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>[Vorname Name, Funktion]</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Kontakt</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>E-Mail: <a href="mailto:mail@initiative-deutschlandtakt.de">mail@initiative-deutschlandtakt.de</a><br>[Telefon, optional]</p><!-- /wp:paragraph -->

<!-- wp:heading {"level":2} --><h2>Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>[Vorname Name, Anschrift wie oben]</p><!-- /wp:paragraph -->
HTML;
}

/**
 * Datenschutzerklärung — startet mit der offiziellen WordPress-Vorlage
 * (lokalisiert, Block-Markup), plus Platzhalter-Hinweis. Die Seite wird
 * zusätzlich als wp_page_for_privacy_policy registriert, sodass die
 * WordPress-eigenen Datenschutz-Werkzeuge greifen.
 */
function idt_content_datenschutz() {
	$hinweis = '<!-- wp:shortcode -->[callout type="yellow"]<strong>Platzhalter:</strong> Dies ist die WordPress-Standardvorlage. Sie muss vor Veröffentlichung an die tatsächliche Datenverarbeitung der Website angepasst werden (Hosting, Kontaktwege, eingebundene Dienste).[/callout]<!-- /wp:shortcode -->' . "\n\n";

	if ( ! class_exists( 'WP_Privacy_Policy_Content' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-privacy-policy-content.php';
	}
	return $hinweis . WP_Privacy_Policy_Content::get_default_content( false, true );
}

/** Demo-Beiträge für „Aktuelles“. */
function idt_demo_posts() {
	return array(
		'beschleunigungsgesetz-verankert-den-takt' => array(
			'title'   => 'Beschleunigungsgesetz verankert den Takt im Recht',
			'tags'    => array( 'Gesetzgebung', 'Infrastruktur' ),
			'excerpt' => 'Mit dem Beschleunigungsgesetz 2023 wurde die fahrplanbasierte Planung erstmals rechtlich verankert — ein Meilenstein für den Deutschlandtakt.',
			'content' => "<!-- wp:paragraph --><p>Lange war der Zielfahrplan vor allem eine gute Idee. Mit dem Beschleunigungsgesetz 2023 ist daraus ein rechtlicher Maßstab geworden: Der Ausbau des Netzes orientiert sich nun am Fahrplan — nicht umgekehrt.</p><!-- /wp:paragraph -->\n\n<!-- wp:paragraph --><p>Für die Initiative ist das ein wichtiger Etappensieg. Jahrzehntelang wurden Strecken nach Zielfahrzeiten oder Nachfrageprognosen geplant, ohne das Gesamtnetz im Blick zu haben. Die Machbarkeitsstudie von 2015 hatte gezeigt, dass es auch anders geht.</p><!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->[callout type=\"cyan\"]Was bedeutet das konkret? Künftig wird zuerst gefragt, wann welche Züge fahren sollen — und daraus abgeleitet, welche Infrastruktur dafür nötig ist.[/callout]<!-- /wp:shortcode -->",
		),
		'warum-knoten-das-herz-des-taktes-sind' => array(
			'title'   => 'Warum Knotenbahnhöfe das Herz des Taktes sind',
			'tags'    => array( 'Infrastruktur', 'Fahrplan' ),
			'excerpt' => 'In den Knotenbahnhöfen entscheidet sich, ob der Deutschlandtakt funktioniert: Hier müssen die Anschlüsse in alle Richtungen kurz sein.',
			'content' => "<!-- wp:paragraph --><p>Ein integraler Taktfahrplan lebt von seinen Knoten. Treffen sich dort die Züge zur vollen oder halben Stunde, sind kurze Anschlüsse in alle Richtungen möglich — egal, woher man kommt und wohin man will.</p><!-- /wp:paragraph -->\n\n<!-- wp:paragraph --><p>Das klingt einfach, stellt die Infrastruktur aber vor klare Anforderungen: Die Fahrzeiten zwischen den Knoten müssen passen. Genau hier setzt die Planung an — sie leitet aus dem gewünschten Takt ab, wo ausgebaut werden muss.</p><!-- /wp:paragraph -->\n\n<!-- wp:paragraph --><p>Der Rhythmus [takt count=\"6\"] entsteht so im ganzen Land.</p><!-- /wp:paragraph -->",
		),
		'mehr-verkehr-auf-die-schiene-und-das-klima' => array(
			'title'   => 'Mehr Verkehr auf die Schiene — gut fürs Klima',
			'tags'    => array( 'Klimaschutz', 'Fahrplan' ),
			'excerpt' => 'Wird Verkehr von der Straße auf die Schiene verlagert, sinkt der Energieverbrauch erheblich. Der Deutschlandtakt schafft die Voraussetzungen dafür.',
			'content' => "<!-- wp:paragraph --><p>Die Bahn fährt schon heute weitgehend lokal emissionsfrei. Mit Grünstrom fahren auch schnelle Züge klimaneutral. Entscheidend ist die Verlagerung: Jeder Pkw-Kilometer, der zur Schiene wechselt, spart Energie, Rohstoffe und Fläche.</p><!-- /wp:paragraph -->\n\n<!-- wp:paragraph --><p>Der Deutschlandtakt macht das Bahnfahren so attraktiv, dass diese Verlagerung im großen Maßstab möglich wird. Damit ist er zugleich eine Voraussetzung für die Energiewende.</p><!-- /wp:paragraph -->\n\n<!-- wp:shortcode -->[diagonal]Mehr Verkehr auf die Schiene heißt: weniger Energie, weniger Rohstoffe, weniger Fläche.[/diagonal]<!-- /wp:shortcode -->",
		),
	);
}
