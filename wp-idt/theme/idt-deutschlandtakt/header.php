<?php
/**
 * Kopfbereich + Site-Header.
 *
 * @package idt
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content"><?php esc_html_e( 'Zum Inhalt springen', 'idt' ); ?></a>

<?php
/* Menüband ausblendbar: Seiten mit der Vorlage „Ohne Menüband" bekommen
   keinen Site-Header. Geprüft wird der Template-Slug (nicht das gerade
   aktive Template-File), damit es auch für die statische Startseite
   greift — dort rendert front-page.php, die Vorlagen-Wahl bleibt aber
   in der Seiten-Einstellung sichtbar. */
$idt_no_nav = is_singular( 'page' ) && 'page-no-nav.php' === get_page_template_slug();
if ( ! $idt_no_nav ) : ?>
<header class="site-header">
	<div class="container site-header__inner">
		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo-idt-transparent.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
		</a>
		<button class="nav-toggle" aria-expanded="false" aria-controls="main-nav" aria-label="<?php esc_attr_e( 'Menü öffnen', 'idt' ); ?>">
			<span class="nav-toggle__bar"></span>
			<span class="nav-toggle__bar"></span>
			<span class="nav-toggle__bar"></span>
		</button>
		<nav class="main-nav" id="main-nav" aria-label="<?php esc_attr_e( 'Hauptmenü', 'idt' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
				'depth'          => 1,
			) );
			?>
		</nav>
	</div>
</header>
<?php endif; ?>

<main id="content">
