<?php

require_once 'class-iworks-post-type.php';

class iWorks_Post_Type_Hero extends iWorks_Post_Type {

	private $post_type_name = 'opi_hero';

	public function __construct() {
		parent::__construct();
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
		add_shortcode( 'opi_heroes', array( $this, 'get_list' ) );
	}

	/**
	 * Get post list
	 *
	 * @since 1.3.9
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $content current content
	 *
	 * @return string $content
	 */
	public function get_list( $atts, $content = '' ) {
		$args      = array(
			'post_type'      => $this->post_type_name,
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
			esc_html__( 'Learn what our employees are saying.', 'THEME_SLUG' )
		);
		$content .= sprintf(
			'<p class="become-one-of-them">%s</p>',
			esc_html__( 'Become one of them!', 'THEME_SLUG' )
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

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.3.9
	 */
	public function custom_post_type() {
		$labels = array(
			'name'                  => _x( 'Heroes', 'Post Type General Name', 'THEME_SLUG' ),
			'singular_name'         => _x( 'Hero', 'Post Type Singular Name', 'THEME_SLUG' ),
			'menu_name'             => __( 'Heroes', 'THEME_SLUG' ),
			'name_admin_bar'        => __( 'Heroes', 'THEME_SLUG' ),
			'archives'              => __( 'Heroes', 'THEME_SLUG' ),
			'all_items'             => __( 'Heroes', 'THEME_SLUG' ),
			'add_new_item'          => __( 'Add New Hero', 'THEME_SLUG' ),
			'add_new'               => __( 'Add New', 'THEME_SLUG' ),
			'new_item'              => __( 'New Hero', 'THEME_SLUG' ),
			'edit_item'             => __( 'Edit Hero', 'THEME_SLUG' ),
			'update_item'           => __( 'Update Hero', 'THEME_SLUG' ),
			'view_item'             => __( 'View Hero', 'THEME_SLUG' ),
			'view_items'            => __( 'View Hero', 'THEME_SLUG' ),
			'search_items'          => __( 'Search Hero', 'THEME_SLUG' ),
			'not_found'             => __( 'Not found', 'THEME_SLUG' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'THEME_SLUG' ),
			'items_list'            => __( 'Hero list', 'THEME_SLUG' ),
			'items_list_navigation' => __( 'Hero list navigation', 'THEME_SLUG' ),
			'filter_items_list'     => __( 'Filter items list', 'THEME_SLUG' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Hero', 'THEME_SLUG' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Heroes', 'THEME_SLUG' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->post_type_name, 'edit.php' ),
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'excerpt', 'page-attributes' ),
		);
		register_post_type( $this->post_type_name, $args );
	}

}

