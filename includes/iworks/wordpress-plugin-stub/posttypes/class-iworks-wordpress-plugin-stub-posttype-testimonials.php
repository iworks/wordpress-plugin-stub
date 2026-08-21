<?php

require_once 'class-iworks-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_testimonials extends iworks_wordpress_plugin_stub_posttype {

	/**
	 * Post type name
	 *
	 * @since 1.0.0
	 * @var string $post_type Post type identifier
	 */
	protected string $post_type = 'testimonial';

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
		add_shortcode( 'iworks-testimonials', array( $this, 'shortcode_default_list' ) );
		/**
		 * Custom Columns
		 */
		$this->handle_custom_columns( $this->post_type );
		/**
		 * load meta boxes
		 */
		$this->load_meta_boxes( $this->post_type );
	}

	/**
	 * Register taxonomy
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_taxonomy() { }

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
		$this->meta_boxes[ $this->post_type_name[ $this->post_type ] ] = array(
			'testimonial' => array(
				'title'  => __( 'Testimonial Data', 'wordpress-plugin-stub' ),
				'fields' => array(
					array(
						'name'   => 'person',
						'type'   => 'text',
						'label'  => esc_html__( 'Person', 'wordpress-plugin-stub' ),
						'column' => 'show',
					),
					array(
						'name'   => 'position',
						'type'   => 'text',
						'label'  => esc_html__( 'Position', 'wordpress-plugin-stub' ),
						'column' => 'show',
					),
				),
			),
		);
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
		$atts = shortcode_atts(
			array(
				'type'           => 'plain',
				'posts_per_page' => 3,
			),
			$atts,
			'iworks_testimonials'
		);
		/**
		 * sanitize data
		 */
		$atts['posts_per_page'] = max( 1, intval( $atts['posts_per_page'] ) );
		$atts['type']           = in_array( strtolower( $atts['type'] ), array( 'plain', 'slider' ) ) ? strtolower( $atts['type'] ) : 'plain';
		/**
		 * WP Query args
		 */
		$args               = apply_filters(
			'iworks_testimonials_get_list_wp_query_args',
			array(
				'post_type'      => $this->post_type_name['testimonial'],
				'orderby'        => 'rand',
				'posts_per_page' => $atts['posts_per_page'],
				'post_status'    => 'publish',
			)
		);
		$testimonials_query = new WP_Query( $args );
		/**
		 * No data!
		 */
		if ( ! $testimonials_query->have_posts() ) {
			return $content;
		}
		/**
		 * Content
		 */
		$nav      = '';
		$content  = sprintf(
			'<div class="iworks-testimonials-container iworks-testimonials-%1$s" data-type="%1$s" data-posts="%2$d">',
			esc_attr( $atts['type'] ),
			esc_attr( $atts['posts_per_page'] )
		);
		$content .= '<ul class="iworks-testimonials">';
		while ( $testimonials_query->have_posts() ) {
			$testimonials_query->the_post();
			$classes = array(
				'iworks-testimonial',
			);
			if ( 'slider' === $atts['type'] ) {
				if ( 0 === $testimonials_query->current_post ) {
					$classes[] = 'current';
				} else {
					$classes[] = 'hide';
				}
			}
			$content .= sprintf(
				'<li id="iworks-testimonial-item-%1$d" data-id="%1$d" class="%2$s">',
				esc_attr( get_the_ID() ),
				esc_attr( implode( ' ', get_post_class( $classes ) ) )
			);
			$content .= '<div class="iworks-testimonial-content">';
			$content .= get_the_content();
			$content .= '</div>';
			$content .= '<div class="iworks-testimonial-footer">';
			$content .= sprintf(
				'<p class="iworks-testimonial-footer-person">%s</p>',
				esc_html( get_post_meta( get_the_ID(), 'iworks_testimonial_person', true ) )
			);
			$content .= sprintf(
				'<p class="iworks-testimonial-footer-position">%s</p>',
				esc_html( get_post_meta( get_the_ID(), 'iworks_testimonial_position', true ) )
			);
			$content .= '</div>';
			$content .= '</li>';
			/**
			 * Navigation
			 */
			$classes = array(
				'iworks-testimonials-nav-item',
			);
			if ( 'slider' === $atts['type'] ) {
				if ( 0 === $testimonials_query->current_post ) {
					$classes[] = 'current';
				} else {
					$classes[] = 'hide';
				}
			}
			$nav .= sprintf(
				'<li class="%1$s"><a href="#iworks-testimonial-item-%2$d" data-id="%2$d"><span class="sr-only">%3$s</span></a></li>',
				esc_attr( implode( ' ', $classes ) ),
				esc_attr( get_the_ID() ),
				sprintf( __( 'Show Testimonial %s', 'wordpress-plugin-stub' ), esc_html( get_the_title() ) )
			);

		}
		/* Restore original Post Data */
		wp_reset_postdata();
		$content .= '</ul>';
		if ( ! empty( $nav ) && 'slider' === $atts['type'] ) {
			$content .= '<ul class="iworks-testimonials-nav">';
			$content .= $nav;
			$content .= '</ul>';
		}
		$content .= '</div>';
		return $content;
	}

	/**
	 * Add custom columns to admin list
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Existing columns
	 * @return array Modified columns
	 */
	public function add_admin_columns( $columns ) {
		$columns['person']   = __( 'Person', 'wordpress-plugin-stub' );
		$columns['position'] = __( 'Position', 'wordpress-plugin-stub' );
		return $columns;
	}

	/**
	 * Display custom column content
	 *
	 * @since 1.0.0
	 *
	 * @param string $column Column name
	 * @param int    $post_id Post ID
	 */
	public function display_admin_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'person':
				$person = get_post_meta( $post_id, 'iworks_testimonial_person', true );
				echo $person ? esc_html( $person ) : '&mdash;';
				break;
			case 'position':
				$position = get_post_meta( $post_id, 'iworks_testimonial_position', true );
				echo $position ? esc_html( $position ) : '&mdash;';
				break;
		}
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Testimonials', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Testimonial', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Testimonials', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Testimonials', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Testimonials', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Testimonials', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New Testimonial', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New Testimonial', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit Testimonial', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update Testimonial', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View Testimonial', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View Testimonials', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search Testimonials', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Testimonials list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Testimonials list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Testimonial', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Testimonials', 'wordpress-plugin-stub' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => 'edit.php',
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor' ),
		);
		$this->register_post_type( $this->post_type, $args );
	}
}
