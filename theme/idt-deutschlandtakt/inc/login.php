<?php
/**
 * Anmeldeseite (wp-login.php) im IDT-Design.
 *
 * Angemeldet wird über Authentik: Das Plugin „OpenID Connect Generic Client"
 * setzt über den Filter `login_message` (Priorität 99) eine Karte
 * `.openid-connect-login-button` mit dem Anmeldeknopf über das Formular.
 * Darunter bleibt das WordPress-Formular `#loginform` stehen — für die wenigen
 * lokalen Ausnahmekonten. Diese Datei regelt nur die Darstellung; Rollen und
 * Zugriff liegen in Must-Use-Plugins und gehören nicht ins Theme.
 *
 * Die Anmeldeseite lädt style.css nicht (das würde die Basis-Styles des
 * Frontends über das WordPress-Formular legen). Damit assets/login.css
 * trotzdem mit den Design-Tokens und der lokalen Inter arbeitet, werden
 * Abschnitt 1 (Web-Fonts) und 2 (Tokens) aus style.css herausgelesen und
 * inline vorangestellt — eine Quelle, keine Kopie der Werte.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Beschriftung des Authentik-Knopfs. */
function idt_login_button_text() {
	return __( 'Mit IDT-Konto anmelden', 'idt' );
}
add_filter( 'openid-connect-generic-login-button-text', 'idt_login_button_text' );

/* Das Logo über dem Formular führt zur Website, nicht zu wordpress.org. */
function idt_login_headerurl() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'idt_login_headerurl' );

function idt_login_headertext() {
	return get_bloginfo( 'name', 'display' );
}
add_filter( 'login_headertext', 'idt_login_headertext' );

/* Trenner zwischen Authentik-Karte und lokalem Formular. Nur wenn die Karte
 * des Plugins wirklich in der Meldung steckt — ohne Plugin (oder auf
 * „Passwort vergessen") gäbe es kein „oder". Priorität nach dem Plugin (99). */
function idt_login_trenner( $message ) {
	if ( false === strpos( (string) $message, 'openid-connect-login-button' ) ) {
		return $message;
	}
	return $message . '<p class="idt-login-trenner"><span>' . esc_html__( 'oder mit lokalem Konto', 'idt' ) . '</span></p>';
}
add_filter( 'login_message', 'idt_login_trenner', 100 );

/**
 * Abschnitt 1 und 2 aus style.css: die @font-face-Regeln und der :root-Block.
 * Die Schriftpfade sind in style.css relativ zum Theme-Ordner und werden auf
 * absolute Adressen umgeschrieben, weil die Regeln inline auf wp-login.php
 * landen.
 */
function idt_login_tokens_css() {
	$css = file_get_contents( get_template_directory() . '/style.css' );
	if ( false === $css ) {
		return '';
	}
	$out = '';
	if ( preg_match_all( '/@font-face\s*\{[^}]*\}/', $css, $faces ) ) {
		$out .= implode( "\n", $faces[0] ) . "\n";
	}
	/* Der erste :root-Block ist Abschnitt 2; er endet mit der ersten Zeile,
	   die nur aus „}" besteht. */
	if ( preg_match( '/^:root\s*\{.*?^\}/ms', $css, $root ) ) {
		$out .= $root[0] . "\n";
	}
	return str_replace(
		array( "url('assets/", 'url("assets/', 'url(assets/' ),
		array(
			"url('" . get_template_directory_uri() . '/assets/',
			'url("' . get_template_directory_uri() . '/assets/',
			'url(' . get_template_directory_uri() . '/assets/',
		),
		$out
	);
}

/* Logo aus „Website-Identität" als Hintergrund von #login h1 a. Ohne Logo
 * bleibt der Website-Name als Text stehen (assets/login.css). */
function idt_login_logo_css() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
	if ( ! $logo ) {
		return '';
	}
	list( $url, $width, $height ) = $logo;
	/* SVG-Logos melden oft keine Maße — dann ein Querformat annehmen. */
	$ratio = ( $width && $height ) ? $width / $height : 3;
	return sprintf(
		'#login h1 a { background-image: url("%s"); aspect-ratio: %s; height: auto; text-indent: -9999px; overflow: hidden; }',
		esc_url( $url ),
		round( $ratio, 4 )
	);
}

function idt_login_assets() {
	wp_enqueue_style( 'idt-login', get_template_directory_uri() . '/assets/login.css', array( 'login' ), IDT_VERSION );
	wp_add_inline_style( 'login', idt_login_tokens_css() );
	/* Ohne Logo bleibt es beim Website-Namen als Text (assets/login.css). */
	$logo_css = idt_login_logo_css();
	if ( '' !== $logo_css ) {
		wp_add_inline_style( 'idt-login', $logo_css );
	}
}
add_action( 'login_enqueue_scripts', 'idt_login_assets' );
