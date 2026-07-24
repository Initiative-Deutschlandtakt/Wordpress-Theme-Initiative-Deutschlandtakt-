<?php
/**
 * Fallback-/Blog-Index (Aktuelles-Archiv).
 *
 * @package idt
 */

get_header(); ?>
<div class="container">
	<header class="page-hero">
		<?php if ( is_search() ) : /* Suchergebnisse: eigener Eyebrow + Suchbegriff in der Überschrift */ ?>
			<span class="idt-eyebrow">Suche</span>
			<h1>Suchergebnisse für „<?php echo esc_html( get_search_query() ); ?>“</h1>
		<?php elseif ( is_tag() ) : /* Schlagwort-Archiv: gefilterte Beitragsansicht */ ?>
			<span class="idt-eyebrow">Schlagwort</span>
			<h1><?php echo esc_html( single_tag_title( '', false ) ); ?></h1>
		<?php else : ?>
			<span class="idt-eyebrow">Aktuelles</span>
			<h1><?php is_home() ? esc_html_e( 'Aus der Initiative', 'idt' ) : the_archive_title(); ?></h1>
		<?php endif; ?>
	</header>

	<?php
	/* Filterleiste (nur auf der Beitragsübersicht und den Schlagwort-Archiven):
	 * verlinkte Schlagwort-Chips, das aktive Schlagwort ist hervorgehoben. */
	if ( is_home() || is_tag() ) {
		echo idt_render_tag_filter(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<li class="post-list__item">
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="post-list__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					<?php endif; ?>
					<div class="post-meta"><?php echo esc_html( get_the_date() ); ?></div>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p style="color:var(--text-muted)"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php $post_tags = idt_post_tags_html( get_the_ID(), 0, true ); ?>
					<?php if ( $post_tags ) : ?>
						<div class="post-list__tags"><?php echo $post_tags; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>">Weiterlesen →</a>
				</li>
			<?php endwhile; ?>
		</ul>
		<div style="margin-top:var(--space-7)"><?php the_posts_pagination(); ?></div>
	<?php else : ?>
		<p>Keine Beiträge vorhanden.</p>
	<?php endif; ?>
</div>
<?php get_footer();
