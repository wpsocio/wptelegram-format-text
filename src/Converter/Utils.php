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

	const PLACEHOLDERS = [
		'{:space:}' => ' ',
		'{:tab:}'   => "\t",
	];

	/**
	 * It processes the pre-defined placeholders in the given text.
	 *
	 * @param string $text   The text to process.
	 * @param string $action The action to take - 'add' or 'replace'.
	 *
	 * @return string The processed text.
	 */
	public static function processPlaceholders( string $text, string $action = 'replace' ) {

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
	 * @param string $value The value to decode.
	 *
	 * @return string The decoded value.
	 */
	public static function decodeHtmlEntities( string $value ) {

		return html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Convert special characters to HTML entities.
	 *
	 * @param string $value The string to be converted.
	 *
	 * @return string The converted string.
	 */
	public static function htmlSpecialChars( string $value ) {

		return htmlspecialchars( $value, ENT_NOQUOTES, 'UTF-8' );
	}

	/**
	 * Check if PHP version is at least $version.
	 *
	 * @param  string $version PHP version string to compare.
	 *
	 * @return boolean Result of comparison check.
	 */
	public static function phpAtLeast( string $version ) {
		return version_compare( PHP_VERSION, $version, '>=' );
	}

	/**
	 * Parse style attribute
	 *
	 * @param string $style The style attribute.
	 *
	 * @return array The style attribute as an array.
	 */
	public static function parseStyle( string $style ) {
		$style_array = [];

		$parts = explode( ';', $style );

		foreach ( $parts as $part ) {
			$part = trim( $part );

			if ( empty( $part ) ) {
				continue;
			}

			list( $key, $value ) = array_map( 'trim', explode( ':', $part, 2 ) );

			if ( ! empty( $key ) ) {
				$style_array[ $key ] = $value;
			}
		}

		return $style_array;
	}
}
