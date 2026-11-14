<?php
/**
 * Plugin Name:       AF Blocks
 * Description:       Three custom Gutenberg blocks: Callout, FAQ and Related Posts.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Adriano Fernandes
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       af-blocks
 *
 * @package AF_Blocks
 */

defined( 'ABSPATH' ) || exit;

define( 'AF_BLOCKS_VERSION', '0.1.0' );
define( 'AF_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );

require_once AF_BLOCKS_PATH . 'includes/register-blocks.php';
require_once AF_BLOCKS_PATH . 'includes/related-cache.php';

register_activation_hook( __FILE__, 'af_blocks_related_cache_activate' );
