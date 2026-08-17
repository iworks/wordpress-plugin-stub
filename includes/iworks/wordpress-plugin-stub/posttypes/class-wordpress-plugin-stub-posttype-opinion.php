<?php
/**
 * Class for custom Post Type: OPINION
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

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_opinion extends iworks_wordpress_plugin_stub_posttype {

	/**
	 * List of opinions
	 *
	 * @var array $list
	 */
	private array $list = array();

	/**
	 * Post type name
	 *
	 * @since 1.0.0
	 * @var string $post_type Post type identifier
	 */
	protected string $post_type = 'opinion';

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
		add_action( 'add_meta_boxes_' . $this->post_type_name[ $this->post_type ], array( $this, 'add_meta_boxes' ) );
		/**
		 * Shortcodes
		 */
		add_shortcode( 'iworks-opinions', array( $this, 'get_list' ) );
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
		/**
		 * Settings
		 */
		$this->meta_boxes[ $this->post_type_name[ $this->post_type ] ] = array(
			'opinion-data' => array(
				'title'  => __( 'Opinion Data', 'wordpress-plugin-stub' ),
				'fields' => array(
					array(
						'name'    => 'stars',
						'type'    => 'select',
						'label'   => esc_html__( 'The Opinion Stars', 'wordpress-plugin-stub' ),
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
						'label' => esc_html__( 'The Opinion URL', 'wordpress-plugin-stub' ),
					),
					array(
						'name'  => 'author_url',
						'type'  => 'url',
						'label' => esc_html__( 'The Opinion Author URL', 'wordpress-plugin-stub' ),
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
		$args = shortcode_atts(
			array(
				'orderby'        => 'rand',
				'posts_per_page' => 4,
			),
			$atts,
			'iworks/wordpress-plugin-stub/opinion/list'
		);
		/**
		 * Post type
		 */
		$args['post_type'] = $this->post_type_name[ $this->post_type ];
		/**
		 * Post status
		 */
		$args['post_status'] = 'publish';
		/**
		 * Query
		 */
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
		ob_start();
		$file = $this->get_module_file( 'header', 'opinions' );
		if ( $file ) {
			include_once $file;
		}
		$join = rand( 0, 2 );
		$i    = 0;
		$file = $this->get_module_file( 'one', 'opinions' );
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$one_args = array(
				'join' => $join,
				'i'    => $i++,
			);
			if ( $file ) {
				include_once $file;
			}
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		$file = $this->get_module_file( 'footer', 'opinions' );
		if ( $file ) {
			include_once $file;
		}
		$content = ob_get_contents();
		ob_end_clean();
		return apply_filters( 'iworks/wordpress-plugin-stub/opinion/get_list', $content );
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Opinions', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Opinion', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Opinions', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Opinions', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Opinions', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Opinions', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New Opinion', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New Opinion', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit Opinion', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update Opinion', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View Opinion', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View Opinion', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search Opinion', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Opinion list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Opinion list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Opinion', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Opinions', 'wordpress-plugin-stub' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-businessopinion',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 20,
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php',
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'thumbnail', 'editor', 'revisions' ),
			'rewrite'             => array(
				'slug' => defined( 'ICL_SITEPRESS_VERSION' ) ? 'opinion' : _x( 'opinion', 'WordPress Plugin Stub Post Type Opinion SLUG', 'wordpress-plugin-stub' ),
			),
		);
		$this->register_post_type( $this->post_type, $args );
	}

	/**
	 * Register Custom Taxonomy
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_taxonomy() {}

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
}
