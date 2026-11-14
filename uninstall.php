<?php
/**
 * Uninstall routine.
 *
 * Deletes the related-posts cache version option. Transients are not deleted
 * one by one: they expire on their own (SPEC §6).
 *
 * @package AF_Blocks
 */

defined( 'ABSPATH' ) || exit;
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'af_blocks_related_cache_version' );
