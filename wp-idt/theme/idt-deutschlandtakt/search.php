<?php
/**
 * Ergebnisseite der Suche.
 *
 * Zeigt dieselben Treffer-Karten wie die Live-Vorschau im Such-Overlay
 * (gemeinsame Bausteine in inc/search.php), dazu das große Suchfeld zum
 * Nachschärfen und die Trefferzahl.
 *
 * @package idt
 */

get_header();

$idt_q     = get_search_query();
$idt_total = (int) $GLOBALS['wp_query']->found_posts;
?>
<div class="container">
	<header class="page-hero page-hero--search">
		<span class="idt-eyebrow"><?php esc_html_e( 'Suche', 'idt' ); ?></span>
		<?php if ( '' !== $idt_q ) : ?>
			<h1><?php printf( 'Ergebnisse für „%s“', esc_html( $idt_q ) ); ?></h1>
		<?php else : ?>
			<h1><?php esc_html_e( 'Suche', 'idt' ); ?></h1>
		<?php endif; ?>

		<div class="page-hero__search"><?php idt_search_form( 'idt-search-page-field', $idt_q ); ?></div>

		<?php if ( '' !== $idt_q ) : ?>
			<p class="idt-search-count">
				<?php
				printf(
					/* translators: %s = Anzahl der Treffer */
					esc_html( _n( '%s Treffer', '%s Treffer', $idt_total, 'idt' ) ),
					esc_html( number_format_i18n( $idt_total ) )
				);
				?>
			</p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php
		/* Treffer der aktuellen Seite einsammeln und mit demselben Baustein
		   rendern, den auch die Live-Vorschau im Overlay nutzt. */
		echo idt_search_results_list( $GLOBALS['wp_query']->posts, $idt_q ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<div class="idt-search-pagination"><?php the_posts_pagination(); ?></div>
	<?php elseif ( '' !== $idt_q ) : ?>
		<?php echo idt_search_empty_notice( $idt_q ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<p class="idt-search-fallback">
			<a class="pill pill--violet" href="<?php echo esc_url( idt_blog_url() ); ?>"><?php esc_html_e( 'Alle Beiträge durchstöbern', 'idt' ); ?></a>
		</p>
	<?php else : ?>
		<p class="idt-search-empty"><?php esc_html_e( 'Bitte einen Suchbegriff eingeben.', 'idt' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer();
