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
/* Logo ausblendbar, Menü bleibt: Vorlage „Menü ohne Logo" — für die
   Startseite, deren Splash-Bühne das Logo bereits selbst zeigt. Gleiches
   Prinzip wie bei $idt_no_nav (Template-Slug statt aktivem Template-File). */
$idt_no_logo = is_singular( 'page' ) && 'page-no-logo.php' === get_page_template_slug();
if ( ! $idt_no_nav ) : ?>
<header class="site-header<?php echo $idt_no_logo ? ' site-header--no-logo' : ''; ?>">
	<div class="container site-header__inner">
		<?php if ( $idt_no_logo ) : /* Logo bewusst ausgeblendet, siehe oben. */ ?>
		<?php elseif ( has_custom_logo() ) : /* Logo aus „Website-Identität", falls gesetzt … */ ?>
			<div class="site-header__brand"><?php the_custom_logo(); ?></div>
		<?php else : /* … sonst das mitgelieferte Marken-PNG. */ ?>
			<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo-idt-transparent.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
			</a>
		<?php endif; ?>
		<nav class="main-nav" id="main-nav" aria-label="<?php esc_attr_e( 'Hauptmenü', 'idt' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
				/* depth 2 = Hauptpunkte plus eine Ebene Dropdown-Untermenüs.
				   Ohne Untermenü-Punkte bleibt das Menü unverändert einzeilig;
				   sobald im WP-Menü einem Punkt Unterpunkte zugeordnet werden,
				   erscheinen sie als Dropdown (Desktop) bzw. eingerückt (Mobil). */
				'depth'          => 2,
			) );
			?>
		</nav>

		<?php /* Rechte Aktionsleiste: die Suche ist auf eine reine Lupe
		         reduziert (das Feld selbst öffnet sich als Overlay über der
		         ganzen Seite, s. idt_render_search_overlay()), daneben der
		         Hamburger für das Menüband auf schmalen Viewports. */ ?>
		<div class="site-header__actions">
			<?php /* Bewusst ein Link statt eines Buttons: Ohne JavaScript klappt das
			         Overlay per :target-Regel auf (siehe style.css), das Formular
			         darin führt ganz normal zur Ergebnisseite. Mit JavaScript
			         fängt search.js den Klick ab und blendet es ein. */ ?>
			<a class="idt-searchtoggle" href="#idt-searchbox" aria-expanded="false" aria-controls="idt-searchbox" aria-label="<?php esc_attr_e( 'Suche öffnen', 'idt' ); ?>">
				<?php echo idt_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<button class="nav-toggle" aria-expanded="false" aria-controls="main-nav" aria-label="<?php esc_attr_e( 'Menü öffnen', 'idt' ); ?>">
				<span class="nav-toggle__bar"></span>
				<span class="nav-toggle__bar"></span>
				<span class="nav-toggle__bar"></span>
			</button>
		</div>
	</div>
</header>
<?php idt_render_search_overlay(); ?>
<?php endif; ?>

<main id="content">
