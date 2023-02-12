<?php
/**
 * Utils for tests.
 *
 * @package WPTelegram\FormatText
 *
 */

namespace WPTelegram\FormatText\Tests;

class Utils {

	const FORMATS = [ 'HTML', 'Markdown', 'MarkdownV2', 'text' ];

	/**
	 * Get the path to the output file.
	 *
	 * @param string $inputFile The input file path.
	 * @param string $format    The format.
	 *
	 * @return string The output file path.
	 */
	public static function getTestOutputPath( string $inputFile, string $format ): string {

		$output_path = str_replace( '/input', '/output', $inputFile );

		$output_path = preg_replace( '/\.html$/iu', '-' . strtolower( $format ) . '.txt', $output_path );

		return self::normalizePath( $output_path );
	}

	/**
	 * Get normalized path, like realpath() for non-existing path or file
	 *
	 * @param string $path      Path to be normalized.
	 * @param string $separator Separator to be used.
	 * @return string Normalized path.
	 */
	public function normalizePath( string $path, string $separator = '\\/' ) {
		// Remove any kind of funky unicode whitespace
		$normalized = preg_replace( '#\p{C}+|^\./#u', '', $path );

		// Path remove self referring paths ("/./").
		$normalized = preg_replace( '#/\.(?=/)|^\./|\./$#', '', $normalized );

		// Regex for resolving relative paths
		$regex = '#\/*[^/\.]+/\.\.#Uu';

		while ( preg_match( $regex, $normalized ) ) {
			$normalized = preg_replace( $regex, '', $normalized );
		}

		if ( preg_match( '#/\.{2}|\.{2}/#', $normalized ) ) {
			throw new \LogicException( 'Path is outside of the defined root, path: [' . $path . '], resolved: [' . $normalized . ']' );
		}

		return trim( $normalized, $separator );
	}
}
