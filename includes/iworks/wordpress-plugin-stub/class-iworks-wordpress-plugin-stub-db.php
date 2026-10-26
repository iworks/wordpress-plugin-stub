<?php
/**
 * WordPress Plugin Stub - Database Class
 *
 * Handles all database operations for the WordPress Plugin Stub plugin.
 *
 * @package WordPress_Plugin_Stub
 * @author  Marcin Pietrzak <marcin@iworks.pl>
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_wordpress_plugin_stub_db' ) ) {
	return;
}

require_once dirname( __DIR__ ) . '/class-iworks-wordpress-plugin-stub-base.php';

/**
 * Database functionality for WordPress Plugin Stub.
 *
 * This class handles all database-specific functionality including:
 * - Database table creation and updates
 * - Database version tracking
 * - Table registration with $wpdb
 * - Table cleanup on uninstall
 *
 * @since 1.0.0
 */
class iworks_wordpress_plugin_stub_db extends iworks_wordpress_plugin_stub_base {

	/**
	 * Option name for storing the database version.
	 *
	 * This is used to track the current version of the database schema
	 * and to determine if database updates are needed.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $_db_version = 'wordpress_plugin_stub_db_version';

	/**
	 * Array of database table names without prefix.
	 *
	 * Contains the names of all custom database tables used by the plugin.
	 * These names will be automatically prefixed with the WordPress table prefix.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	private array $table_names = array();
	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->register_tables();
		/**
		 * WordPress hooks.
		 */
		add_action( 'shutdown', array( $this, 'db_install' ) );
		/**
		 * WordPress Plugin Stub hooks.
		 */
		add_action( 'iworks/wordpress-plugin-stub/register_uninstall_hook', array( $this, 'drop_tables' ) );
		add_action( 'iworks/wordpress-plugin-stub/register_activation_hook', array( $this, 'db_install' ) );
	}

	/**
	 * Register plugin tables with $wpdb.
	 *
	 * This allows the tables to be referenced as $wpdb->{table_name} throughout the plugin.
	 * Tables are registered with the WordPress database prefix.
	 *
	 * @since 1.0.0
	 */
	private function register_tables() {
		global $wpdb;

		// Register each table.
		foreach ( $this->table_names as $key => $table_name ) {
			$wpdb->$table_name = $wpdb->prefix . $table_name;
		}
	}

	/**
	 * Create the database table if it doesn't exist.
	 *
	 * @since 1.0.0
	 */
	public function db_install() {
		/**
		 * Get current DB version.
		 */
		$db_version = intval( get_option( $this->_db_version ) );
		if ( 1 > $db_version ) {
			add_option( $this->_db_version, 0, '', 'no' );
		}
		/**
		 * chemistry table
		 */
		$version_to_update = 2;
		if ( $db_version < $version_to_update ) {
			//$sql = "CREATE TABLE IF NOT EXISTS X (id bigint(20) unsigned NOT NULL AUTO_INCREMENT)";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			//dbDelta( $sql );
			update_option( $this->_db_version, $version_to_update );
			$db_version = intval( get_option( $this->_db_version ) );
		}
	}

	/**
	 * Drop all plugin database tables
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function drop_tables() {
		global $wpdb;

		// Disable foreign key checks to avoid issues with table dependencies
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

		// Drop each table
		foreach ( $this->table_names as $table_name ) {
			$sql   = 'DROP TABLE IF EXISTS %s';
			$query = $wpdb->prepare( $sql, $wpdb->$table_name );
			$wpdb->query( $query );
		}

		// Re-enable foreign key checks
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1' );

		// Delete the database version option
		delete_option( $this->_db_version );
	}
}
