<?php
/**
 * A helper class to convert HTML
 *
 * @package HtmlConverter
 */

namespace WPTelegram\FormatText;

use DOMDocument;
use WPTelegram\FormatText\Converter\Utils;

/**
 * A helper class to convert HTML
 */
class HtmlConverter implements HtmlConverterInterface {

	/**
	 * The environment
	 *
	 * @var Environment
	 */
	protected $environment;

	/**
	 * Constructor
	 *
	 * @param Environment|array<string, mixed> $options Environment object or configuration options.
	 */
	public function __construct( $options = [] ) {
		if ( $options instanceof Environment ) {
			$this->environment = $options;
		} elseif ( is_array( $options ) ) {
			$defaults = [
				// Set the default character set.
				'char_set'            => 'auto',
				// An array of elements to remove.
				'elements_to_remove'  => [ 'form' ],
				// Set to 'HTML', 'Markdown' or 'MarkdownV2'.
				'format_to'           => 'text',
				// Set the default character for each <li> in a <ul>. Can be '-', '*', or '+'.
				'list_item_style'     => '-',
				// Set to 'preserve' to preserve relative links.
				'relative_links'      => 'clean',
				// Set to false to keep display:none elements.
				'remove_display_none' => true,
				// A callable to determine if a node should be converted.
				'should_convert_cb'   => null,
				// `list_item_style` for nested <ul> and <ol>.
				'sub_list_item_style' => '◦',
				// Set to false to show warnings when loading malformed HTML.
				'suppress_errors'     => true,
				// Set the default separator for each <td> and <th>.
				'table_cell_sep'      => ' | ',
				// Set the default separator for each <tr>.
				'table_row_sep'       => "\n",
			];

			$this->environment = Environment::createDefaultEnvironment( $defaults );

			$this->environment->getConfig()->merge( $options );
		}
	}

	/**
	 * It returns the environment.
	 *
	 * @return Environment The environment.
	 */
	public function getEnvironment() {
		return $this->environment;
	}

	/**
	 * It returns the configuration.
	 *
	 * @return Configuration The configuration.
	 */
	public function getConfig() {
		return $this->environment->getConfig();
	}

	/**
	 * Convert
	 *
	 * @param string $html The html to convert.
	 *
	 * @see HtmlConverter::convert
	 *
	 * @return string The Markdown version of the html
	 */
	public function __invoke( string $html ) {
		return $this->convert( $html );
	}

	/**
	 * Convert the given $html
	 *
	 * @param string $html The html to convert.
	 *
	 * @return string The Markdown version of the html
	 */
	public function convert( $html ) {
		// DOMDocument doesn't support empty value and throws an error.
		if ( trim( $html ) === '' ) {
			return '';
		}

		$document = $this->createDOMDocument( $this->prepareHtml( $html ) );

		$root = $document->documentElement;

		$rootElement = new Element( $root );

		$result = $this->convertChildren( $rootElement );

		return $this->cleanUp( $result );
	}

	/**
	 *  Prepare HTML
	 *
	 * @param string $html The html to prepare.
	 * @return string The prepared html.
	 */
	protected function prepareHtml( $html ) {

		// replace &nbsp; with spaces.
		$html = str_replace( '&nbsp;', ' ', $html );
		$html = str_replace( "\xc2\xa0", ' ', $html );
		// replace \r\n to \n.
		$html = str_replace( "\r\n", "\n", $html );
		// remove \r.
		$html = str_replace( "\r", "\n", $html );
		// remove <script> and <style> tags.
		$html = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $html );
		// Convert <br> to \n.
		$html = preg_replace( '@<br[^>]*?/?>@si', "\n", $html );

