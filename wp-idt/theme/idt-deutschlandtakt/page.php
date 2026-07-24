<?php
/**
 * Einzelne Seite.
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
