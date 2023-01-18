<?php
/**
 * The loader.
 *
 * The class which loads the latest version of the library.
 *
 * @link       https://wpsocio.com
 * @since      1.0.0
 *
 * @package    WPTelegram\FormatText
 * @subpackage WPTelegram\FormatText
 */

namespace WPTelegram\FormatText;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( __NAMESPACE__ . '\WPLoader_1_0_0', false ) ) {
	/**
	 * Handles checking for and loading the newest version of WPTelegram\FormatText
	 *
	 * Inspired from CMB2 loading technique
	 * to ensure that only the latest version is loaded
	 *
	 * @see https://github.com/CMB2/CMB2/blob/v2.3.0/init.php
	 *
	 * @since  1.0.0
	 *
	 * @category  WordPress_Plugin Addon
	 * @package   WPTelegram\FormatText
	 * @author    WPTelegram team
	 * @license   GPL-2.0+
	 * @link      https://t.me/WPTelegram
	 */
	class WPLoader_1_0_0 {

		/**
		 * Current version number
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		const VERSION = '1.0.0';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 1.0.0
		 */
		const PRIORITY = 9999;

		/**
		 * Single instance of the WPLoader_1_0_0 object
		 *
		 * @var WPLoader_1_0_0
		 */
		public static $single_instance = null;

		/**
		 * Creates/returns the single instance WPLoader_1_0_0 object
		 *
		 * @since  1.0.0
		 * @return WPLoader_1_0_0 Single instance object
		 */
		public static function initiate() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
			return self::$single_instance;
		}

		/**
		 * Starts the version checking process.
		 * Creates WPTELEGRAM_FORMAT_TEXT_LOADED definition for early detection by other scripts
		 *
		 * Hooks WPTelegram\FormatText inclusion to the after_setup_theme hook on a high priority which decrements
		 * (increasing the priority) with each version release.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			/**
			 * A constant you can use to check if WPTelegram\FormatText is loaded
			 * for your plugins/themes with WPTelegram\FormatText dependency
			 */
			if ( ! defined( 'WPTELEGRAM_FORMAT_TEXT_LOADED' ) ) {
				define( 'WPTELEGRAM_FORMAT_TEXT_LOADED', self::PRIORITY );
			}

			/**
			 * Use after_setup_theme hook instead of init
			 * to make the API library available during init
			 */
			add_action( 'after_setup_theme', [ $this, 'init_wptelegram_format_text' ], self::PRIORITY );
		}

		/**
		 * A final check if WPTelegram\FormatText exists before kicking off our WPTelegram\FormatText loading.
		 * WPTELEGRAM_FORMAT_TEXT_VERSION constant is set at this point.
		 *
		 * @since  1.0.0
		 */
		public function init_wptelegram_format_text() {
			if ( class_exists( FormatText\API::class, false ) ) {
				return;
			}

			if ( ! defined( 'WPTELEGRAM_FORMAT_TEXT_VERSION' ) ) {
				define( 'WPTELEGRAM_FORMAT_TEXT_VERSION', self::VERSION );
			}

			if ( ! defined( 'WPTELEGRAM_FORMAT_TEXT_DIR' ) ) {
				define( 'WPTELEGRAM_FORMAT_TEXT_DIR', dirname( __FILE__ ) );
			}

			// Now kick off the class autoloader.
			spl_autoload_register( [ __CLASS__, 'wptelegram_format_text_autoload_classes' ] );
		}

		/**
		 * Autoloads files with WPTelegram\FormatText classes when needed
		 *
		 * @since  1.0.0
		 * @param  string $class_name Name of the class being requested.
		 */
		public static function wptelegram_format_text_autoload_classes( $class_name ) {
			$namespace = 'WPTelegram\FormatText';

			if ( 0 !== strpos( $class_name, $namespace ) ) {
				return;
			}

			$class_name = str_replace( $namespace, '', $class_name );
			$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );

			$path = WPTELEGRAM_FORMAT_TEXT_DIR . DIRECTORY_SEPARATOR . 'src' . $class_name . '.php';

			include_once $path;
		}
	}
	WPLoader_1_0_0::initiate();
}
