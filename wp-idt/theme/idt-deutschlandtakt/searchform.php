<?php
/**
 * Suchformular im Markenstil (kompaktes Feld + Icon-Button).
 * Wird u. a. im Site-Header und über get_search_form() genutzt.
 *
 * @package idt
 */
?>
<form role="search" method="get" class="idt-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="idt-search__label">
		<span class="screen-reader-text"><?php esc_html_e( 'Suche nach:', 'idt' ); ?></span>
		<input type="search" class="idt-search__field" name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'Suchen …', 'idt' ); ?>">
	</label>
	<button type="submit" class="idt-search__submit" aria-label="<?php esc_attr_e( 'Suche starten', 'idt' ); ?>">
		<?php echo idt_icon( 'search', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</form>
