<?php
/**
 * Einzelner Beitrag (Aktuelles).
 *
 * @package idt
 */

get_header();
while ( have_posts() ) : the_post(); ?>
	<article class="container">
		<header class="page-hero">
			<div class="post-meta">
				<?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( idt_reading_time( get_the_content() ) ); ?>
			</div>
			<h1><?php the_title(); ?></h1>
		</header>
		<div class="entry idt-dropcap">
			<?php the_content(); ?>
		</div>
		<p style="margin-top:var(--space-7)"><a class="pill" href="<?php echo esc_url( idt_page_url( 'aktuelles', home_url( '/' ) ) ); ?>">← Alle Beiträge</a></p>
	</article>
<?php endwhile;
get_footer();
