<?php
/**
 * Class for custom Post Type: HERO
 *
 * @since 1.0.0

Copyright CURRENT_YEAR-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

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

require_once 'class-iworks-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_hero extends iworks_wordpress_plugin_stub_posttype {

	/**
	 * Post type name
	 *
	 * @since 1.0.0
	 * @var string $post_type Post type identifier
	 */
	protected string $post_type = 'hero';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->register_class_custom_posttype_name( $this->post_type );
		/**
		 * WordPress Hooks
		 */
		add_shortcode( 'iworks-heroes', array( $this, 'get_list' ) );
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
	}

	/**
	 * Get post list
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $content current content
	 *
	 * @return string $content
	 */
	public function get_list( $atts, $content = '' ) {
		$args = shortcode_atts(
			array(
				'orderby'        => 'rand',
				'posts_per_page' => 2,
				'post_status'    => 'publish',
				'title'          => esc_html__( 'Learn what our employees are saying.', 'wordpress-plugin-stub' ),
				'subtitle'       => esc_html__( 'Become one of them!', 'wordpress-plugin-stub' ),
			),
			$atts,
			'iworks/wordpress-plugin-stub/hero/list'
		);
		return $this->shortcode_list( $args, $content );
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_taxonomy() {}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Heroes', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Hero', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Heroes', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Heroes', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Heroes', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Heroes', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New Hero', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New Hero', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit Hero', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update Hero', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View Hero', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View Hero', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search Hero', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Hero list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Hero list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Hero', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Heroes', 'wordpress-plugin-stub' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => 'edit.php',
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'excerpt', 'page-attributes' ),
		);
		$this->register_post_type( $this->post_type, $args );
	}
}
