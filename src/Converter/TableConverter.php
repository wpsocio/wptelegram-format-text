<?php
/**
 * Table converter.
 *
 * @package WPTelegram\FormatText\Converter
 */

namespace WPTelegram\FormatText\Converter;

use WPTelegram\FormatText\ElementInterface;

/**
 * Class TableConverter
 */
class TableConverter extends BaseConverter {

	/**
	 * Prepare the element before converting.
	 *
	 * @param ElementInterface $element The element to convert.
	 */
	public function preConvert( ElementInterface $element ): void {
		$tag = $element->getTagName();
		// Only table cells and caption are allowed to contain content.
		// Remove all text between other table elements.
		if ( 'th' === $tag || 'td' === $tag || 'caption' === $tag ) {
			return;
		}

		foreach ( $element->getChildren() as $child ) {
			if ( $child->isText() ) {
				$child->setFinalOutput( '' );
			}
		}
	}

	/**
	 * Convert the table elements
	 *
	 * @param ElementInterface $element The element to convert.
	 */
	public function convert( ElementInterface $element ) {
		$tag = $element->getTagName();

		$value = $element->getValue();

		switch ( $tag ) {
			case 'th':
			case 'td':
				$value       = trim( $value );
				$nextElement = $element->getNextElement();

				// If the next element is a td or th, add a tab.
				if ( $nextElement && in_array( $nextElement->getTagName(), [ 'th', 'td' ], true ) ) {
					$value = $value . $this->escapeMarkdownChars( $this->config->getOption( 'table_cell_sep', ' | ' ) );
				}
				break;

			case 'tr':
				$value = $value . $this->escapeMarkdownChars( $this->config->getOption( 'table_row_sep', "\n" ) );
				break;
			case 'caption':
				$value = $value . "\n";
				break;

		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSupportedTags() {
		return [ 'table', 'tr', 'th', 'td', 'thead', 'tbody', 'tfoot', 'colgroup', 'col', 'caption' ];
	}
}
