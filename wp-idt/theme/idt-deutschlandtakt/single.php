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
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="post-hero-img"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>
		<div class="entry idt-dropcap">
			<?php the_content(); ?>
		</div>
		<?php $post_tags = idt_post_tags_html( get_the_ID(), 0, true ); ?>
		<?php if ( $post_tags ) : ?>
			<footer class="post-tags" aria-label="<?php esc_attr_e( 'Schlagwörter', 'idt' ); ?>"><?php echo $post_tags; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></footer>
		<?php endif; ?>
		<?php if ( get_previous_post() || get_next_post() ) : ?>
		<nav class="post-nav" aria-label="<?php esc_attr_e( 'Weitere Beiträge', 'idt' ); ?>">
			<?php previous_post_link( '<span class="post-nav__prev">%link</span>', '&larr; %title' ); ?>
			<?php next_post_link( '<span class="post-nav__next">%link</span>', '%title &rarr;' ); ?>
		</nav>
		<?php endif; ?>
		<p style="margin-top:var(--space-7)"><a class="pill" href="<?php echo esc_url( idt_blog_url() ); ?>">← Alle Beiträge</a></p>
	</article>
<?php endwhile;
get_footer();