		return trim( $html );
	}

	/**
	 * Create a DOMDocument from the given $html
	 *
	 * @param string $html The html to convert.
	 *
	 * @return DOMDocument The DOMDocument version of the html
	 */
	private function createDOMDocument( $html ) {
		$document = new DOMDocument();

		// use mb_convert_encoding for legacy versions of php.
		if ( ! Utils::phpAtLeast( '8.1' ) && mb_detect_encoding( $html, 'UTF-8', true ) ) {
			$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
		}

		$suppress_errors = $this->getConfig()->getOption( 'suppress_errors' );

		// If the HTML does not start with a tag, add <body> tag.
		if ( 0 !== strpos( trim( $html ), '<' ) ) {
			$html = '<body>' . $html . '</body>';
		}

		$header = '';
		// use char sets for modern versions of php.
		if ( Utils::phpAtLeast( '8.1' ) ) {
			// use specified char_set, or auto detect if not set.
			$char_set = $this->getConfig()->getOption( 'char_set', 'auto' );
			if ( 'auto' === $char_set ) {
				$char_set = mb_detect_encoding( $html );
			} elseif ( strpos( $char_set, ',' ) ) {
				mb_detect_order( $char_set );
				$char_set = mb_detect_encoding( $html );
			}
			// turn off error detection for Windows-1252 legacy html.
			if ( strpos( $char_set, '1252' ) ) {
				$suppress_errors = true;
			}
			$header = '<?xml version="1.0" encoding="' . $char_set . '">';
		}

		if ( $suppress_errors ) {
			// Suppress conversion errors.
			$document->strictErrorChecking = false;
			$document->recover             = true;
			$document->xmlStandalone       = true;
			libxml_use_internal_errors( true );
		}

		$document->loadHTML( $header . $html );

		if ( $suppress_errors ) {
			libxml_clear_errors();
		}

		return $document;
	}

	/**
	 * Convert Children
	 *
	 * Recursive function to drill into the DOM and convert each node into Markdown from the inside out.
	 *
	 * Finds children of each node and convert those to #text nodes containing their Markdown equivalent,
	 * starting with the innermost element and working up to the outermost element.
	 *
	 * @param ElementInterface $element The element to convert.
	 */
	private function convertChildren( ElementInterface $element ) {

		// Give converter a chance to inspect/modify the DOM before children are converted.
		$converter = $this->environment->getConverterByTag( $element->getTagName() );
		if ( is_callable( [ $converter, 'preConvert' ] ) ) {
			call_user_func( [ $converter, 'preConvert' ], $element );
		}

		// If the node has children, convert those first.
		if ( $element->hasChildren() ) {
			foreach ( $element->getChildren() as $child ) {
				$this->convertChildren( $child );
			}
		}

		// Now that child nodes have been converted, convert the original node.
		$output = $this->shouldConvert( $element ) ? $this->convertElement( $element ) : '';

		$element->setFinalOutput( $output );

		return $output;
	}

	/**
	 * Whether the element should be converted
	 *
	 * @param ElementInterface $element The element to check.
	 *
	 * @return boolean Whether the element should be converted.
	 */
	public function shouldConvert( ElementInterface $element ) {
		$shouldConvert = true;

		$elementsToRemove = $this->getConfig()->getOption( 'elements_to_remove', [] );

		// If the element is in the list of elements to remove, don't convert it.
		if ( in_array( $element->getTagName(), $elementsToRemove, true ) ) {
			$shouldConvert = false;
		}

		if ( $shouldConvert && $this->getConfig()->getOption( 'remove_display_none', true ) ) {
			$style = $element->getAttribute( 'style' );

			if ( ! empty( $style ) ) {
				$style = Utils::parseStyle( $style );
				if ( isset( $style['display'] ) && 'none' === $style['display'] ) {
					$shouldConvert = false;
				}
			}
		}

		$shouldConvertCb = $this->getConfig()->getOption( 'should_convert_cb', null );

		// Have shouldConvert callback the final say.
		if ( is_callable( $shouldConvertCb ) ) {
			$shouldConvertVal = call_user_func( $shouldConvertCb, $element, $shouldConvert );

			$shouldConvert = is_bool( $shouldConvertVal ) ? $shouldConvertVal : $shouldConvert;
		}

		return $shouldConvert;
	}

	/**
	 * Convert element
	 *
	 * Converts an individual node into a #text node containing a string of its Markdown equivalent.
	 *
	 * Example: An <h3> node with text content of 'Title' becomes a text node with content of '### Title'
	 *
	 * @param ElementInterface $element The element to convert.
	 *
	 * @return string The converted HTML as Markdown
	 */
	protected function convertElement( ElementInterface $element ) {
		$tag = $element->getTagName();

		$converter = $this->environment->getConverterByTag( $tag );

		return $converter->convert( $element );
	}

	/**
	 * Clean up the result
	 *
	 * @param string $input The input to clean up.
	 *
	 * @return string The clean text.
	 */
	protected function cleanUp( $input ) {
		$output = $input;

		// remove leading and trailing spaces on each line.
		$output = preg_replace( "/[ \t]*\n[ \t]*/im", "\n", $output );
		$output = preg_replace( "/ *\t */im", "\t", $output );

		$output = Utils::processPlaceholders( $output, 'replace' );

		// unarmor pre blocks.
		$output = str_replace( "\r", "\n", $output );

		// remove unnecessary empty lines.
		$output = preg_replace( "/\n\n\n*/im", "\n\n", $output );

		return trim( $output );
	}

	/**
	 * Pass a series of key-value pairs in an array; these will be passed
	 * through the config and set.
	 *
	 * @param array<string, mixed> $options Options to set.
	 *
	 * @return $this
	 */
	public function setOptions( $options ) {
		$config = $this->getConfig();

		foreach ( $options as $key => $option ) {
			$config->setOption( $key, $option );
		}

		return $this;
	}
}
