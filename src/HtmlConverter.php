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
				'suppress_errors'   => true, // Set to false to show warnings when loading malformed HTML.
				'list_item_style'   => '-', // Set the default character for each <li> in a <ul>. Can be '-', '*', or '+'.
				'table_caption_pos' => 'top', // Set to 'top' or 'bottom' to show <caption> content before or after table, null to suppress.
				'format_to'         => 'text', // Set to 'HTML', 'Markdown' or 'MarkdownV2'.
				'relative_links'    => 'clean', // Set to 'preserve' to preserve relative links.
				'table_cell_sep'    => ' | ', // Set the default separator for each <td> and <th>.
				'table_row_sep'     => "\n", // Set the default separator for each <tr>.
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

		return $html;
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

		if ( $this->getConfig()->getOption( 'suppress_errors' ) ) {
			// Suppress conversion errors.
			libxml_use_internal_errors( true );
		}

		// Hack to load utf-8 HTML.
		$document->loadHTML( '<?xml encoding="UTF-8">' . $html );
		$document->encoding = 'UTF-8';

		if ( $this->getConfig()->getOption( 'suppress_errors' ) ) {
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
		$output = $this->convertElement( $element );

		$element->setFinalOutput( $output );

		return $output;
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
