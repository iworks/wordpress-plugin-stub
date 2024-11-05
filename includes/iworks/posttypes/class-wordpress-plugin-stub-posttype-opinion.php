<?php

require_once 'class-iworks-post-type.php';

class iWorks_Post_Type_Opinion extends iWorks_Post_Type {

	private $list = array();

	public function __construct() {
		parent::__construct();
		add_shortcode( 'iworks_opinions_list', array( $this, 'get_list' ) );
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes_add' ) );
		add_filter( 'iworks_post_type_opinion_options_list', array( $this, 'get_options_list_array' ), 10, 2 );
		add_action( 'save_post_' . $this->post_type_name['opinion'], array( $this, 'action_save_post_page' ), 10, 3 );
		$this->meta_boxes[ $this->post_type_name['opinion'] ] = array(
			'opinion-data' => array(
				'title'  => __( 'Opinion Data', 'THEME_SLUG' ),
				'fields' => array(
					array(
						'name'    => 'stars',
						'type'    => 'select',
						'label'   => esc_html__( 'The Opinion Stars', 'THEME_SLUG' ),
						'options' => array(
							'5' => '&bigstar;&bigstar;&bigstar;&bigstar;&bigstar;',
							'4' => '&bigstar;&bigstar;&bigstar;&bigstar;',
							'3' => '&bigstar;&bigstar;&bigstar;',
							'2' => '&bigstar;&bigstar;',
							'1' => '&bigstar;',
						),
					),
					array(
						'name'  => 'opinion_url',
						'type'  => 'url',
						'label' => esc_html__( 'The Opinion URL', 'THEME_SLUG' ),
					),
					array(
						'name'  => 'author_url',
						'type'  => 'url',
						'label' => esc_html__( 'The Opinion Author URL', 'THEME_SLUG' ),
					),
				),
			),
		);
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
				'posts_per_page' => 4,
			)
		);
		$args['post_type']   = $this->post_type_name['opinion'];
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
		get_template_part( 'template-parts/opinions/header' );
		$join = rand( 0, 2 );
		$i    = 0;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$args = array(
				'join' => $join,
				'i'    => $i++,
			);
			get_template_part( 'template-parts/opinions/one', get_post_type(), $args );
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		get_template_part( 'template-parts/opinions/footer' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.8
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Opinions', 'Post Type General Name', 'THEME_SLUG' ),
			'singular_name'         => _x( 'Opinion', 'Post Type Singular Name', 'THEME_SLUG' ),
			'menu_name'             => __( 'Opinions', 'THEME_SLUG' ),
			'name_admin_bar'        => __( 'Opinions', 'THEME_SLUG' ),
			'archives'              => __( 'Opinions', 'THEME_SLUG' ),
			'all_items'             => __( 'Opinions', 'THEME_SLUG' ),
			'add_new_item'          => __( 'Add New Opinion', 'THEME_SLUG' ),
			'add_new'               => __( 'Add New', 'THEME_SLUG' ),
			'new_item'              => __( 'New Opinion', 'THEME_SLUG' ),
			'edit_item'             => __( 'Edit Opinion', 'THEME_SLUG' ),
			'update_item'           => __( 'Update Opinion', 'THEME_SLUG' ),
			'view_item'             => __( 'View Opinion', 'THEME_SLUG' ),
			'view_items'            => __( 'View Opinion', 'THEME_SLUG' ),
			'search_items'          => __( 'Search Opinion', 'THEME_SLUG' ),
			'not_found'             => __( 'Not found', 'THEME_SLUG' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'THEME_SLUG' ),
			'items_list'            => __( 'Opinion list', 'THEME_SLUG' ),
			'items_list_navigation' => __( 'Opinion list navigation', 'THEME_SLUG' ),
			'filter_items_list'     => __( 'Filter items list', 'THEME_SLUG' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Opinion', 'THEME_SLUG' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Opinions', 'THEME_SLUG' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessopinion',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 20,
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_menu'        => 'adjc',
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'revisions' ),
			'rewrite'             => array(
				'slug' => _x( 'opinion', 'iWorks Post Type Opinion SLUG', 'THEME_SLUG' ),
			),
		);
		register_post_type(
			$this->post_type_name['opinion'],
			apply_filters( 'iworks_post_type_opinion_args', $args )
		);
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.8
	 */
	public function register_taxonomy() {
	}

	/**
	 * Get post list
	 *
	 * @param array $list options list
	 * @param array $atts WP_Query attributes
	 *
	 * @return string $content
	 */
	public function get_options_list_array( $list, $atts = array() ) {
		if ( ! empty( $this->list ) ) {
			return $this->list;
		}
		$list       = $this->get_select_array( $this->post_type_name['opinion'] );
		$this->list = $list;
		return $list;
	}


	public function action_add_meta_boxes_add( $post_type ) {
		if ( $post_type !== $this->post_type_name['opinion'] ) {
			return;
		}
		$this->add_meta_boxes( $this->post_type_name['opinion'] );
	}

	public function action_save_post_page( $post_id, $post, $update ) {
		$this->save_meta( $post_id, $post, $update, $this->post_type_name['opinion'] );
	}
}

