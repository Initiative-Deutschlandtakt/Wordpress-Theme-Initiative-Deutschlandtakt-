<?php
/**
 * Template Name: Verlaufsseite (ohne Kopf und Fuß)
 * Template Post Type: page
 *
 * Nackte Seite auf dem Markenverlauf Violett → Cyan: kein Menüband, kein
 * Footer, keine Seitenüberschrift — nur der Inhalt der Seite, mittig auf der
 * Verlaufsfläche. Gedacht für Linkseiten („Link in Bio"), Kampagnen- und
 * QR-Code-Ziele, auf denen Logo, ein paar beige Buttons und die Social-Leiste
 * stehen (Vorlage „Verlaufsseite" im Inserter unter Vorlagen).
 *
 * Ausgeblendet wird beides wie bei den übrigen Sondervorlagen über den
 * Template-Slug (idt_page_template_is() in functions.php): das Menüband in
 * header.php, der Footer in footer.php. Die Verlaufsfläche selbst hängt an
 * der Body-Klasse „idt-verlauf" (style.css 6c).
 *
 * @package idt
 */

get_header();
while ( have_posts() ) : the_post(); ?>
	<div class="idt-verlauf__inner">
		<div class="entry entry--verlauf">
			<?php the_content(); ?>
		</div>
	</div>
<?php endwhile;
get_footer();
