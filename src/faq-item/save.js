/**
 * WordPress dependencies
 */
import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

/**
 * Always saves its markup, even with an empty question: returning null here
 * would drop the inner blocks (the answer) from post content. The parent FAQ
 * skips items without a question when it renders.
 *
 * Static block: once released, any change here needs a `deprecated` entry.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Block attributes.
 * @return {Element} Saved markup.
 */
export default function save( { attributes } ) {
	const blockProps = useBlockProps.save();
	const innerBlocksProps = useInnerBlocksProps.save( {
		className: 'wp-block-af-blocks-faq-item__answer',
	} );

	return (
		<details { ...blockProps }>
			<RichText.Content tagName="summary" value={ attributes.question } />
			<div { ...innerBlocksProps } />
		</details>
	);
}
