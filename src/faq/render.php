<?php
/**
 * Server render for af-blocks/faq.
 *
 * Renders the FAQ items one by one and skips any item without a question
 * (the item always saves its markup, so the answer is never lost). When
 * outputSchema is on, prints FAQPage JSON-LD, once per page.
 *
 * @package AF_Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks markup. Not used: items are rendered one by one.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'af_blocks_faq_plain_text' ) ) {
	/**
	 * Converts block HTML to a single line of plain text.
	 *
	 * @param string $html HTML.
	 * @return string Plain text.
	 */
	function af_blocks_faq_plain_text( $html ) {
		$text = wp_strip_all_tags( (string) $html );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
	}
}

if ( ! function_exists( 'af_blocks_faq_claim_schema' ) ) {
	/**
	 * Returns true only the first time it is called in a request, so only
	 * one FAQPage is printed per page.
	 *
	 * @return bool Whether the caller may print the schema.
	 */
	function af_blocks_faq_claim_schema() {
		static $claimed = false;

		if ( $claimed ) {
			return false;
		}

		$claimed = true;

		return true;
	}
}

$af_blocks_faq_items_html = '';
$af_blocks_faq_entities   = array();

foreach ( $block->inner_blocks as $af_blocks_faq_item ) {
	if ( 'af-blocks/faq-item' !== $af_blocks_faq_item->name ) {
		continue;
	}

	$af_blocks_faq_question = af_blocks_faq_plain_text( $af_blocks_faq_item->attributes['question'] ?? '' );

	if ( '' === $af_blocks_faq_question ) {
		continue;
	}

	$af_blocks_faq_items_html .= $af_blocks_faq_item->render();

	$af_blocks_faq_answer_html = '';
	foreach ( $af_blocks_faq_item->inner_blocks as $af_blocks_faq_answer_block ) {
		$af_blocks_faq_answer_html .= $af_blocks_faq_answer_block->render();
	}

	$af_blocks_faq_answer = af_blocks_faq_plain_text( $af_blocks_faq_answer_html );

	if ( '' === $af_blocks_faq_answer ) {
		continue;
	}

	$af_blocks_faq_entities[] = array(
		'@type'          => 'Question',
		'name'           => $af_blocks_faq_question,
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => $af_blocks_faq_answer,
		),
	);
}

// Empty means nothing: no item with a question, no wrapper.
if ( '' === trim( $af_blocks_faq_items_html ) ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes each attribute. ?>>
	<?php echo wp_kses_post( $af_blocks_faq_items_html ); ?>
</div>
<?php
if ( ! empty( $attributes['outputSchema'] ) && $af_blocks_faq_entities && af_blocks_faq_claim_schema() ) :
	$af_blocks_faq_schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $af_blocks_faq_entities,
	);
	?>
<script type="application/ld+json"><?php echo wp_json_encode( $af_blocks_faq_schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON for a script element; JSON_HEX_TAG escapes < and > so the text cannot close the tag. ?></script>
	<?php
endif;
