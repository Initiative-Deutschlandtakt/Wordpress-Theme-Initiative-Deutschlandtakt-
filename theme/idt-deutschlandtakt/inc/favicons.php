<?php
/**
 * Favicon-Set des Design Systems.
 *
 * Das Set (favicon.ico, 16, 32, apple-touch-icon, 192, 512, Manifest) kommt
 * unverändert aus dem Design System; die Marke wird nie neu gezeichnet. Wer
 * unter „Design → Customizer → Website-Identität" ein eigenes Website-Icon
 * wählt, behält es: Dann gibt WordPress seine eigenen Tags aus, und dieser
 * Hook bleibt still.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Links auf das mitgelieferte Favicon-Set, solange kein Website-Icon gesetzt ist. */
function idt_favicons() {
	if ( has_site_icon() ) {
		return;
	}
	$base = get_template_directory_uri() . '/assets/favicons/';
	echo '<link rel="icon" href="' . esc_url( $base . 'favicon.ico' ) . '" sizes="32x32">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url( $base . 'favicon-16x16.png' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $base . 'favicon-32x32.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $base . 'apple-touch-icon.png' ) . '">' . "\n";
	echo '<link rel="manifest" href="' . esc_url( $base . 'site.webmanifest' ) . '">' . "\n";
	echo '<meta name="theme-color" content="#00373C">' . "\n";
}
add_action( 'wp_head', 'idt_favicons', 6 );
add_action( 'login_head', 'idt_favicons' );
