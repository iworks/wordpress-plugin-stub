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
 * Define static options and constants for the plugin
 */
// Define plugin version constant
define( 'IWORKS_WORDPRESS_PLUGIN_STUB_VERSION', 'PLUGIN_VERSION' );
// Define prefix for all plugin options and functions
define( 'IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX', 'iworks_wordpress-plugin-stub_' );
// Get the base directory path
$base = __DIR__;
// Set vendor directory path (where core classes are located)
$vendor = $base . '/includes';

/**
 * Load the main plugin class if it doesn't exist
 * This is the core class that handles all plugin functionality
 */
if ( ! class_exists( 'iworks_wordpress_plugin_stub' ) ) {
	// Load the main plugin class from the includes directory
	require_once $vendor . '/iworks/class-wordpress-plugin-stub.php';
}

/**
 * Load configuration options
 * This file contains all plugin configuration settings
 */
require_once $base . '/etc/options.php';

/**
 * Load the options class if it doesn't exist
 * This class handles all plugin options and settings
 */
if ( ! class_exists( 'iworks_options' ) ) {
	// Load the options class from the includes directory
	require_once $vendor . '/iworks/options/options.php';
}

/**
 * Post Type Filters
 * These filters control which custom post types are loaded by the plugin
 * Each filter returns false by default - change to true to enable the post type
 */
// FAQ post type
add_filter( 'wordpress-plugin-stub/load/posttype/faq', '__return_false' );
// Hero post type
add_filter( 'wordpress-plugin-stub/load/posttype/hero', '__return_false' );
// Opinion post type
add_filter( 'wordpress-plugin-stub/load/posttype/opinion', '__return_false' );
// Custom page post type
add_filter( 'wordpress-plugin-stub/load/posttype/page', '__return_false' );
// Person post type
add_filter( 'wordpress-plugin-stub/load/posttype/person', '__return_false' );
// Custom post post type
add_filter( 'wordpress-plugin-stub/load/posttype/post', '__return_false' );
// Project post type
add_filter( 'wordpress-plugin-stub/load/posttype/project', '__return_false' );
// Promo post type
add_filter( 'wordpress-plugin-stub/load/posttype/promo', '__return_false' );
// Publication post type
add_filter( 'wordpress-plugin-stub/load/posttype/publication', '__return_false' );

/**
 * Initialize and get plugin options
 * This function creates and returns the options object
 *
 * @return iworks_options The plugin options object
 */
function iworks_wordpress_plugin_stub_get_options() {
	// Use global variable to store options object
	global $iworks_wordpress_plugin_stub_options;

	// Return existing options object if it exists
	if ( is_object( $iworks_wordpress_plugin_stub_options ) ) {
		return $iworks_wordpress_plugin_stub_options;
	}

	// Create new options object if it doesn't exist
	$iworks_wordpress_plugin_stub_options = new iworks_options();

	// Set the function name for options
	$iworks_wordpress_plugin_stub_options->set_option_function_name( 'iworks_wordpress_plugin_stub_options' );
	// Set the option prefix for all plugin options
	$iworks_wordpress_plugin_stub_options->set_option_prefix( IWORKS_WORDPRESS_PLUGIN_STUB_PREFIX );

	// Set the plugin file name if the method exists
	if ( method_exists( $iworks_wordpress_plugin_stub_options, 'set_plugin' ) ) {
		$iworks_wordpress_plugin_stub_options->set_plugin( basename( __FILE__ ) );
	}

	// Initialize the options
	$iworks_wordpress_plugin_stub_options->options_init();

	// Return the options object
	return $iworks_wordpress_plugin_stub_options;
}

// Initialize the main plugin class
$iworks_wordpress_plugin_stub = new iworks_wordpress_plugin_stub();

/**
 * Register plugin activation and deactivation hooks
 */
// Register activation hook to run when plugin is activated
register_activation_hook( __FILE__, array( $iworks_wordpress_plugin_stub, 'register_activation_hook' ) );
// Register deactivation hook to run when plugin is deactivated
register_deactivation_hook( __FILE__, array( $iworks_wordpress_plugin_stub, 'register_deactivation_hook' ) );
