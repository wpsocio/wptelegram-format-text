<?php
/**
 * Configuration.
 *
 * @package WPTelegram\FormatText
 */

namespace WPTelegram\FormatText;

/**
 * Class Configuration
 */
class Configuration {

	/**
	 * Configuration.
	 *
	 * @var array<string, mixed>
	 */
	protected $config;

	/**
	 * Configuration constructor.
	 *
	 * @param array<string, mixed> $config Configuration.
	 */
	public function __construct( $config = [] ) {
		$this->config = $config;
	}

	/**
	 * Merge configuration.
	 *
	 * @param array<string, mixed> $config Configuration.
	 */
	public function merge( $config = [] ) {
		$this->config = array_replace_recursive( $this->config, $config );
	}

	/**
	 * Replace configuration.
	 *
	 * @param array<string, mixed> $config Configuration.
	 */
	public function replace( $config = [] ) {
		$this->config = $config;
	}

	/**
	 * Set option.
	 *
	 * @param string $key Key.
	 * @param mixed  $value Value.
	 */
	public function setOption( $key, $value ) {
		$this->config[ $key ] = $value;
	}

	/**
	 * Get option.
	 *
	 * @param string|null $key     Key.
	 * @param mixed|null  $default Default value.
	 *
	 * @return mixed|null
	 */
	public function getOption( $key = null, $default = null ) {
		if ( null === $key ) {
			return $this->config;
		}

		if ( ! isset( $this->config[ $key ] ) ) {
			return $default;
		}

		return $this->config[ $key ];
	}
}
