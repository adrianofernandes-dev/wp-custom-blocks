<?php
/**
 * Server render for af-blocks/related-posts.
 *
 * Lists published posts that share a term (category or tag) with the current
 * post. Result post IDs are cached in a transient for 12 hours; the key
 * carries a cache version that includes/related-cache.php bumps on changes.
 *
 * @package AF_Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'af_blocks_related_is_editor_preview' ) ) {
	/**
	 * Whether this is the editor's ServerSideRender request for this block.
	 *
	 * Other REST responses that render post content (such as
	 * content.rendered) must not include related posts.
	 *
	 * @return bool
	 */
	function af_blocks_related_is_editor_preview() {
		if ( ! wp_is_serving_rest_request() ) {
			return false;
		}

		$route = $GLOBALS['wp']->query_vars['rest_route'] ?? '';

		return is_string( $route ) && str_starts_with( $route, '/wp/v2/block-renderer/af-blocks/related-posts' );
	}
}

// Runs only on a single post, or in the editor preview of a post.
if ( is_singular( 'post' ) ) {
	$af_blocks_related_post_id = get_queried_object_id();
} elseif ( af_blocks_related_is_editor_preview() && 'post' === get_post_type() ) {
	$af_blocks_related_post_id = get_the_ID();
} else {
	return;
}

// Sanitize early: never trust stored attributes, clamp in PHP too.
$af_blocks_related_count      = min( 6, max( 1, (int) ( $attributes['count'] ?? 3 ) ) );
$af_blocks_related_taxonomy   = in_array( $attributes['taxonomy'] ?? '', array( 'category', 'post_tag' ), true ) ? $attributes['taxonomy'] : 'category';
$af_blocks_related_show_image = ! empty( $attributes['showImage'] );
$af_blocks_related_show_date  = ! empty( $attributes['showDate'] );

$af_blocks_related_terms = get_the_terms( $af_blocks_related_post_id, $af_blocks_related_taxonomy );

if ( empty( $af_blocks_related_terms ) || is_wp_error( $af_blocks_related_terms ) ) {
	return;
}

// Only the attributes that change which posts are found go into the key.
$af_blocks_related_key = sprintf(
	'af_blocks_related_%d_%s_v%d',
	$af_blocks_related_post_id,
	md5( $af_blocks_related_taxonomy . '|' . $af_blocks_related_count ),
	af_blocks_related_cache_version()
);

$af_blocks_related_ids = get_transient( $af_blocks_related_key );

if ( is_array( $af_blocks_related_ids ) ) {
	$af_blocks_related_ids = array_map( 'intval', $af_blocks_related_ids );

	if ( $af_blocks_related_ids ) {
		// One primary-key query for all posts (and their meta when images are on).
		_prime_post_caches( $af_blocks_related_ids, false, $af_blocks_related_show_image );
	}

	$af_blocks_related_posts = array_filter( array_map( 'get_post', $af_blocks_related_ids ) );
} else {
	$af_blocks_related_query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Result is cached in a transient.
				array(
					'taxonomy' => $af_blocks_related_taxonomy,
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $af_blocks_related_terms, 'term_id' ),
				),
			),
			'post__not_in'           => array( $af_blocks_related_post_id ),
			'posts_per_page'         => $af_blocks_related_count,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => $af_blocks_related_show_image,
		)
	);

	$af_blocks_related_posts = $af_blocks_related_query->posts;

	set_transient(
		$af_blocks_related_key,
		wp_list_pluck( $af_blocks_related_posts, 'ID' ),
		12 * HOUR_IN_SECONDS
	);
}

// A cached ID may point to a post that is no longer published.
$af_blocks_related_posts = array_filter(
	$af_blocks_related_posts,
	static function ( $post ) {
		return 'publish' === $post->post_status;
	}
);

// Empty means nothing.
if ( ! $af_blocks_related_posts ) {
	return;
}

if ( $af_blocks_related_show_image ) {
	$af_blocks_related_thumb_ids = array_filter( array_map( 'get_post_thumbnail_id', $af_blocks_related_posts ) );

	if ( $af_blocks_related_thumb_ids ) {
		// One query for all attachments and their metadata.
		_prime_post_caches( array_map( 'intval', $af_blocks_related_thumb_ids ), false, true );
	}
}
?>
<ul <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes each attribute. ?> role="list">
	<?php foreach ( $af_blocks_related_posts as $af_blocks_related_post ) : ?>
		<?php
		$af_blocks_related_title = get_the_title( $af_blocks_related_post );
		$af_blocks_related_thumb = $af_blocks_related_show_image ? get_post_thumbnail_id( $af_blocks_related_post ) : 0;
		?>
		<li class="wp-block-af-blocks-related-posts__item">
			<a class="wp-block-af-blocks-related-posts__link" href="<?php echo esc_url( get_permalink( $af_blocks_related_post ) ); ?>">
				<?php
				if ( $af_blocks_related_thumb ) {
					// Decorative: the title next to it is the link text.
					echo wp_get_attachment_image(
						$af_blocks_related_thumb,
						'thumbnail',
						false,
						array(
							'class'   => 'wp-block-af-blocks-related-posts__image',
							'alt'     => '',
							'loading' => 'lazy',
							'sizes'   => '96px',
						)
					);
				}
				?>
				<span class="wp-block-af-blocks-related-posts__title"><?php echo esc_html( '' !== $af_blocks_related_title ? $af_blocks_related_title : __( '(no title)', 'af-blocks' ) ); ?></span>
			</a>
			<?php if ( $af_blocks_related_show_date ) : ?>
				<time class="wp-block-af-blocks-related-posts__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $af_blocks_related_post ) ); ?>"><?php echo esc_html( get_the_date( '', $af_blocks_related_post ) ); ?></time>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
