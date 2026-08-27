<?php
/**
 * Template Name: Menü ohne Logo
 * Template Post Type: page
 *
 * Seite mit normalem Site-Header/Menüband, aber ohne das Marken-Logo links
 * oben — für die Startseite, die ihr eigenes Logo bereits in der Bühne
 * (Splash) zeigt und es im Header nicht doppelt braucht.
 *
 * Das Ausblenden des Logos passiert in header.php über den Template-Slug —
 * dadurch greift es auch für die statische Startseite, die von
 * front-page.php gerendert wird (siehe page-no-nav.php für das analoge
 * Vorgehen beim kompletten Ausblenden des Menübands).
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
