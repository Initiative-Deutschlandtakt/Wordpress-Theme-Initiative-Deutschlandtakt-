<?php
/**
 * Knotendreieck — Ausgabe im Frontend.
 *
 * Nur die Brücke zwischen Block und Render-Funktion: das Markup baut
 * idt_render_knotendreieck() in inc/shortcodes.php — dieselbe Funktion, die
 * auch den Shortcode [knotendreieck] bedient (CLAUDE.md, Konvention 2).
 * Gezeichnet wird die Grafik im Browser von view.js.
 *
 * @var array    $attributes Blockattribute, siehe block.json.
 * @var string   $content    Innerer Inhalt — der Block hat keinen.
 * @var WP_Block $block      Blockinstanz.
 *
 * @package idt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* get_block_wrapper_attributes() bringt Anker und Abstände aus der
   Seitenleiste mit; die Klasse idt-knotendreieck kommt für style.css dazu. */
echo idt_render_knotendreieck( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- idt_render_knotendreieck() escapet jeden Wert einzeln.
	$attributes,
	get_block_wrapper_attributes( array( 'class' => 'idt-knotendreieck' ) )
);
