<?php
/*
Plugin Name: WordPress Plugin Stub
Text Domain: wordpress-plugin-stub
Plugin URI: PLUGIN_URI
Description: PLUGIN_TAGLINE
Version: PLUGIN_VERSION
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

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
defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/**
 * static options
 */
define( 'IWORKS_WORDPRESS_PLUGIN_STUB_VERSION', 'PLUGIN_VERSION' );
define( 'IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX', 'iworks_wordpress-plugin-stub_' );
$base   = dirname( __FILE__ );
$vendor = $base . '/includes';

/**
 * require: Iworkswordpress-plugin-stub Class
 */
if ( ! class_exists( 'iworks_wordpress_plugin_stub' ) ) {
	require_once $vendor . '/iworks/class-wordpress-plugin-stub.php';
}
/**
 * configuration
 */
require_once $base . '/etc/options.php';
/**
 * require: IworksOptions Class
 */
if ( ! class_exists( 'iworks_options' ) ) {
	require_once $vendor . '/iworks/options/options.php';
}
/**
 * load posttypes - change to `__return_true' to load
 */
add_filter( 'wordpress-plugin-stub/load/posttype/faq', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/hero', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/opinion', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/page', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/person', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/post', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/project', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/promo', '__return_false' );
add_filter( 'wordpress-plugin-stub/load/posttype/publication', '__return_false' );

/**
 * load options
 */
function iworks_wordpress_plugin_stub_get_options() {
	global $iworks_wordpress_plugin_stub_options;
	if ( is_object( $iworks_wordpress_plugin_stub_options ) ) {
		return $iworks_wordpress_plugin_stub_options;
	}
	$iworks_wordpress_plugin_stub_options = new iworks_options();
	$iworks_wordpress_plugin_stub_options->set_option_function_name( 'iworks_wordpress_plugin_stub_options' );
	$iworks_wordpress_plugin_stub_options->set_option_prefix( IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX );
	if ( method_exists( $iworks_wordpress_plugin_stub_options, 'set_plugin' ) ) {
		$iworks_wordpress_plugin_stub_options->set_plugin( basename( __FILE__ ) );
	}
	$iworks_wordpress_plugin_stub_options->init();
	return $iworks_wordpress_plugin_stub_options;
}

$iworks_wordpress_plugin_stub = new iworks_wordpress_plugin_stub();

/**
 * install & uninstall
 */
register_activation_hook( __FILE__, array( $iworks_wordpress_plugin_stub, 'register_activation_hook' ) );
register_deactivation_hook( __FILE__, array( $iworks_wordpress_plugin_stub, 'register_deactivation_hook' ) );
