<?php
/**
 * Registers every block found in build/.
 *
 * Each subdirectory of build/ that contains a block.json is registered from
 * its metadata. With no blocks built yet, nothing is registered.
 *
 * @package AF_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers each block in build/ from its block.json.
 *
 * @return void
 */
function af_blocks_register_blocks() {
	$block_dirs = glob( AF_BLOCKS_PATH . 'build/*', GLOB_ONLYDIR );

	if ( empty( $block_dirs ) ) {
		return;
	}

	foreach ( $block_dirs as $block_dir ) {
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}
add_action( 'init', 'af_blocks_register_blocks' );
