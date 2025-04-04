<?php
/*

Copyright 2025-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
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

require_once( dirname( __FILE__ ) . '/class-wordpress-plugin-stub-base.php' );

class iworks_wordpress_plugin_stub extends iworks_wordpress_plugin_stub_base {

	private $capability;

	/**
	 * Plugin Objects
	 *
	 * @since 1.0.0
	 */
	private array $objects = array();

	public function __construct() {
		parent::__construct();
		$this->version    = 'PLUGIN_VERSION';
		$this->capability = apply_filters( 'iworks_wordpress_plugin_stub_capability', 'manage_options' );
		/**
		 * post types
		 */
		include_once 'class-wordpress-plugin-stub-posttypes.php';
		new iworks_wordpress_plugin_posttypes();
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
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
		 * is active?
		 */
		add_filter( 'wordpress-plugin-stub/is_active', '__return_true' );
	}

	public function action_admin_init() {
		$this->check_option_object();
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		/**
		 * off on not wordpress-plugin-stub pages
		 */
		$re = sprintf( '/%s_/', __CLASS__ );
		if ( ! preg_match( $re, $screen->id ) ) {
			return;
		}
		/**
		 * datepicker
		 */
		$file = 'assets/externals/datepicker/css/jquery-ui-datepicker.css';
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'jquery-ui-datepicker', $file, false, '1.12.1' );
		/**
		 * select2
		 */
		$file = 'assets/externals/select2/css/select2.min.css';
		$file = plugins_url( $file, $this->base );
		wp_register_style( 'select2', $file, false, '4.0.3' );
		/**
		 * Admin styles
		 */
		$file    = sprintf( '/assets/styles/admin%s.css', $this->dev );
		$version = $this->get_version( $file );
		$file    = plugins_url( $file, $this->base );
		wp_register_style( 'admin-wordpress-plugin-stub', $file, array( 'jquery-ui-datepicker', 'select2' ), $version );
		wp_enqueue_style( 'admin-wordpress-plugin-stub' );
		/**
		 * select2
		 */
		wp_register_script(
			'select2',
			plugins_url(
				'assets/externals/select2/js/select2.full.min.js',
				$this->base
			),
			array(),
			'4.0.3',
			array(
				'in_footer' => true,
			)
		);
		/**
		 * Admin scripts
		 */
		$files = array(
			'wordpress-plugin-stub-admin' => sprintf( 'assets/scripts/admin/admin%s.js', $this->dev ),
		);
		if ( '' == $this->dev ) {
			$files = array(
				'wordpress-plugin-stub-admin-datepicker' => 'assets/scripts/admin/src/datepicker.js',
				'wordpress-plugin-stub-admin-select2'    => 'assets/scripts/admin/src/select2.js',
				'wordpress-plugin-stub-admin-media-library' => 'assets/scripts/admin/src/media-library.js',
			);
		}
		$deps = array(
			'jquery-ui-datepicker',
			'select2',
		);
		foreach ( $files as $handle => $file ) {
			wp_register_script(
				$handle,
				plugins_url( $file, $this->base ),
				$deps,
				$this->get_version(),
				true
			);
			wp_enqueue_script( $handle );
		}
		/**
		 * JavaScript messages
		 *
		 * @since 1.0.0
		 */
		$data = array(
			'messages' => array(),
			'nonces'   => array(),
			'user_id'  => get_current_user_id(),
		);
		wp_localize_script(
			'wordpress_plugin_stub_admin',
			__CLASS__,
			apply_filters( 'wp_localize_script_wordpress_plugin_stub_admin', $data )
		);
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
		$this->check_option_object();
		if ( is_admin() ) {
		} else {
			$file = 'assets/styles/wordpress_plugin_stub' . $this->dev . '.css';
			wp_enqueue_style( 'wordpress-plugin-stub', plugins_url( $file, $this->base ), array(), $this->get_version( $file ) );
		}
	}

	/**
	 * Plugin row data
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/wordpress-plugin-stub.php' == $file ) {
			if ( ! is_multisite() && current_user_can( $this->capability ) ) {
				$links[] = sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						add_query_arg(
							array(
								'page' => $this->dir . '/admin/index.php',
							),
							admin_url( 'admin.php' )
						)
					),
					esc_html__( 'Settings', 'wordpress-plugin-stub' )
				);
			}
			/* start:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'wordpress-plugin-stub',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://ko-fi.com/iworks'
					)
				),
				esc_html__( 'Donate', 'wordpress-plugin-stub' )
			);
			/* end:free */
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'utm_source' => 'wordpress-plugin-stub',
							'utm_medium' => 'plugin-row-donate-link',
						),
						'https://github.com/iworks.pl/wordpress-plugin-stub'
					)
				),
				esc_html__( 'GitHub', 'wordpress-plugin-stub' )
			);
		}
		return $links;
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
