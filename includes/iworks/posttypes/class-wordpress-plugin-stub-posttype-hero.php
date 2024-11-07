<?php
/**
 * Class for custom Post Type: HERO
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_hero extends iworks_wordpress_plugin_stub_posttype_base {

	public function __construct() {
		parent::__construct();
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->posttype_name = preg_replace( '/^iworks_wordpress_plugin_stub_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name, 'iw' );
		/**
		 * WordPress Hooks
		 */
		add_shortcode( 'opi_heroes', array( $this, 'get_list' ) );
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
		$args      = array(
			'post_type'      => $this->posttype_name,
			'orderby'        => 'rand',
			'posts_per_page' => 2,
			'post_status'    => 'publish',
		);
		$the_query = new WP_Query( $args );
		/**
		 * No data!
		 */
		if ( ! $the_query->have_posts() ) {
			return $content;
		}
		/**
		 * Content
		 */
		$content .= '<div class="wp-block-group alignfull work-with-us work-with-us-heroes">';
		$content .= '<div class="wp-block-group__inner-container">';
		$content .= sprintf(
			'<h2>%s</h2>',
			esc_html__( 'Learn what our employees are saying.', 'wordpress-plugin-stub' )
		);
		$content .= sprintf(
			'<p class="become-one-of-them">%s</p>',
			esc_html__( 'Become one of them!', 'wordpress-plugin-stub' )
		);
		$content .= '<ul>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$content .= sprintf( '<li class="%s">', implode( ' ', get_post_class() ) );
			$content .= sprintf( '<h3>%s</h3>', get_the_title() );
			$content .= '<div class="post-inner">';
			$content .= '<blockquote class="post-content">';
			$content .= get_the_content();
			$content .= '</blockquote>';
			$content .= '</div>';
			$content .= get_the_post_thumbnail( get_the_ID(), 'full' );
			$content .= '<div class="post-excerpt">';
			$content .= get_the_excerpt();
			$content .= '</div>';
			$content .= '</li>';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		$content .= '</ul>';
		$content .= '</div>';
		$content .= '</div>';
		return $content;
	}

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
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->posttype_name, 'edit.php' ),
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'excerpt', 'page-attributes' ),
		);
		register_post_type( $this->posttype_name, $args );
	}

}

