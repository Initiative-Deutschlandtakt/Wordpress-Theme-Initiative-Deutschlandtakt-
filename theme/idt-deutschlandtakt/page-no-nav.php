<?php
/**
 * Template Name: Ohne Menüband
 * Template Post Type: page
 *
 * Seite ohne Site-Header (Menüband) — für Landing-/Splash-Seiten, die ohne
 * Navigation auskommen sollen. Bewusst auch ohne page-hero: der Inhalt
 * bestimmt die Bühne komplett selbst (z. B. [splash] als erster Block).
 *
 * Das Ausblenden selbst passiert in header.php über den Template-Slug —
 * dadurch greift es auch für die statische Startseite, die von
 * front-page.php gerendert wird.
 *
 * @package idt
 */

get_header();
while ( have_posts() ) : the_post(); ?>
	<div class="container">
		<div class="entry entry--home">
			<?php the_content(); ?>
		</div>
	</div>
<?php endwhile;
get_footer();
