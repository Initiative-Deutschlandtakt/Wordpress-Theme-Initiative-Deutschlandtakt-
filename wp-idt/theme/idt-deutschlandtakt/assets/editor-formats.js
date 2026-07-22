/**
 * IDT-Editor-Anpassungen (build-frei, nur globale wp.*-Pakete, in IIFE gekapselt):
 *
 *  1. Inline-Stilelemente (Marker, Eyebrow, Tag) als RichText-Formate —
 *     erscheinen beim Markieren von Text im „▾ Weitere"-Menü der Formatierungs-
 *     leiste. Gestylt über style.css (per add_editor_style auch im Editor sichtbar).
 *  2. Off-Brand-Buttons ausblenden — der WordPress-Standard-Button-Block wird
 *     entfernt, sodass nur die IDT-Buttons ([btn]/[pill]) angeboten werden.
 */
( function ( wp ) {
	if ( ! wp || ! wp.richText || ! wp.element || ! wp.blockEditor || ! wp.i18n ) {
		return;
	}

	var registerFormatType = wp.richText.registerFormatType;
	var toggleFormat       = wp.richText.toggleFormat;
	var el                 = wp.element.createElement;
	var RichTextToolbarButton = wp.blockEditor.RichTextToolbarButton;
	var __ = wp.i18n.__;

	var FORMATS = [
		{ name: 'idt/mark',        title: __( 'Markieren (Gelb)', 'idt' ),     tagName: 'mark', className: 'idt-mark',         icon: 'edit' },
		{ name: 'idt/mark-cyan',   title: __( 'Markieren (Cyan)', 'idt' ),     tagName: 'mark', className: 'idt-mark--cyan',   icon: 'edit' },
		{ name: 'idt/mark-violet', title: __( 'Markieren (Violett)', 'idt' ),  tagName: 'mark', className: 'idt-mark--violet', icon: 'edit' },
		{ name: 'idt/eyebrow',     title: __( 'Eyebrow-Label', 'idt' ),        tagName: 'span', className: 'idt-eyebrow',      icon: 'arrow-right-alt' },
		{ name: 'idt/tag',         title: __( 'Tag / Chip', 'idt' ),           tagName: 'span', className: 'idt-tag',          icon: 'tag' }
	];

	FORMATS.forEach( function ( f ) {
		registerFormatType( f.name, {
			title:     f.title,
			tagName:   f.tagName,
			className: f.className,
			edit: function ( props ) {
				return el( RichTextToolbarButton, {
					icon:     f.icon,
					title:    f.title,
					isActive: props.isActive,
					onClick:  function () {
						props.onChange( toggleFormat( props.value, { type: f.name } ) );
					}
				} );
			}
		} );
	} );

	/* Off-Brand-Buttons ausblenden: nur die IDT-Buttons (Shortcode [btn]/[pill],
	   als Patterns auswählbar) sollen angeboten werden. Reversibel — diese
	   beiden Zeilen entfernen, um den Standard-Button zurückzuholen. */
	if ( wp.blocks && wp.domReady ) {
		wp.domReady( function () {
			[ 'core/buttons', 'core/button' ].forEach( function ( name ) {
				if ( wp.blocks.getBlockType( name ) ) {
					try { wp.blocks.unregisterBlockType( name ); } catch ( e ) {}
				}
			} );
		} );
	}
} )( window.wp );
