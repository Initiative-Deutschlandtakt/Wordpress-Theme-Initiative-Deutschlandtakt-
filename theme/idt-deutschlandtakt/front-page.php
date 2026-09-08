<?php
/**
 * Front-Page: rendert den Editor-Inhalt der „Startseite"-Seite.
 *
 * Das Homepage-Design wird als Block-/Shortcode-Komposition in der Seite selbst
 * gepflegt (siehe idt_content_startseite() in inc/demo-content.php) und ist
 * dadurch vollständig im Block-Editor bearbeitbar — kein fest verdrahtetes
 * Markup mehr.
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
