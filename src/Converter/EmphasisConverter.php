<?php
/**
 * Emphasis converter.
 *
 * @package WPTelegram\FormatText\Converter
 */

namespace WPTelegram\FormatText\Converter;

use WPTelegram\FormatText\ElementInterface;

/**
 * Class EmphasisConverter
 */
class EmphasisConverter extends BaseConverter {

	/**
	 * Get the normalized tag name.
	 *
	 * @param ElementInterface|null $element The element.
	 */
	protected function getNormTag( $element ) {
		if ( null !== $element && ! $element->isText() ) {
			$tag = $element->getTagName();
			if ( 'i' === $tag || 'em' === $tag ) {
				return 'em';
			}

			if ( 'b' === $tag || 'strong' === $tag ) {
				return 'b';
			}
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToMarkdown( ElementInterface $element ) {
		$tag   = $this->getNormTag( $element );
		$value = $element->getValue();

		if ( ! trim( $value ) ) {
			return $value;
		}

		$style = 'em' === $tag ? '_' : '*';

		$prefix = ltrim( $value ) !== $value ? ' ' : '';
		$suffix = rtrim( $value ) !== $value ? ' ' : '';

		/*
		 * If this node is immediately preceded or followed by one of the same type don't emit
		 * the start or end $style, respectively. This prevents <em>foo</em><em>bar</em> from
		 * being converted to *foo**bar* which is incorrect. We want *foobar* instead.
		 */
		$preStyle  = $this->getNormTag( $element->getPreviousSibling() ) === $tag ? '' : $style;
		$postStyle = $this->getNormTag( $element->getNextSibling() ) === $tag ? '' : $style;

		return $prefix . $preStyle . trim( $value ) . $postStyle . $suffix;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToHtml( ElementInterface $element ) {
		$tag   = $this->getNormTag( $element );
		$value = $element->getValue();

		if ( ! trim( $value ) ) {
			return $value;
		}

		return sprintf( '<%1$s>%2$s</%1$s>', $tag, $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'em', 'i', 'strong', 'b' ];
	}
}
