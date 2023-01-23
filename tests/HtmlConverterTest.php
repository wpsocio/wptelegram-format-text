<?php
/**
 * Tests for the HtmlConverter class.
 *
 * @package WPTelegram\FormatText
 *
 * @phpcs:disable Squiz.Commenting.ClassComment,Squiz.Commenting.FunctionComment
 */

use PHPUnit\Framework\TestCase;
use WPTelegram\FormatText\HtmlConverter;

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
}
