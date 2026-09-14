/**
 * Knotendreieck — Oberfläche im Block-Editor.
 *
 * Build-frei wie der Rest des Themes: nur globale wp.*-Pakete, kein JSX, alles
 * in eine IIFE gekapselt. Bearbeitet wird über Felder in der Seitenleiste; die
 * Vorschau im Editor ist dasselbe Custom Element wie im Frontend (view.js,
 * über block.json als "script" auch im Editor geladen) — nur ohne Autostart,
 * damit beim Schreiben nichts im Augenwinkel zappelt. Wer sie sehen will,
 * drückt den Play-Knopf unter der Grafik.
 *
 * Gespeichert wird nichts: der Block ist dynamisch, das Frontend-Markup baut
 * idt_render_knotendreieck() über render.php.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.i18n ) {
		return;
	}

	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var C                 = wp.components;
	var __                = wp.i18n.__;

	wp.blocks.registerBlockType( 'idt/knotendreieck', {
		edit: function ( props ) {
			var a = props.attributes;

			function set( key ) {
				return function ( value ) {
					var update = {};
					update[ key ] = value;
					props.setAttributes( update );
				};
			}

			/* Das Custom Element liest seine Texte beim Aufbau ein und danach
			   nicht mehr. Der key erzwingt einen Neuaufbau, sobald sich eine
			   Beschriftung oder die Fahrzeugform ändert — so zeigt die
			   Vorschau beim Tippen mit. */
			var key = [
				a.grafikTitel, a.subzeile, a.knotenOben,
				a.knotenLinks, a.knotenRechts, a.fahrzeugStil
			].join( '|' );

			var sidebar = el( InspectorControls, null,
				el( C.PanelBody, { title: __( 'Beschriftung', 'idt' ), initialOpen: true },
					el( C.TextControl, {
						label: __( 'Überschrift im Bild', 'idt' ),
						help: __( 'Steht oben links in der Grafik und bleibt beim Bild, wenn es jemand weiterverwendet. Ab etwa 20 Zeichen wird sie automatisch kleiner.', 'idt' ),
						value: a.grafikTitel,
						onChange: set( 'grafikTitel' )
					} ),
					el( C.TextControl, {
						label: __( 'Zeile unten links', 'idt' ),
						value: a.subzeile,
						onChange: set( 'subzeile' )
					} ),
					el( C.TextControl, {
						label: __( 'Knoten oben', 'idt' ),
						value: a.knotenOben,
						onChange: set( 'knotenOben' )
					} ),
					el( C.TextControl, {
						label: __( 'Knoten unten links', 'idt' ),
						value: a.knotenLinks,
						onChange: set( 'knotenLinks' )
					} ),
					el( C.TextControl, {
						label: __( 'Knoten unten rechts', 'idt' ),
						value: a.knotenRechts,
						onChange: set( 'knotenRechts' )
					} ),
					el( C.TextareaControl, {
						label: __( 'Bildunterschrift', 'idt' ),
						help: __( 'Erscheint als Zeile unter der Grafik, nicht im Bild — dort bleibt sie durchsuchbar und für Screenreader lesbar.', 'idt' ),
						value: a.bildunterschrift,
						onChange: set( 'bildunterschrift' )
					} ),
					el( 'p', { style: { fontSize: '12px', color: '#757575', marginBottom: 0 } },
						__( 'Die Fahrzeiten 28 / 28 / 57 Minuten sind fest: Sie hängen mit den Knotenfenstern :28–:32 und :58–:02 zusammen. Eine frei geänderte Zahl würde nur die Beschriftung ändern, nicht den Fahrplan — das Bild behauptete dann etwas anderes, als die Bewegung zeigt.', 'idt' )
					)
				),
				el( C.PanelBody, { title: __( 'Bewegung', 'idt' ), initialOpen: false },
					el( C.RangeControl, {
						label: __( 'Dauer eines Stundenzyklus (Sekunden)', 'idt' ),
						value: a.zyklusSekunden,
						onChange: set( 'zyklusSekunden' ),
						min: 8,
						max: 24,
						step: 0.5
					} ),
					el( C.ToggleControl, {
						label: __( 'Von selbst starten', 'idt' ),
						help: __( 'Startet auf der Website, sobald die Grafik im Sichtfeld steht. Wer im System reduzierte Bewegung eingestellt hat, sieht ohnehin das Standbild.', 'idt' ),
						checked: !! a.autoplay,
						onChange: set( 'autoplay' )
					} ),
					el( C.SelectControl, {
						label: __( 'Fahrzeuge', 'idt' ),
						value: a.fahrzeugStil,
						options: [
							{ label: __( 'Striche', 'idt' ), value: 'Striche' },
							{ label: __( 'Punkte', 'idt' ), value: 'Punkte' }
						],
						onChange: set( 'fahrzeugStil' )
					} ),
					el( 'p', { style: { fontSize: '12px', color: '#757575', marginBottom: 0 } },
						__( 'Im Editor läuft die Animation nicht von selbst — der Play-Knopf unter der Grafik zeigt sie.', 'idt' )
					)
				)
			);

			var grafik = el( 'idt-knotendreieck', {
				key: key,
				'grafik-titel': a.grafikTitel,
				'subzeile': a.subzeile,
				'knoten-oben': a.knotenOben,
				'knoten-links': a.knotenLinks,
				'knoten-rechts': a.knotenRechts,
				'cycle-seconds': String( a.zyklusSekunden ),
				'vehicle-style': a.fahrzeugStil,
				'autoplay': 'false'
			} );

			var caption = a.bildunterschrift
				? el( 'figcaption', { className: 'wp-element-caption' }, a.bildunterschrift )
				: null;

			return el( Fragment, null,
				sidebar,
				el( 'figure', useBlockProps( { className: 'idt-knotendreieck' } ), grafik, caption )
			);
		},

		/* Dynamischer Block: die Ausgabe kommt aus render.php. */
		save: function () { return null; }
	} );

} )( window.wp );
