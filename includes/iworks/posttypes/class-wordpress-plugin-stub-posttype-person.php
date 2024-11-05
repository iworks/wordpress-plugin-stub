<?php

class iWorks_Post_Type_Person {

	private $post_type_name = 'iworks_person';
	private $taxonomy_name  = 'iworks_person_role';

	public function __construct() {
		add_action( 'init', array( $this, 'register' ), 0 );
		add_shortcode( 'iworks_persons_list', array( $this, 'get_list' ) );
		add_filter( 'og_og_type_value', array( $this, 'filter_og_og_type_value' ) );
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
		$args                = wp_parse_args(
			$atts,
			array(
				'orderby'        => 'rand',
				'posts_per_page' => -1,
			)
		);
		$args['post_type']   = $this->post_type_name;
		$args['post_status'] = 'publish';
		$the_query           = new WP_Query( $args );
		/**
		 * No data!
		 */
		if ( ! $the_query->have_posts() ) {
			return $content;
		}
		/**
		 * Content
		 */
		ob_start();
		get_template_part( 'template-parts/heroes/header' );
		$join = rand( 0, 2 );
		$i    = 0;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$args = array(
				'join' => $join,
				'i'    => $i++,
			);
			get_template_part( 'template-parts/heroes/one', get_post_type(), $args );
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		get_template_part( 'template-parts/heroes/footer' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Register CPT & CT
	 *
	 * @since 1.0.8
	 */
	public function register() {
		$this->custom_post_type();
		$this->custom_taxonomy();
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.8
	 */
	private function custom_post_type() {
		$labels = array(
			'name'                  => _x( 'Persons', 'Post Type General Name', 'THEME_SLUG' ),
			'singular_name'         => _x( 'Person', 'Post Type Singular Name', 'THEME_SLUG' ),
			'menu_name'             => __( 'Persons', 'THEME_SLUG' ),
			'name_admin_bar'        => __( 'Persons', 'THEME_SLUG' ),
			'archives'              => __( 'Persons', 'THEME_SLUG' ),
			'all_items'             => __( 'Persons', 'THEME_SLUG' ),
			'add_new_item'          => __( 'Add New Person', 'THEME_SLUG' ),
			'add_new'               => __( 'Add New', 'THEME_SLUG' ),
			'new_item'              => __( 'New Person', 'THEME_SLUG' ),
			'edit_item'             => __( 'Edit Person', 'THEME_SLUG' ),
			'update_item'           => __( 'Update Person', 'THEME_SLUG' ),
			'view_item'             => __( 'View Person', 'THEME_SLUG' ),
			'view_items'            => __( 'View Person', 'THEME_SLUG' ),
			'search_items'          => __( 'Search Person', 'THEME_SLUG' ),
			'not_found'             => __( 'Not found', 'THEME_SLUG' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'THEME_SLUG' ),
			'items_list'            => __( 'Person list', 'THEME_SLUG' ),
			'items_list_navigation' => __( 'Person list navigation', 'THEME_SLUG' ),
			'filter_items_list'     => __( 'Filter items list', 'THEME_SLUG' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Person', 'THEME_SLUG' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Persons', 'THEME_SLUG' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => true,
			'show_in_admin_bar'   => false,
			'menu_position'       => 20,
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'revisions' ),
			'rewrite'             => array(
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'person' : _x( 'person', 'iWorks Post Type Person SLUG', 'THEME_SLUG' ),
			),
		);
		register_post_type(
			$this->post_type_name,
			apply_filters( 'iworks_post_type_person_args', $args )
		);
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.8
	 */
	private function custom_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Roles', 'Role General Name', 'THEME_SLUG' ),
			'singular_name'              => _x( 'Role', 'Role Singular Name', 'THEME_SLUG' ),
			'menu_name'                  => __( 'Roles', 'THEME_SLUG' ),
			'all_items'                  => __( 'All Roles', 'THEME_SLUG' ),
			'parent_item'                => __( 'Parent Role', 'THEME_SLUG' ),
			'parent_item_colon'          => __( 'Parent Role:', 'THEME_SLUG' ),
			'new_item_name'              => __( 'New Role Name', 'THEME_SLUG' ),
			'add_new_item'               => __( 'Add New Role', 'THEME_SLUG' ),
			'edit_item'                  => __( 'Edit Role', 'THEME_SLUG' ),
			'update_item'                => __( 'Update Role', 'THEME_SLUG' ),
			'view_item'                  => __( 'View Role', 'THEME_SLUG' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'THEME_SLUG' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'THEME_SLUG' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'THEME_SLUG' ),
			'popular_items'              => __( 'Popular Roles', 'THEME_SLUG' ),
			'search_items'               => __( 'Search Roles', 'THEME_SLUG' ),
			'not_found'                  => __( 'Not Found', 'THEME_SLUG' ),
			'no_terms'                   => __( 'No items', 'THEME_SLUG' ),
			'items_list'                 => __( 'Roles list', 'THEME_SLUG' ),
			'items_list_navigation'      => __( 'Roles list navigation', 'THEME_SLUG' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'role' : _x( 'role', 'iWorks Post Type Person SLUG', 'THEME_SLUG' ),
			),
		);
		register_taxonomy( $this->taxonomy_name, array( $this->post_type_name ), $args );
	}

	public function filter_og_og_type_value( $value ) {
		if ( is_singular( $this->post_type_name ) ) {
			return 'profile';
		}
		return $value;
	}

}

