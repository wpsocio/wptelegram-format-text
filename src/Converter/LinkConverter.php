<?php
/**
 * Link converter.
 *
 * @package WPTelegram\FormatText\Converter
 */

namespace WPTelegram\FormatText\Converter;

use WPTelegram\FormatText\ElementInterface;

/**
 * Class LinkConverter
 */
class LinkConverter extends BaseConverter {

	/**
	 * {@inheritdoc}
	 */
	public function convert( ElementInterface $element ) {

		list( $href, $text ) = $this->getLinkInfo( $element );

		$relativeLinks = $this->config->getOption( 'relative_links', 'clear' );

		// If relative link should NOT be preserved.
		if ( $this->isRelativeLink( $href ) && 'preserve' !== $relativeLinks ) {
			return $text;
		}

		if ( $this->formattingToMarkdown() ) {
			$href = $this->escapeMarkdownChars( $href, '', [ ')', '\\' ] );
			return sprintf( '[%1$s](%2$s)', $text, $href );
		}

		if ( $this->formattingToHtml() ) {
			$href = str_replace( '"', rawurlencode( '"' ), $href );
			return sprintf( '<a href="%1$s">%2$s</a>', $href, $text );
		}

		$retainHyperlinks = $this->config->getOption( 'retain_hyperlinks', true );

		$format = $retainHyperlinks ? '%1$s (%2$s)' : '%1$s';

		return sprintf( $format, $text, $href );
	}

	/**
	 * Get link info.
	 *
	 * @param ElementInterface $element The element.
	 *
	 * @return array - The link info.
	 */
	private function getLinkInfo( ElementInterface $element ) {
		$href = trim( $element->getAttribute( 'href' ) );
		$text = trim( $element->getValue(), "\t\n\r\0\x0B" );

		return [ $href, $text ];
	}

	/**
	 * Whether a link is relative. based on whether it starts with a valid protocol scheme.
	 *
	 * @param string $href The link.
	 *
	 * @return boolean Whether the link is relative.
	 */
	protected function isRelativeLink( string $href ) {
		// If the link starts with a valid protocol scheme e.g. "http://", it's not relative.
		return ! preg_match( '#^[a-z][a-z0-9]*://#i', $href );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'a' ];
	}
}
