/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './edit';
import './style.scss';

registerBlockType( metadata.name, {
	edit: Edit,
	// Rendered in PHP (render.php). Saving the inner blocks keeps the items
	// in post content so render.php receives them.
	save: () => <InnerBlocks.Content />,
} );
