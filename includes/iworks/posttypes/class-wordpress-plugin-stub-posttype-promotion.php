<?php
/**
 * Class for custom Post Type: PROMOTION
 *
 * @since 1.0.0

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

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_promo extends iworks_wordpress_plugin_stub_posttype_base {

	private $option_name_url               = '_opi_featured_url';
	private $option_name_button_label_more = '_opi_featured_button_label_more';
	private $option_name_ukrainian         = '_opi_featured_ukrainian';

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
		add_action( 'admin_head', array( $this, 'css' ) );
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
		add_action( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_custom_column', array( $this, 'action_add_menu_order_value' ), 10, 2 );
		add_action( 'pre_get_posts', array( $this, 'admin_set_default_order' ) );
		add_action( 'save_post', array( $this, 'save' ), PHP_INT_MAX );
		add_filter( 'manage_' . $this->posttype_name . '_posts_columns', array( $this, 'column_add' ), 10, 2 );
		add_filter( 'upload_mimes', array( $this, 'add_mime_types' ) );
		/**
		 * WordPress Plugin Stub Hooks
		 */
		add_filter( 'opi_pib_theme_main_page_get_promo', array( $this, 'get_list' ) );
		/**
		 * settings
		 */
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'featured-url' => array(
				'title'  => __( 'URL Configuration', 'wordpress-plugin-stub' ),
				'fields' => array(
					array(
						'name'  => 'target_button_url',
						'type'  => 'url',
						'label' => esc_html__( 'Target Button URL', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'target_button_text',
						'label' => esc_html__( 'Target Button Text', 'wordpress-plugin-stub' ),
					),
				),
			),
		);
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
	}

	/**
	 * Add colun size
	 *
	 * @since 1.0.0
	 */
	public function css() {
		echo '<style type="text/css">#menu_order, td.menu_order { width: 5em; }td.menu_order{text-align: right;}</style>';
	}

	/**
	 * Add column order
	 *
	 * @since 1.0.0
	 */
	public function column_add( $columns ) {
		$inserted = array(
			'menu_order' => __( 'Order', 'wordpress-plugin-stub' ),
		);
		$columns  = array_merge(
			array_slice( $columns, 0, 1, true ),
			$inserted,
			$columns
		);
		return $columns;
	}

	/**
	 * add column order value
	 *
	 * @since 1.0.0
	 */
	public function action_add_menu_order_value( $column, $post_id ) {
		switch ( $column ) {
			case 'menu_order':
				echo get_post_field( 'menu_order', $post_id, true );

				break;
		}
	}

	/**
	 * Set default order for admin
	 *
	 * @since 1.0.0
	 */
	public function admin_set_default_order( $query ) {
		if ( ! is_admin() ) {
			return;
		}
		if ( $this->posttype_name !== $query->get( 'post_type' ) ) {
			return;
		}
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}

	/**
	 * Add SVG files to allowed mimetypes
	 *
	 * @since 1.0.0
	 *
	 */
	public function add_mime_types( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	/**
	 * Get promo post list
	 *
	 * @since 1.0.0
	 *
	 * @param string $content current content
	 *
	 * @return string $content
	 */
	public function get_list( $content ) {
		$args      = array(
			'post_type'      => $this->posttype_name,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
		);
		$url       = get_bloginfo( 'url' );
		$url_info  = parse_url( $url );
		$host      = $url_info['host'];
		$the_query = new WP_Query( $args );
		// The Loop
		if ( $the_query->have_posts() ) {
			$content .= '<section class="promo" id="promo">';
			$content .= '<div class="section-wrapper">';
			$content .= '<div class="container">';
			$first    = true;
			while ( $the_query->have_posts() ) {
				if ( ! $first ) {
					$content .= '<span class="split"></span>';
				}
				$first = false;
				$the_query->the_post();
				$url      = get_post_meta( get_the_ID(), $this->option_name_url, true );
				$url_info = parse_url( $url );
				$target   = $host === $url_info['host'] ? '' : ' target="_blank"';
				/**
				 * add lang?
				 */
				if ( 'uk' === get_post_meta( get_the_ID(), $this->option_name_ukrainian, true ) ) {
					$content .= '<div class="post-inner" lang="uk">';
				} else {
					$content .= '<div class="post-inner">';
				}
				$content          .= '<div class="thumbnail">';
				$content          .= get_the_post_thumbnail( get_the_ID(), 'full' );
				$content          .= '</div>';
				$content          .= sprintf(
					'<p>%s</p>',
					get_the_title()
				);
				$button_label_more = get_post_meta( get_the_ID(), $this->option_name_button_label_more, true );
				if ( empty( $button_label_more ) ) {
					$button_label_more = _x( 'Find out more', 'Promo button text', 'wordpress-plugin-stub' );
				}
				$content .= sprintf(
					'<a href="%s" class="button button-small button-invert" %s title="%s">%s</a>',
					esc_url( $url ),
					$target,
					esc_attr(
						sprintf(
							__( 'Article on: %s', 'wordpress-plugin-stub' ),
							get_the_title()
						)
					),
					esc_html( $button_label_more )
				);
				$content .= '</div>';
			}
			$content .= '</div>';
			$content .= '</section>';
		}
		/* Restore original Post Data */
		wp_reset_postdata();
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
			'name'                  => _x( 'Featured', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Featured', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Featured', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Featured', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Featured', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Featured', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New Featured', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New Featured', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit Featured', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update Featured', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View Featured', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View Featured', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search Featured', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Featured list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Featured list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Featured', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Featured', 'wordpress-plugin-stub' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessperson',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->posttype_name, 'edit.php' ),
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		);
		register_post_type( $this->posttype_name, $args );
	}

	/**
	 * Save post meta
	 *
	 * @since 1.0.0
	 */
	public function save( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( $post_type !== $this->posttype_name ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		/**
		 * URL
		 */
		$nonce = filter_input( INPUT_POST, 'featured_nonce', FILTER_DEFAULT );
		if ( wp_verify_nonce( $nonce, '_featured' ) ) {
			/**
			 * url
			 */
			$value = filter_input( INPUT_POST, $this->option_name_url, FILTER_SANITIZE_URL );
			if ( empty( $value ) ) {
				delete_post_meta( $post_id, $this->option_name_url );
			} else {
				$result = update_post_meta( $post_id, $this->option_name_url, $value );
				if ( false === $result ) {
					add_post_meta( $post_id, $this->option_name_url, $value, true );
				}
			}
			/**
			 * button_label_more
			 */
			$value = filter_input( INPUT_POST, $this->option_name_button_label_more, FILTER_DEFAULT );
			if ( empty( $value ) ) {
				delete_post_meta( $post_id, $this->option_name_button_label_more );
			} else {
				$result = update_post_meta( $post_id, $this->option_name_button_label_more, $value );
				if ( false === $result ) {
					add_post_meta( $post_id, $this->option_name_button_label_more, $value, true );
				}
			}
			/**
			 * Ukrainian
			 */
			$value = filter_input( INPUT_POST, $this->option_name_ukrainian, FILTER_DEFAULT );
			if ( empty( $value ) || 'uk' !== $value ) {
				delete_post_meta( $post_id, $this->option_name_ukrainian );
			} else {
				$result = update_post_meta( $post_id, $this->option_name_ukrainian, $value );
				if ( false === $result ) {
					add_post_meta( $post_id, $this->option_name_ukrainian, $value, true );
				}
			}
		}
	}

	/**
	 * HTML for URL metabox
	 *
	 * @since 1.0.0
	 */
	public function html_url( $post ) {
		wp_nonce_field( '_featured', 'featured_nonce' );
		/**
		 * url
		 */
		$url = get_post_meta( $post->ID, $this->option_name_url, true );
		echo '<label><h4>';
		_e( 'Target URL', 'wordpress-plugin-stub' );
		echo '</h4>';
		printf(
			'<input class="large-text code" type="url" name="%s" value="%s" />',
			esc_attr( $this->option_name_url ),
			esc_url( $url )
		);
		echo '</label>';
		/**
		 * button more label
		 */
		$button_label_more = get_post_meta( $post->ID, $this->option_name_button_label_more, true );
		echo '<label><h4>';
		_e( 'Target Button Label', 'wordpress-plugin-stub' );
		echo '</h4>';
		printf(
			'<input class="large-text " type="text" name="%s" value="%s" />',
			esc_attr( $this->option_name_button_label_more ),
			esc_attr( $button_label_more )
		);
		echo '</label>';
		printf(
			'<p class="description">%s</p>',
			__( 'Leave empty to default', 'wordpress-plugin-stub' )
		);
		/**
		 * is ukrainian text?
		 */
		$ukrainian = get_post_meta( $post->ID, $this->option_name_ukrainian, true );
		echo '<label><h4>';
		_e( 'Is it Ukrainian language?', 'wordpress-plugin-stub' );
		echo '</h4><label>';
		printf(
			'<input type="checkbox" name="%s" value="uk" %s /> %s',
			esc_attr( $this->option_name_ukrainian ),
			checked( 'uk', $ukrainian, false ),
			esc_html__( 'Yes, it is in Ukrainian!', 'wordpress-plugin-stub' )
		);
		echo '</label>';
	}

}

