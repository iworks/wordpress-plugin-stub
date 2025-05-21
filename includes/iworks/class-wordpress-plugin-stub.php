<?php
/*

Copyright 2025-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'iworks_wordpress_plugin_stub' ) ) {
	return;
}

require_once __DIR__ . '/class-wordpress-plugin-stub-base.php';

class iworks_wordpress_plugin_stub extends iworks_wordpress_plugin_stub_base {


	/**
	 * Plugin Objects
	 *
	 * @since 1.0.0
	 */
	private array $objects = array();

	public function __construct() {
		parent::__construct();
		$this->version = 'PLUGIN_VERSION';
		/**
		 * post types
		 */
		include_once 'class-wordpress-plugin-stub-posttypes.php';
		new iworks_wordpress_plugin_posttypes();
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'action_init_settings' ) );
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
	 * Initialize plugin
	 *
	 * @since 1.0.0
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
	 * register_activation_hook
	 *
	 * @since 1.0.0
	 */
	public function register_activation_hook() {
		$this->db_install();
		$this->check_option_object();
		$this->options->activate();
		do_action( 'iworks/wordpress-plugin-stub/register_activation_hook' );
	}

	/**
	 * register_deactivation_hook
	 *
	 * @since 1.0.0
	 */
	public function register_deactivation_hook() {
		$this->check_option_object();
		$this->options->deactivate();
		do_action( 'iworks/wordpress-plugin-stub/register_deactivation_hook' );
	}

	/**
	 * db install (if needed)
	 *
	 * @since 1.0.0
	 */
	private function db_install() {
	}
}
