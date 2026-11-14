<?php
/**
 * Related Posts cache version and invalidation.
 *
 * Cached results are keyed by a version number. Any change that can affect
 * related posts bumps the version: old keys become unreachable and expire
 * on their own, so no key ever has to be tracked or deleted one by one.
 *
 * @package AF_Blocks
 */

defined( 'ABSPATH' ) || exit;

const AF_BLOCKS_RELATED_CACHE_OPTION = 'af_blocks_related_cache_version';

/**
 * Returns the current cache version.
 *
 * @return int Cache version.
 */
function af_blocks_related_cache_version() {
	return (int) get_option( AF_BLOCKS_RELATED_CACHE_OPTION, 1 );
}

/**
 * Creates the version option, autoloaded, on activation. An existing option
 * is left as is. Autoloading keeps the read off the database on every view.
 *
 * @return void
 */
function af_blocks_related_cache_activate() {
	add_option( AF_BLOCKS_RELATED_CACHE_OPTION, 1, '', true );
}

/**
 * Bumps the cache version, invalidating every cached result at once.
 *
 * @return void
 */
function af_blocks_related_cache_bump() {
	update_option( AF_BLOCKS_RELATED_CACHE_OPTION, af_blocks_related_cache_version() + 1, true );
}

/**
 * Bumps the version when a post is saved. Revisions and autosaves do not
 * change published content, and other post types are never related posts.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function af_blocks_related_cache_on_save_post( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( 'post' !== $post->post_type ) {
		return;
	}

	af_blocks_related_cache_bump();
}
add_action( 'save_post', 'af_blocks_related_cache_on_save_post', 10, 2 );

/**
 * Bumps the version when a post is deleted.
 *
 * @param int          $post_id Post ID.
 * @param WP_Post|null $post    Post object.
 * @return void
 */
function af_blocks_related_cache_on_deleted_post( $post_id, $post = null ) {
	if ( $post instanceof WP_Post && 'post' !== $post->post_type ) {
		return;
	}

	af_blocks_related_cache_bump();
}
add_action( 'deleted_post', 'af_blocks_related_cache_on_deleted_post', 10, 2 );

/**
 * Bumps the version when a post's categories or tags change.
 *
 * @param int    $object_id Object ID.
 * @param array  $terms     Terms (unused).
 * @param array  $tt_ids    Term taxonomy IDs (unused).
 * @param string $taxonomy  Taxonomy slug.
 * @return void
 */
function af_blocks_related_cache_on_set_object_terms( $object_id, $terms, $tt_ids, $taxonomy ) {
	if ( ! in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
		return;
	}

	if ( 'post' !== get_post_type( $object_id ) ) {
		return;
	}

	af_blocks_related_cache_bump();
}
add_action( 'set_object_terms', 'af_blocks_related_cache_on_set_object_terms', 10, 4 );
