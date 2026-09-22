<?php
/**
 * Fußbereich + Site-Footer.
 *
 * @package idt
 */
?>
</main><!-- #content -->

<?php
/* Footer ausblendbar: Seiten mit der Vorlage „Verlaufsseite" stehen ohne
   Gerüst da — kein Menüband (s. header.php), kein Footer. Die Seite selbst
   bestimmt, was auf der Verlaufsfläche steht. */
if ( ! idt_is_verlauf_page() ) : ?>
<footer class="site-footer">
	<div class="fwrap">
		<div class="frow">
			<div class="fbrand">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo-idt-inverse.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
				<p><?php echo esc_html( get_theme_mod( 'idt_footer_slogan', idt_footer_slogan_default() ) ); ?></p>
				<?php
				/* Social-Media-Menü (Design → Menüs, Standort „Social-Media-Menü
				   (Footer)"): eine Reihe Icon-Kacheln unter dem Slogan. Ohne
				   zugewiesenes Menü gibt die Funktion nichts aus. */
				echo idt_render_social_menu(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
			<div class="fcol">
				<h4>Themen</h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '<ul>%3$s</ul>',
						'depth'          => 1,
					) );
				}
				?>
			</div>
			<div class="fcol">
				<h4>Mitmachen</h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer-mitmachen',
					'container'      => false,
					'items_wrap'     => '<ul>%3$s</ul>',
					'depth'          => 1,
					'fallback_cb'    => 'idt_footer_mitmachen_fallback',
				) );
				?>
			</div>
			<?php /* Optionale, im Backend pflegbare Zusatzspalten (Widgets). */
			if ( is_active_sidebar( 'footer' ) ) {
				dynamic_sidebar( 'footer' );
			} ?>
		</div>
		<div class="fbar">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Initiative Deutschlandtakt · Verein</span>
			<span>
				<a href="<?php echo esc_url( idt_page_url( 'impressum', 'https://initiative-deutschlandtakt.de/impressum/' ) ); ?>">Impressum</a>
				<a href="<?php echo esc_url( idt_page_url( 'datenschutz', 'https://initiative-deutschlandtakt.de/datenschutz/' ) ); ?>">Datenschutz</a>
			</span>
		</div>
	</div>
</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
