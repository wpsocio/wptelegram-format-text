<?php
/**
 * Utilities for converters.
 *
 * @package WPTelegram\FormatText\Converter
 */

namespace WPTelegram\FormatText\Converter;

/**
 * Class Utils
 */
class Utils {

	public const PLACEHOLDERS = [
		'{:space:}' => ' ',
		'{:tab:}'   => "\t",
	];

	/**
	 * It processes the pre-defined placeholders in the given text.
	 *
	 * @param string $text The text to process.
	 * @param string $action 'add' or 'replace'.
	 *
	 * @return string The processed text.
	 */
	public static function processPlaceholders( $text, $action = 'replace' ) {

		$placeholders = array_keys( self::PLACEHOLDERS );
		$values       = array_values( self::PLACEHOLDERS );

		if ( 'add' === $action ) {
			$output = str_replace( $values, $placeholders, $text );
		} else {
			$output = str_replace( $placeholders, $values, $text );
		}

		return $output;
	}

	/**
	 * Decode HTML entities.
	 *
	 * @param string $value - The value to decode.
	 *
	 * @return string - The decoded value.
	 */
	public static function decodeHtmlEntities( $value ) {

		return html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Convert special characters to HTML entities.
	 *
	 * @param string $value The string to be converted.
	 *
	 * @return string The converted string.
	 */
	public static function htmlSpecialChars( $value ) {

		return htmlspecialchars( $value, ENT_NOQUOTES, 'UTF-8' );
	}
}
