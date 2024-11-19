<?php
/**
 * Class for custom Post Type: PERSON
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_person extends iworks_wordpress_plugin_stub_posttype_base {

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
		 * Taxonomy name
		 */
		$this->taxonomy_name = preg_replace( '/^iworks_wordpress_plugin_stub_posttype_/', '', __CLASS__ );
		$this->register_class_custom_taxonomy_name( $this->taxonomy_name, 'iw', 'role' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_shortcode( 'iworks_persons_list', array( $this, 'get_list' ) );
		add_filter( 'og_og_type_value', array( $this, 'filter_og_og_type_value' ) );
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
		$args                = wp_parse_args(
			$atts,
			array(
				'orderby'        => 'rand',
				'posts_per_page' => -1,
			)
		);
		$args['post_type']   = $this->posttype_name;
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
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Persons', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Person', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Persons', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Persons', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Persons', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Persons', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New Person', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New Person', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit Person', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update Person', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View Person', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View Person', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search Person', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Person list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Person list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Person', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Persons', 'wordpress-plugin-stub' ),
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
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'person' : _x( 'person', 'iWorks Post Type Person SLUG', 'wordpress-plugin-stub' ),
			),
		);
		register_post_type(
			$this->posttype_name,
			apply_filters( 'iworks_post_type_person_args', $args )
		);
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Roles', 'Role General Name', 'wordpress-plugin-stub' ),
			'singular_name'              => _x( 'Role', 'Role Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'                  => __( 'Roles', 'wordpress-plugin-stub' ),
			'all_items'                  => __( 'All Roles', 'wordpress-plugin-stub' ),
			'parent_item'                => __( 'Parent Role', 'wordpress-plugin-stub' ),
			'parent_item_colon'          => __( 'Parent Role:', 'wordpress-plugin-stub' ),
			'new_item_name'              => __( 'New Role Name', 'wordpress-plugin-stub' ),
			'add_new_item'               => __( 'Add New Role', 'wordpress-plugin-stub' ),
			'edit_item'                  => __( 'Edit Role', 'wordpress-plugin-stub' ),
			'update_item'                => __( 'Update Role', 'wordpress-plugin-stub' ),
			'view_item'                  => __( 'View Role', 'wordpress-plugin-stub' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'wordpress-plugin-stub' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'wordpress-plugin-stub' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'wordpress-plugin-stub' ),
			'popular_items'              => __( 'Popular Roles', 'wordpress-plugin-stub' ),
			'search_items'               => __( 'Search Roles', 'wordpress-plugin-stub' ),
			'not_found'                  => __( 'Not Found', 'wordpress-plugin-stub' ),
			'no_terms'                   => __( 'No items', 'wordpress-plugin-stub' ),
			'items_list'                 => __( 'Roles list', 'wordpress-plugin-stub' ),
			'items_list_navigation'      => __( 'Roles list navigation', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'role' : _x( 'role', 'iWorks Post Type Person SLUG', 'wordpress-plugin-stub' ),
			),
		);
		register_taxonomy( $this->taxonomy_name, array( $this->posttype_name ), $args );
	}

	public function filter_og_og_type_value( $value ) {
		if ( is_singular( $this->posttype_name ) ) {
			return 'profile';
		}
		return $value;
	}

}

