<?php
/**
 * Tests for the HtmlConverter class.
 *
 * @package WPTelegram\FormatText
 *
 * @phpcs:disable Squiz.Commenting.ClassComment,Squiz.Commenting.FunctionComment
 */

namespace WPTelegram\FormatText\Tests;

use PHPUnit\Framework\TestCase;
use WPTelegram\FormatText\HtmlConverter;

require_once __DIR__ . '/Utils.php';

final class HtmlConverterTest extends TestCase {

	public function testCanBeCreatedWithoutOptions() {
		$this->assertInstanceOf(
			HtmlConverter::class,
			new HtmlConverter()
		);
	}

	public function testWhitespaceReturnsEmptyString() {
		$whitespace = " \t\n ";
		$this->assertEquals(
			'',
			( new HtmlConverter() )->convert( $whitespace )
		);
	}

	public function testPlainTextReturnsAsIs() {
		$text = 'Hello World!';
		$this->assertEquals(
			$text,
			( new HtmlConverter() )->convert( $text )
		);
	}

	public function testWithInputFiles() {
		$files = Utils::getInputFiles();

		foreach ( $files as $file ) {
			$input = file_get_contents( $file );

			foreach ( Utils::FORMATS as $format ) {

				$output_path = Utils::getTestOutputPath( $file, $format );

				$expected = file_get_contents( $output_path );

				$this->assertEquals(
					$expected,
					( new HtmlConverter( [ 'format_to' => $format ] ) )->convert( $input )
				);
			}
		}
	}
}
