<?php
/**
 * Template Name: Logo mittig
 * Template Post Type: page
 *
 * Seite mit vollständigem Kopf, aber mittiger Marke: Das Logo steht zentriert
 * über dem Menü statt links daneben — für Seiten, die wie ein Plakat
 * auftreten (Kampagne, Veranstaltung, Einladung), wo die Mitte trägt.
 *
 * Bis auf die Stellung des Logos ändert die Vorlage nichts: Überschrift und
 * Inhalt stehen wie auf jeder anderen Seite (page.php). Wer zusätzlich das
 * Menüband loswerden will, nimmt „Ohne Menüband"; wer das Logo ganz weghaben
 * will, „Menü ohne Logo".
 *
 * Die Stellung selbst passiert über den Template-Slug (idt_logo_pos() in
 * functions.php) und die daraus gesetzte Body-Klasse .idt-logo-mitte
 * (style.css 5d) — dadurch greift sie auch für die statische Startseite, die
 * von front-page.php gerendert wird, und schlägt dort wie überall die
 * Customizer-Einstellung „Stellung des Logos im Kopf".
 *
 * @package idt
 */

get_header();
while ( have_posts() ) : the_post(); ?>
	<article class="container">
		<header class="page-hero">
			<h1><?php the_title(); ?></h1>
		</header>
		<div class="entry">
			<?php the_content(); ?>
		</div>
	</article>
<?php endwhile;
get_footer();
