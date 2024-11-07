<?php
/**
 * Class for custom Post Type: PUBLICATION
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_publication extends iworks_wordpress_plugin_stub_posttype_base {

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
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'pre_get_posts', array( $this, 'set_default_order' ) );
		/**
		 * WordPress Plugin Stub Hooks
		 */
		add_filter( 'opi_pib_get_systems_publications', array( $this, 'get_random' ), 10, 2 );
		add_filter( 'opi_pib_theme_system_tab_button_more_url', array( $this, 'get_archive_page_url' ), 10, 4 );
		/**
		 * settings
		 */
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'publication-data' => array(
				'title'  => __( 'Data', 'wordpress-plugin-stub' ),
				'fields' => array(
					array(
						'name'  => 'language',
						'type'  => 'select',
						'label' => esc_html__( 'Language', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'year',
						'type'  => 'year',
						'label' => esc_html__( 'Year', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'authors',
						'label' => esc_html__( 'Authors', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'where',
						'label' => esc_html__( 'Where', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'url',
						'type'  => 'url',
						'label' => esc_html__( 'URL', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'conference',
						'label' => esc_html__( 'Conference', 'wordpress-plugin-stub' ),
					),
				),
			),
		);
	}

	/**
	 * Set default order
	 *
	 * @since 1.0.0
	 */
	public function set_default_order( $query ) {
		if ( is_admin() ) {
			return;
		}
		if ( $this->posttype_name !== $query->get( 'post_type' ) ) {
			return;
		}
		$query->set( 'meta_key', 'opi_publication_year' );
		$query->set(
			'orderby',
			array(
				'meta_value_num' => 'DESC',
				'title'          => 'ASC',
			)
		);
	}

	public function get_archive_page_url( $url, $type, $id, $language ) {
		if ( 'publications' !== $type ) {
			return $url;
		}
		return get_post_type_archive_link( $this->posttype_name );
	}

	public function get_random( $content, $args ) {
		$args                   = wp_parse_args(
			$args,
			array(
				'post_type'      => $this->posttype_name,
				'orderby'        => 'rand',
				'posts_per_page' => 1,
				'wp_doing_ajax'  => apply_filters( 'wp_doing_ajax', false ),
			)
		);
		$args['posts_per_page'] = max( 1, intval( $args['posts_per_page'] ) );
		$the_query              = new WP_Query( $args );
		if ( 'pl_PL' === get_locale() ) {
			$content .= '<span class="section-title">Publikacje Naukowe OPI PIB</span>';
		} else {
			$content .= sprintf( '<span class="section-title">%s</span>', esc_html__( 'Scientific publications of OPI PIB', 'wordpress-plugin-stub' ) );
		}
		if ( $the_query->have_posts() ) {
			ob_start();
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				get_template_part( 'template-parts/single-content', 'publication', $args );
			}
			$content .= ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}

	public function the_content( $content ) {
		if ( get_post_type() !== $this->posttype_name ) {
			return $content;
		}
		$post_ID = get_the_ID();
		$c       = '';
		/**
		 * Authors
		 */
		$value = get_post_meta( $post_ID, 'opi_publication_authors', true );
		if ( ! empty( $value ) ) {
			$c .= sprintf(
				'<p class="opi-publication-authors">%s</p>',
				$value
			);
		}
		/**
		 * Year & where
		 */
		$year  = get_post_meta( $post_ID, 'opi_publication_year', true );
		$value = get_post_meta( $post_ID, 'opi_publication_where', true );
		if ( ! empty( $year ) || ! empty( $value ) ) {
			$c .= '<p class="opi-publication-where">';
			if ( ! empty( $year ) ) {
				$c .= sprintf( '<span class="year">%d</span> ', $year );
			}
			if ( ! empty( $value ) ) {
				$c .= $value;
			}
			$c .= '</p>';
		}
		/**
		 * conference
		 */
		$value = get_post_meta( $post_ID, 'opi_publication_conference', true );
		if ( ! empty( $value ) ) {
			$c .= sprintf(
				'<p class="opi-publication-conference">%s</p>',
				$value
			);
		}
		/**
		 * Content
		 */
		$c .= $content;
		/**
		 * url
		 */
		$value = get_post_meta( $post_ID, 'opi_publication_url', true );
		if ( ! empty( $value ) ) {
			$c .= sprintf(
				'<p class="opi-publication-url"><a href="%1$s" target="_blank">%1$s</a></p>',
				$value
			);
		}
		return $c;
	}

	public function action_init_register_taxonomy() {}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Publications', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Publication', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Publications', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Publications', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Publications', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Publications', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New Publication', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New Publication', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit Publication', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update Publication', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View Publication', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View Publication', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search Publication', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Publication list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Publication list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Publication', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'label'               => __( 'Publications', 'wordpress-plugin-stub' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => true,
			'show_in_admin_bar'   => true,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->posttype_name, 'edit.php' ),
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'excerpt' ),
		);
		register_post_type( $this->posttype_name, $args );
	}

	/**
	 * Save Publication data.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public function save( $post_ID ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, '_publication_nonce' );
		if ( ! wp_verify_nonce( $nonce, __CLASS__ ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_ID ) ) {
			return;
		}
		$this->update_meta( $post_ID, 'opi_publication_language', filter_input( INPUT_POST, 'opi_publication_language' ) );
		$this->update_meta( $post_ID, 'opi_publication_year', filter_input( INPUT_POST, 'opi_publication_year', FILTER_SANITIZE_NUMBER_INT ) );
		$this->update_meta( $post_ID, 'opi_publication_authors', filter_input( INPUT_POST, 'opi_publication_authors' ) );
		$this->update_meta( $post_ID, 'opi_publication_where', filter_input( INPUT_POST, 'opi_publication_where' ) );
		$this->update_meta( $post_ID, 'opi_publication_url', filter_input( INPUT_POST, 'opi_publication_url', FILTER_SANITIZE_URL ) );
		$this->update_meta( $post_ID, 'opi_publication_conference', filter_input( INPUT_POST, 'opi_publication_conference' ) );
	}
}

