<?php
/**
 * WordPress Plugin Stub - Admin Class
 *
 * Handles all WordPress admin-specific functionality for the plugin.
 *
 * @package WordPress_Plugin_Stub
 * @author  Marcin Pietrzak <marcin@iworks.pl>
 * @license GPL-2.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_wordpress_plugin_stub_cron' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/class-wordpress-plugin-stub-posttype.php';

/**
 * Admin functionality for WordPress Plugin Stub.
 *
 * This class handles all admin-specific functionality including:
 * - Plugin settings and options management
 * - Admin assets registration
 * - Plugin meta links
 * - Admin interface rendering
 *
 * @since 1.0.0
 */
class iworks_wordpress_plugin_stub_cron extends iworks_wordpress_plugin_stub_base {

	/**
	 * Cron hooks mapping.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private array $hooks = array();

	/**
	 * Last sync time option name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private string $last_sync_time_option_name = 'last_sync_get_polls';

	/**
	 * Last sync answers option name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private string $last_sync_answers_option_name = 'last_sync_send_answers';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Register cron hooks.
		 */
		$this->hooks = array(
			'wordpress-plugin-stub/cron/hourly'     => array(
				'method'   => 'run_event_hourly',
				'interval' => 'hourly',
			),
			'wordpress-plugin-stub/cron/daily'      => array(
				'method'   => 'run_event_daily',
				'interval' => 'daily',
			),
			'wordpress-plugin-stub/cron/twicedaily' => array(
				'method'   => 'run_event_twicedaily',
				'interval' => 'twicedaily',
			),
			'wordpress-plugin-stub/cron/weekly'     => array(
				'method'   => 'run_event_weekly',
				'interval' => 'weekly',
			),
		);
		// Cron Hooks.
		foreach ( $this->hooks as $hook => $config ) {
			add_action( $hook, array( $this, $config['method'] ) );
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time(), $config['interval'], $hook );
			}
		}
	}

	/**
	 * Cron event hourly.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run_event_hourly() {
		do_action( 'wordpress-plugin-stub/cron/hourly/run' );
	}

	/**
	 * Cron event twicedaily.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run_event_twicedaily() {
		do_action( 'wordpress-plugin-stub/cron/twicedaily/run' );
	}

	/**
	 * Cron event daily.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run_event_daily() {
		do_action( 'wordpress-plugin-stub/cron/daily/run' );
	}

	/**
	 * Cron event weekly.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run_event_weekly() {
		do_action( 'wordpress-plugin-stub/cron/weekly/run' );
	}
}
