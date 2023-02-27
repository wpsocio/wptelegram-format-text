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

		list($src, $text ) = $this->getImageInfo( $element );

		$this->isOnlyChildOfLink( $element );

		// If the image is inside a link, return the image text if present
		if ( $element->isDescendantOf( [ 'a' ] ) && $text ) {
			return $text;
		}

		// If the image is the only child of the parent link, return the image lint.
		if ( $this->isOnlyChildOfLink( $element ) ) {
			return $src;
		}

		return '';
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

		return [ $src, $text ];
	}

	/**
	 * Whether the image is the only child of the parent link.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return boolean
	 */
	private function isOnlyChildOfLink( ElementInterface $element ) {
		$parent = $element->getParent();

		if ( $parent && 'a' === $parent->getTagName() ) {
			$children = $parent->getChildren();

			foreach ( $children as $child ) {
				if ( ! $child->isWhitespace() && ! $child->equals( $element ) ) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'img' ];
	}
}
