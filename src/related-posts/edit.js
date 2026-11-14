/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const TAXONOMIES = [
	{ value: 'category', label: __( 'Categories', 'af-blocks' ) },
	{ value: 'post_tag', label: __( 'Tags', 'af-blocks' ) },
];

function EmptyPreview() {
	return (
		<p>
			{ __(
				'Related posts appear on single posts that share a category or tag with other published posts.',
				'af-blocks'
			) }
		</p>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { count, taxonomy, showImage, showDate } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'af-blocks' ) }>
					<RangeControl
						label={ __( 'Number of posts', 'af-blocks' ) }
						value={ count }
						onChange={ ( value ) =>
							setAttributes( { count: value } )
						}
						min={ 1 }
						max={ 6 }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Related by', 'af-blocks' ) }
						value={ taxonomy }
						options={ TAXONOMIES }
						onChange={ ( value ) =>
							setAttributes( { taxonomy: value } )
						}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show featured image', 'af-blocks' ) }
						checked={ showImage }
						onChange={ ( value ) =>
							setAttributes( { showImage: value } )
						}
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show date', 'af-blocks' ) }
						checked={ showDate }
						onChange={ ( value ) =>
							setAttributes( { showDate: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<ServerSideRender
					block="af-blocks/related-posts"
					attributes={ attributes }
					EmptyResponsePlaceholder={ EmptyPreview }
				/>
			</div>
		</>
	);
}
