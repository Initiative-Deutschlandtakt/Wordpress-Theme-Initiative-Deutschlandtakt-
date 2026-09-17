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
   keinen Site-Header, Seiten mit der Vorlage „Verlaufsseite" ebenso wenig
   (dort fällt zusätzlich der Footer weg, s. footer.php). Geprüft wird der
   Template-Slug (nicht das gerade aktive Template-File), damit es auch für
   die statische Startseite greift — s. idt_page_template_is(). */
$idt_no_nav = idt_page_template_is( 'page-no-nav.php' ) || idt_is_verlauf_page();
/* Logo ausblendbar, Menü bleibt: Vorlage „Menü ohne Logo" — für die
   Startseite, deren Splash-Bühne das Logo bereits selbst zeigt. Gleiches
   Prinzip wie bei $idt_no_nav (Template-Slug statt aktivem Template-File). */
$idt_no_logo = idt_page_template_is( 'page-no-logo.php' );
/* Menüform aus dem Customizer: 'band' (Vorgabe) oder 'overlay' — s.
   idt_nav_style() in functions.php. Das Markup ist bis auf die Inverse-
   Fassung des Logos identisch; unterschieden wird über die Body-Klasse
   .idt-nav-overlay (style.css 5c). */
$idt_nav_overlay = 'overlay' === idt_nav_style();
if ( ! $idt_no_nav ) : ?>
<header class="site-header<?php echo $idt_no_logo ? ' site-header--no-logo' : ''; ?>">
	<div class="container site-header__inner">
		<?php if ( $idt_no_logo ) : /* Logo bewusst ausgeblendet, siehe oben. */ ?>
		<?php elseif ( has_custom_logo() ) : /* Logo aus „Website-Identität", falls gesetzt … */ ?>
			<div class="site-header__brand"><?php the_custom_logo(); ?></div>
		<?php else : /* … sonst das mitgelieferte Marken-PNG. */ ?>
			<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo-idt-transparent.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
				<?php if ( $idt_nav_overlay ) : /* Beim aufklappbaren Menü kippt das Menüband auf
				         Markentinte, sobald das Menü offen ist — die dunkle Schrift der Wortmarke
				         verschwände darauf. Deshalb liegt die Inverse-Fassung (dieselbe, die der
				         Footer nutzt) darüber und wird eingeblendet. Rein dekorativ, daher ohne
				         Alternativtext: Die Marke steht schon im alt des Bildes darunter. */ ?>
					<img class="site-header__logo-inverse" src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo-idt-inverse.png' ); ?>" alt="" aria-hidden="true">
				<?php endif; ?>
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
			<?php if ( $idt_nav_overlay ) : /* Beim aufklappbaren Menü steht die Suche im
			         Menü statt im Kopf — dort bleiben nur die drei Striche. Der Link trägt
			         dieselbe Klasse wie die Lupe im Menüband und öffnet dasselbe Overlay
			         (assets/search.js nimmt alles mit .idt-searchtoggle). Bewusst ein
			         Geschwister der Menüliste und kein <li> darin: Ohne zugewiesenes
			         WP-Menü gibt wp_nav_menu() gar nichts aus (fallback_cb => false) —
			         als Listenpunkt verschwände die Suche mit. */ ?>
				<a class="idt-searchtoggle idt-navsearch" href="#idt-searchbox" aria-expanded="false" aria-controls="idt-searchbox">
					<span class="idt-navsearch__icon" aria-hidden="true"><?php echo idt_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="idt-navsearch__label"><?php esc_html_e( 'Suche', 'idt' ); ?></span>
				</a>
			<?php endif; ?>
		</nav>

		<?php /* Rechte Aktionsleiste. Im Menüband stehen hier die Lupe (das Suchfeld
		         selbst öffnet sich als Overlay über der ganzen Seite, s.
		         idt_render_search_overlay()) und daneben der Hamburger für schmale
		         Viewports. Beim aufklappbaren Menü bleiben nur die drei Striche übrig:
		         Die Suche ist dort in das Menü gewandert (s. oben), und ein Kopf, der
		         ohnehin keinen Balken mehr trägt, soll auch nicht zwei Schaltflächen
		         über der Seite schweben lassen. */ ?>
		<div class="site-header__actions">
			<?php /* Bewusst ein Link statt eines Buttons: Ohne JavaScript klappt das
			         Overlay per :target-Regel auf (siehe style.css), das Formular
			         darin führt ganz normal zur Ergebnisseite. Mit JavaScript
			         fängt search.js den Klick ab und blendet es ein.

			         Steht auch beim aufklappbaren Menü im Markup, wird dort aber
			         ausgeblendet, sobald JavaScript läuft (style.css 5c) — dann
			         übernimmt der Suchpunkt im Menü. Ohne JavaScript bliebe der
			         sonst hinter einem Menü liegen, das sich nicht öffnen lässt,
			         und die Suche wäre auf dem Telefon gar nicht mehr zu
			         erreichen. */ ?>
			<a class="idt-searchtoggle" href="#idt-searchbox" aria-expanded="false" aria-controls="idt-searchbox" aria-label="<?php esc_attr_e( 'Suche öffnen', 'idt' ); ?>">
				<?php echo idt_icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<?php /* Nur das Symbol, keine Beschriftung: Die Bedeutung trägt das
			         aria-label. Beim aufklappbaren Menü liegen die Striche ohne Rahmen
			         frei über der Seite und bekommen erst beim Scrollen eine
			         Papierfläche untergelegt (style.css 5c). */ ?>
			<button class="nav-toggle" aria-expanded="false" aria-controls="main-nav" aria-label="<?php esc_attr_e( 'Menü öffnen', 'idt' ); ?>">
				<span class="nav-toggle__icon" aria-hidden="true">
					<span class="nav-toggle__bar"></span>
					<span class="nav-toggle__bar"></span>
					<span class="nav-toggle__bar"></span>
				</span>
			</button>
		</div>
	</div>
</header>
<?php idt_render_search_overlay(); ?>
<?php endif; ?>

<main id="content">
