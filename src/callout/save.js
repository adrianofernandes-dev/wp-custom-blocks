/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';

/**
 * Static block: this markup is stored in post content and validated against
 * it on every editor load. Once released, any change here needs a matching
 * entry in `deprecated`, or existing callouts become invalid blocks.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element|null} Saved markup, or nothing when there is no content.
 */
export default function save( { attributes } ) {
	const { variant, title, content } = attributes;

	// Empty means nothing: no content, no wrapper.
	if ( RichText.isEmpty( content ) ) {
		return null;
	}

	const blockProps = useBlockProps.save( {
		className: `is-variant-${ variant }`,
		role: 'note',
	} );

	return (
		<aside { ...blockProps }>
			{ ! RichText.isEmpty( title ) && (
				<RichText.Content
					tagName="p"
					className="wp-block-af-blocks-callout__title"
					value={ title }
				/>
			) }
			<RichText.Content
				tagName="div"
				className="wp-block-af-blocks-callout__content"
				value={ content }
			/>
		</aside>
	);
}
