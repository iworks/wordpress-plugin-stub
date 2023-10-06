<?php
/*

Copyright 2018-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

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

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( class_exists( 'iworks_wordpress_plugin_stub' ) ) {
	return;
}

require_once( dirname( __FILE__ ) . '/class-wordpress-plugin-stub-base.php' );

class iworks_wordpress_plugin_stub extends iworks_wordpress_plugin_stub_base {

	private $capability;

	public function __construct() {
		parent::__construct();
		$this->version    = 'PLUGIN_VERSION';
		$this->capability = apply_filters( 'iworks_wordpress_plugin_stub_capability', 'manage_options' );
		/**
		 * admin init
		 */
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		/**
		 * is active?
		 */
		add_filter( 'wordpress-plugin-stub/is_active', '__return_true' );
	}

	public function admin_init() {
		iworks_wordpress_plugin_stub_options_init();
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
		wp_register_script( 'select2', plugins_url( 'assets/externals/select2/js/select2.full.min.js', $this->base ), array(), '4.0.3' );
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

	public function init() {
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
				$links[] = '<a href="admin.php?page=' . $this->dir . '/admin/index.php">' . __( 'Settings' ) . '</a>';
			}
			/* start:free */
			$links[] = '<a href="http://iworks.pl/donate/wordpress-plugin-stub.php">' . __( 'Donate' ) . '</a>';
			/* end:free */
		}
		return $links;
	}

}
