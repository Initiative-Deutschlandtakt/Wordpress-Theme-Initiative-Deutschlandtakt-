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
		<?php else : ?>
			<span class="idt-eyebrow">Aktuelles</span>
			<h1><?php is_home() ? esc_html_e( 'Aus der Initiative', 'idt' ) : the_archive_title(); ?></h1>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<ul class="post-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<li class="post-list__item">
					<div class="post-meta"><?php echo esc_html( get_the_date() ); ?></div>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p style="color:var(--text-muted)"><?php echo esc_html( get_the_excerpt() ); ?></p>
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
