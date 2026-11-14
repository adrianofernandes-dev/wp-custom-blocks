/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

const ALLOWED_BLOCKS = [ 'af-blocks/faq-item' ];

const TEMPLATE = [ [ 'af-blocks/faq-item' ], [ 'af-blocks/faq-item' ] ];

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		allowedBlocks: ALLOWED_BLOCKS,
		template: TEMPLATE,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'af-blocks' ) }>
					<ToggleControl
						label={ __( 'Output FAQ schema', 'af-blocks' ) }
						help={ __(
							'Adds FAQPage structured data. Only the first FAQ on a page outputs it.',
							'af-blocks'
						) }
						checked={ attributes.outputSchema }
						onChange={ ( value ) =>
							setAttributes( { outputSchema: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...innerBlocksProps } />
		</>
	);
}
