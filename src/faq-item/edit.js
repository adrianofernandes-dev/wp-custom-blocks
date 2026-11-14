/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/list' ];

const TEMPLATE = [
	[ 'core/paragraph', { placeholder: __( 'Answer…', 'af-blocks' ) } ],
];

// Plain emphasis only: a link inside <summary> would nest interactive
// content inside an interactive element.
const QUESTION_FORMATS = [ 'core/bold', 'core/italic' ];

/**
 * The editor uses plain elements instead of <details>/<summary>: typing a
 * space inside a <summary> would toggle the element.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Attribute setter.
 * @return {Element} Editor markup.
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-af-blocks-faq-item__answer' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE }
	);

	return (
		<div { ...blockProps }>
			<RichText
				tagName="p"
				className="wp-block-af-blocks-faq-item__question"
				value={ attributes.question }
				onChange={ ( value ) => setAttributes( { question: value } ) }
				allowedFormats={ QUESTION_FORMATS }
				placeholder={ __( 'Question', 'af-blocks' ) }
			/>
			<div { ...innerBlocksProps } />
		</div>
	);
}
