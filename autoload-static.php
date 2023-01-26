<?php
/**
 * Autoloader.
 *
 * @package WPTelegram\FormatText
 */

spl_autoload_register( 'wptelegram_format_text_autoload' );

/**
 * Autoloads files with WPTelegram\FormatText classes when needed
 *
 * @param  string $className Name of the class being requested.
 *
 * @return void
 */
function wptelegram_format_text_autoload( string $className ) {
	$namespace = 'WPTelegram\FormatText';

	if ( 0 !== strpos( $className, $namespace ) ) {
		return;
	}

	$className = str_replace( $namespace, '', $className );
	$className = str_replace( '\\', DIRECTORY_SEPARATOR, $className );

	$path = __DIR__ . DIRECTORY_SEPARATOR . 'src' . $className . '.php';

	include_once $path;
}
