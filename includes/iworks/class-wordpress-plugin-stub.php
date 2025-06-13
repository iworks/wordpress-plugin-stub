<?php
/**
 * Main plugin class file.
 *
 * @package WordPress_Plugin_Stub
 * @author Marcin Pietrzak <marcin@iworks.pl>
 * @copyright 2025-PLUGIN_TILL_YEAR Marcin Pietrzak
 * @license GPL-3.0-or-later
 * @link https://iworks.pl/
 *
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_wordpress_plugin_stub' ) ) {
	return;
}

require_once __DIR__ . '/class-wordpress-plugin-stub-base.php';

/**
 * Main plugin class.
 *
 * This class initializes the plugin and loads all necessary components.
 *
 * @since 1.0.0
 */
class iworks_wordpress_plugin_stub extends iworks_wordpress_plugin_stub_base {

	/**
	 * Plugin objects container.
	 *
	 * @since 1.0.0
	 * @var array $objects Array to store plugin objects.
	 */
	private array $objects = array();

	/**
	 * Class constructor.
	 *
	 * Initializes the plugin by setting up hooks and loading required files.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->version = 'PLUGIN_VERSION';
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'action_init_settings' ) );
		/**
		 * post types
		 */
		$filename = $this->includes_directory . '/class-iworks-wordpress-plugin-stub-posttypes.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_wordpress_plugin_posttypes();
		}
		/**
		 * load github class
		 */
		$filename = $this->includes_directory . '/class-iworks-wordpress-plugin-stub-github.php';
		if ( is_file( $filename ) ) {
			include_once $filename;
			new iworks_wordpress_plugin_stub_github();
		}
		/**
		 * admin
		 */
		if ( is_admin() ) {
			$filename = $this->includes_directory . '/class-iworks-wordpress-plugin-stub-wp-admin.php';
			if ( is_file( $filename ) ) {
				include_once $filename;
				new iworks_wordpress_plugin_stub_wp_admin();
			}
		}
		/**
		 * is active?
		 */
		add_filter( 'wordpress-plugin-stub/is_active', '__return_true' );
	}

	/**
	 * Initialize plugin settings and assets.
	 *
	 * Handles the initialization of plugin settings and enqueues frontend assets.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function action_init_settings() {
		/**
		 * options
		 */
		if ( is_admin() ) {
		} else {
			$file = 'assets/styles/wordpress-plugin-stub-frontend' . $this->dev . '.css';
			wp_enqueue_style( 'wordpress-plugin-stub', plugins_url( $file, $this->base ), array(), $this->get_version( $file ) );
		}
	}

	/**
	 * Plugin activation hook.
	 *
	 * Handles database installation and option initialization
	 * when the plugin is activated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_activation_hook() {
		$this->db_install();
		$this->check_option_object();
		$this->options->activate();
		do_action( 'iworks/wordpress-plugin-stub/register_activation_hook' );
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * Handles cleanup tasks when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_deactivation_hook() {
		$this->check_option_object();
		$this->options->deactivate();
		do_action( 'iworks/wordpress-plugin-stub/register_deactivation_hook' );
	}

	/**
	 * Database installation method.
	 *
	 * Handles the creation of required database tables.
	 * Currently empty as it's a stub implementation.
	 *
	 * @since 1.0.0
	 * @return void
	 * @todo Implement database table creation if needed.
	 */
	private function db_install() {
	}
}
