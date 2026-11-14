/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

const VARIANTS = [
	{ value: 'info', label: __( 'Info', 'af-blocks' ) },
	{ value: 'success', label: __( 'Success', 'af-blocks' ) },
	{ value: 'warning', label: __( 'Warning', 'af-blocks' ) },
	{ value: 'danger', label: __( 'Danger', 'af-blocks' ) },
];

export default function Edit( { attributes, setAttributes } ) {
	const { variant, title, content } = attributes;

	const blockProps = useBlockProps( {
		className: `is-variant-${ variant }`,
		role: 'note',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'af-blocks' ) }>
					<SelectControl
						label={ __( 'Variant', 'af-blocks' ) }
						value={ variant }
						options={ VARIANTS }
						onChange={ ( value ) =>
							setAttributes( { variant: value } )
						}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<aside { ...blockProps }>
				<RichText
					tagName="p"
					className="wp-block-af-blocks-callout__title"
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					placeholder={ __( 'Title (optional)', 'af-blocks' ) }
				/>
				<RichText
					tagName="div"
					className="wp-block-af-blocks-callout__content"
					value={ content }
					onChange={ ( value ) =>
						setAttributes( { content: value } )
					}
					placeholder={ __( 'Write the callout…', 'af-blocks' ) }
				/>
			</aside>
		</>
	);
}
