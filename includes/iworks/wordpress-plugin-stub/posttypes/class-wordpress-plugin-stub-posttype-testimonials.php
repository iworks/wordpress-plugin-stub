<?php

require_once __DIR__ . '/class-iworks-theme-post-type.php';

class iWorks_Theme_Post_Type_Testimonials extends iWorks_Theme_Post_Type {

	public function __construct() {
		parent::__construct();
		add_action( 'init', array( $this, 'action_init' ) );
		//      add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_shortcode( 'iworks_testimonials', array( $this, 'get_list' ) );
		add_filter( 'manage_' . $this->post_type_name['testimonial'] . '_posts_columns', array( $this, 'add_admin_columns' ) );
		add_action( 'manage_' . $this->post_type_name['testimonial'] . '_posts_custom_column', array( $this, 'display_admin_columns' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes_add' ) );
		add_action( 'save_post_' . $this->post_type_name['testimonial'], array( $this, 'action_save_post_page' ), 10, 3 );
	}


	public function action_init() {
		$this->meta_boxes[ $this->post_type_name['testimonial'] ] = array(
			'testimonial' => array(
				'title'  => __( 'Testimonial Data', 'THEME_TEXT_DOMAIN' ),
				'fields' => array(
					array(
						'name'  => 'iworks_testimonial_person',
						'type'  => 'text',
						'label' => esc_html__( 'Person', 'THEME_TEXT_DOMAIN' ),
					),
					array(
						'name'  => 'iworks_testimonial_position',
						'type'  => 'text',
						'label' => esc_html__( 'Position', 'THEME_TEXT_DOMAIN' ),
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
				sprintf( __( 'Show Testimonial %s', 'THEME_TEXT_DOMAIN' ), esc_html( get_the_title() ) )
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
		$columns['person']   = __( 'Person', 'THEME_TEXT_DOMAIN' );
		$columns['position'] = __( 'Position', 'THEME_TEXT_DOMAIN' );
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

	public function register_taxonomy() {}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Testimonials', 'Post Type General Name', 'THEME_TEXT_DOMAIN' ),
			'singular_name'         => _x( 'Testimonial', 'Post Type Singular Name', 'THEME_TEXT_DOMAIN' ),
			'menu_name'             => __( 'Testimonials', 'THEME_TEXT_DOMAIN' ),
			'name_admin_bar'        => __( 'Testimonials', 'THEME_TEXT_DOMAIN' ),
			'archives'              => __( 'Testimonials', 'THEME_TEXT_DOMAIN' ),
			'all_items'             => __( 'Testimonials', 'THEME_TEXT_DOMAIN' ),
			'add_new_item'          => __( 'Add New Testimonial', 'THEME_TEXT_DOMAIN' ),
			'add_new'               => __( 'Add New', 'THEME_TEXT_DOMAIN' ),
			'new_item'              => __( 'New Testimonial', 'THEME_TEXT_DOMAIN' ),
			'edit_item'             => __( 'Edit Testimonial', 'THEME_TEXT_DOMAIN' ),
			'update_item'           => __( 'Update Testimonial', 'THEME_TEXT_DOMAIN' ),
			'view_item'             => __( 'View Testimonial', 'THEME_TEXT_DOMAIN' ),
			'view_items'            => __( 'View Testimonials', 'THEME_TEXT_DOMAIN' ),
			'search_items'          => __( 'Search Testimonials', 'THEME_TEXT_DOMAIN' ),
			'not_found'             => __( 'Not found', 'THEME_TEXT_DOMAIN' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'THEME_TEXT_DOMAIN' ),
			'items_list'            => __( 'Testimonials list', 'THEME_TEXT_DOMAIN' ),
			'items_list_navigation' => __( 'Testimonials list navigation', 'THEME_TEXT_DOMAIN' ),
			'filter_items_list'     => __( 'Filter items list', 'THEME_TEXT_DOMAIN' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Testimonial', 'THEME_TEXT_DOMAIN' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Testimonials', 'THEME_TEXT_DOMAIN' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => apply_filters( 'storm_post_type_show_in_menu' . $this->post_type_name['testimonial'], 'edit.php' ),
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor' ),
		);
		register_post_type( $this->post_type_name['testimonial'], $args );
	}

	/**
	 * Add meta boxes
	 *
	 * @since 1.0.0
	 */
	public function x_add_meta_boxes() {
		add_meta_box(
			'iworks-testimonials-data',
			__( 'Testimonial data', 'THEME_TEXT_DOMAIN' ),
			array( $this, 'html_data' ),
			$this->post_type_name['testimonial'],
			'side',
			'default'
		);
	}

	/**
	 * HTML for metabox
	 *
	 * @since 1.0.0
	 */
	public function html_data( $post ) {
		wp_nonce_field( __CLASS__, '_testimonials_nonce' );
		?>
<p>
	<label><?php esc_attr_e( 'Person', 'THEME_TEXT_DOMAIN' ); ?><br />
		<?php $value = get_post_meta( $post->ID, 'iworks_testimonial_person', true ); ?>
		<input type="text" class="large-text" value="<?php echo esc_attr( $value ); ?>" name="iworks_testimonial_person" />
	</label>
</p>
<p>
	<label><?php esc_attr_e( 'Position', 'THEME_TEXT_DOMAIN' ); ?><br />
		<?php $value = get_post_meta( $post->ID, 'iworks_testimonial_position', true ); ?>
		<input type="text" class="large-text" value="<?php echo esc_attr( $value ); ?>" name="iworks_testimonial_position" />
	</label>
</p>
		<?php
	}
	/**
	 * Save Testimonial data.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public function save( $post_ID ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, '_testimonials_nonce' );
		if ( ! wp_verify_nonce( $nonce, __CLASS__ ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_ID ) ) {
			return;
		}
		$this->update_meta( $post_ID, 'iworks_testimonial_person', filter_input( INPUT_POST, 'iworks_testimonial_person' ) );
		$this->update_meta( $post_ID, 'iworks_testimonial_position', filter_input( INPUT_POST, 'iworks_testimonial_position' ) );
	}

	public function action_add_meta_boxes_add( $post_type ) {
		if ( $post_type !== $this->post_type_name['testimonial'] ) {
			return;
		}
		$this->add_meta_boxes( $this->post_type_name['testimonial'] );
	}

	public function action_save_post_page( $post_id, $post, $update ) {
		$this->save_meta( $post_id, $post, $update, $this->post_type_name['testimonial'] );
	}
}

