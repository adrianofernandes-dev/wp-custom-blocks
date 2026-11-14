/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import Edit from './edit';
import './style.scss';

// Dynamic block: rendered in PHP (render.php), nothing is saved.
registerBlockType( metadata.name, {
	edit: Edit,
} );
