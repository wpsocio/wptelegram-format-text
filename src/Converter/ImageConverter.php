<?php
/**
 * Image converter.
 *
 * @package WPTelegram\FormatText\Converter
 */

namespace WPTelegram\FormatText\Converter;

use WPTelegram\FormatText\ElementInterface;

/**
 * Class LinkConverter
 */
class ImageConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {

		list( $src, $text ) = $this->getImageInfo( $element );

		if ( $element->isDescendantOf( [ 'a' ] ) ) {
			return $text;
		}

		if ( $this->formattingToMarkdown() ) {
			$src = $this->escapeMarkdownChars( $src, '', [ ')', '\\' ] );
			return sprintf( '[%1$s](%2$s)', $text, $src );
		}

		if ( $this->formattingToHtml() ) {
			$src = str_replace( '"', rawurlencode( '"' ), $src );
			return sprintf( '<a href="%1$s">%2$s</a>', $src, $text );
		}

		return $text;
	}

	/**
	 * Get image info.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return array - The image info.
	 */
	private function getImageInfo( ElementInterface $element ) {
		$src   = trim( $element->getAttribute( 'src' ) );
		$alt   = trim( $element->getAttribute( 'alt' ) );
		$title = trim( $element->getAttribute( 'title' ) );

		$text = $title ? $title : $alt;
		$text = $text ? $text : $src;

		return [ $src, $text ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'img' ];
	}
}
