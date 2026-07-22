<?php
/**
 * Fehlerseite (404 — Seite nicht gefunden).
 *
 * @package idt
 */

get_header(); ?>
<div class="container">
	<header class="page-hero">
		<span class="idt-eyebrow">Fehler 404</span>
		<h1>Seite nicht gefunden</h1>
	</header>

	<p>Die angeforderte Seite existiert nicht oder wurde verschoben.</p>

	<p style="margin-top:var(--space-7)"><a class="pill" href="<?php echo esc_url( home_url( '/' ) ); ?>">Zur Startseite</a></p>
</div>
<?php get_footer();
