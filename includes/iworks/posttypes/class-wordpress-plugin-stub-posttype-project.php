<?php
/**
 * Class for custom Post Type: PROJECT
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

class iworks_wordpress_plugin_stub_posttype_project extends iworks_wordpress_plugin_stub_posttype_base {

	// private string $posttype_name;

	private $fields               = array();
	private $option_name_partners = '_partners';
	/**
	 * partners types
	 *
	 * @since 1.0.0
	 */
	private $partners_types;

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
		add_action( 'pre_get_posts', array( $this, 'set_default_order' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_action( 'wp_loaded', array( $this, 'setup' ) );
		/**
		 * projects tab on main page
		 */
		add_filter( 'opi_pib_get_opi_projects_random', array( $this, 'get_random' ), 10, 2 );
		add_filter( 'opi_pib_get_opi_projects', array( $this, 'get_list' ) );
		add_filter( 'opi_pib_get_opi_project_types', array( $this, 'filter_get_partners_types' ) );
	}

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {
		$this->load_plugin_admin_assets                                     = true;
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'project-data'  => array(
				'title'  => __( 'Project Data', 'wordpress-plugin-stub' ),
				'fields' => array(
					array(
						'name'  => 'icon',
						'type'  => 'image',
						'label' => esc_html__( 'Icon', 'wordpress-plugin-stub' ),
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
			'project-media' => array(
				'title'  => __( 'Project Media', 'wordpress-plugin-stub' ),
				'fields' => array(
					array(
						'name'  => 'icon',
						'type'  => 'image',
						'label' => esc_html__( 'Icon', 'wordpress-plugin-stub' ),
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

	public function action_init_register_taxonomy() {}

	public function setup() {
		$this->partners_types = array(
			'lider'         => __( 'Liders', 'wordpress-plugin-stub' ),
			'scientific'    => __( 'Scientific Partners', 'wordpress-plugin-stub' ),
			'business'      => __( 'Business Partners', 'wordpress-plugin-stub' ),
			'partner'       => __( 'Partners', 'wordpress-plugin-stub' ),
			'subcontractor' => __( 'Subcontractors', 'wordpress-plugin-stub' ),
		);
	}

	public function get_random( $content, $posts_per_page ) {
		$args      = array(
			'orderby'        => 'rand',
			'posts_per_page' => max( 1, intval( $posts_per_page ) ),
			'post_status'    => 'publish',
			'post_type'      => $this->posttype_name,
		);
		$the_query = new WP_Query( $args );
		if ( 'pl_PL' === get_locale() ) {
			$content .= '<span class="section-title">Publikacje OPI PIB</span>';
		} else {
			$content .= sprintf( '<span class="section-title">%s</span>', esc_html__( 'Projects of OPI PIB', 'wordpress-plugin-stub' ) );
		}
		if ( $the_query->have_posts() ) {
			ob_start();
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				get_template_part( 'template-parts/single-content', 'Project' );
			}
			$content .= ob_get_contents();
			ob_end_clean();
		}
		return $content;
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
		$query->set( 'meta_key', '_project_date_start' );
		$query->set(
			'orderby',
			array(
				'meta_value' => 'DESC',
				'title'      => 'ASC',
			)
		);
	}

	/**
	 * Get list
	 *
	 * @since 1.0.0
	 */
	public function get_list( $content ) {
		$args      = array(
			'nopaging'    => true,
			'post_status' => 'publish',
			'post_type'   => $this->posttype_name,
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			$args['wp_doing_ajax'] = wp_doing_ajax();
			ob_start();
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				get_template_part( 'template-parts/single-content', 'Project', $args );
			}
			$content .= ob_get_contents();
			ob_end_clean();
		}
		$url = get_post_type_archive_link( $this->posttype_name );
		if ( $url ) {
			$content .= sprintf(
				'<p class="more %s"><a href="%s" class="button">%s</a></p>',
				esc_attr( $this->posttype_name ),
				$url,
				esc_html__( 'Browse all projects', 'wordpress-plugin-stub' )
			);
		}
		return $content;
	}

	/**
	 * get content
	 *
	 * @since 1.0.0
	 */
	public function the_content( $content ) {
		if ( get_post_type() !== $this->posttype_name ) {
			return $content;
		}
		$post_ID = get_the_ID();
		$c       = '';
		$this->set_fields();
		/**
		 * fields
		 */
		$show = false;
		foreach ( $this->fields as $key => $one ) {
			$value = get_post_meta( $post_ID, $key, true );
			if ( empty( $value ) ) {
				continue;
			}
			if ( isset( $one['type'] ) && 'url' === $one['type'] ) {
				$value = sprintf(
					'<a href="%1$s" target="_blank">%1$s</a>',
					esc_url( $value )
				);
			} elseif ( isset( $one['sanitize'] ) ) {
				$value = $one['sanitize']( $value );
			} else {
				$value = esc_html( $value );
			}
			$this->fields[ $key ]['value'] = $value;
			$show                          = true;
		}
		if ( $show ) {
			$args = array(
				'fields' => $this->fields,
			);
			ob_start();
			get_template_part( 'template-parts/project/part', 'data', $args );
			$c .= ob_get_contents();
			ob_end_clean();
		}
		/**
		 * partners
		 */
		ob_start();
		get_template_part( 'template-parts/project/part', 'partners' );
		$c .= ob_get_contents();
		ob_end_clean();
		/**
		 * Content
		 */
		$c .= $content;
		/**
		 * media
		 */
		$c .= $this->get_media_html( $post_ID );
		return $c;
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 1.0.0
	 */
	public function action_init_register_post_type() {
		$labels = array(
			'name'                  => _x( 'Projects', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'         => _x( 'Project', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'             => __( 'Projects', 'wordpress-plugin-stub' ),
			'name_admin_bar'        => __( 'Projects', 'wordpress-plugin-stub' ),
			'archives'              => __( 'Projects', 'wordpress-plugin-stub' ),
			'all_items'             => __( 'Projects', 'wordpress-plugin-stub' ),
			'add_new_item'          => __( 'Add New project', 'wordpress-plugin-stub' ),
			'add_new'               => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'              => __( 'New project', 'wordpress-plugin-stub' ),
			'edit_item'             => __( 'Edit project', 'wordpress-plugin-stub' ),
			'update_item'           => __( 'Update project', 'wordpress-plugin-stub' ),
			'view_item'             => __( 'View project', 'wordpress-plugin-stub' ),
			'view_items'            => __( 'View project', 'wordpress-plugin-stub' ),
			'search_items'          => __( 'Search project', 'wordpress-plugin-stub' ),
			'not_found'             => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
			'items_list'            => __( 'Project list', 'wordpress-plugin-stub' ),
			'items_list_navigation' => __( 'Project list navigation', 'wordpress-plugin-stub' ),
			'filter_items_list'     => __( 'Filter items list', 'wordpress-plugin-stub' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Project', 'wordpress-plugin-stub' ),
			'exclude_from_search' => true,
			'has_archive'         => true,
			'hierarchical'        => false,
			'label'               => __( 'Projects', 'wordpress-plugin-stub' ),
			'labels'              => $labels,
			'public'              => true,
			'show_in_admin_bar'   => true,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->posttypes_names[ $this->posttype_name ], 'edit.php' ),
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'             => array(
				'slug' => _x( 'Project', 'slug for single project', 'wordpress-plugin-stub' ),
			),
		);
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			unset( $args['rewrite'] );
		}
		register_post_type( $this->posttypes_names[ $this->posttype_name ], $args );
	}

	/**
	 * Add meta boxes
	 *
	 * @since 1.0.0
	 */
	public function x_add_meta_boxes() {
		/**
		 * Partners
		 */
		foreach ( $this->partners_types as $type => $label ) {
			add_meta_box(
				'opi-post-partners-' . $type,
				$label,
				array( $this, 'html_post_partners_' . $type ),
				$this->posttype_name,
				'normal',
				'default'
			);
		}
	}

	private function html_partners( $post, $type ) {
		printf(
			'<div id="opi-partner-%1$s-container" class="opi-partner-container opi-partner-%1$s-container" data-partner="%1$s" aria-hidden="true">',
			esc_attr( $type )
		);
		echo '<p>';
		printf(
			'<button type="button" class="button button-add-partner">%s</button>',
			esc_html__( 'Add a partner', 'wordpress-plugin-stub' )
		);
		echo '</p>';
		printf(
			'<div id="opi-partner-%s-container-rows">',
			esc_attr( $type )
		);
		$value   = get_post_meta( $post->ID, $this->option_name_partners, true );
		$parners = isset( $value[ $type ] ) ? $value[ $type ] : array();
		foreach ( $parners as $caption ) {
			$this->partner_row( array( 'caption' => $caption ), $type );
		}
		echo '</div>';
		echo '</div>';
		printf(
			'<script type="text/html" id="tmpl-opi-partner-%s-row">',
			esc_attr( $type )
		);
		$this->partner_row( array(), $type );
		echo '</script>';
	}

	public function html_post_partners_lider( $post ) {
		$this->html_partners( $post, 'lider' );
	}

	public function html_post_partners_scientific( $post ) {
		$this->html_partners( $post, 'scientific' );
	}

	public function html_post_partners_business( $post ) {
		$this->html_partners( $post, 'business' );
	}

	public function html_post_partners_partner( $post ) {
		$this->html_partners( $post, 'partner' );
	}

	public function html_post_partners_subcontractor( $post ) {
		$this->html_partners( $post, 'subcontractor' );
	}

	private function set_fields() {
		$this->fields = array(
			'_project_date_start'     => array(
				'label' => __( 'Project start date', 'wordpress-plugin-stub' ),
				'type'  => 'date',
			),
			'_project_date_end'       => array(
				'label' => __( 'Project end date', 'wordpress-plugin-stub' ),
				'type'  => 'date',
			),
			'_realization_date_start' => array(
				'label' => __( 'Realization start date', 'wordpress-plugin-stub' ),
				'type'  => 'date',
			),
			'_realization_date_end'   => array(
				'label' => __( 'Realization end date', 'wordpress-plugin-stub' ),
				'type'  => 'date',
			),
			'_project_cost'           => array(
				'label'    => __( 'Project cost', 'wordpress-plugin-stub' ),
				'type'     => 'number',
				'sanitize' => 'floatval',
				'sufix'    => __( 'PLN', 'wordpress-plugin-stub' ),
			),
			'_project_funding'        => array(
				'label'    => __( 'Project amount of funding', 'wordpress-plugin-stub' ),
				'type'     => 'number',
				'sanitize' => 'floatval',
				'sufix'    => __( 'PLN', 'wordpress-plugin-stub' ),
			),
			'_project_currency'       => array(
				'label'    => __( 'Project currency', 'wordpress-plugin-stub' ),
				'type'     => 'text',
				'sanitize' => 'esc_html',
				'hide'     => true,
			),
			'_project_url'            => array(
				'label'    => __( 'Project URL', 'wordpress-plugin-stub' ),
				'type'     => 'url',
				'sanitize' => 'esc_url',
			),
		);
	}

	/**
	 * HTML for metabox
	 *
	 * @since 1.0.0
	 */
	public function html_data( $post ) {
		$this->set_fields();
		wp_nonce_field( __CLASS__, '_project_nonce' );
		foreach ( $this->fields as $key => $one ) {
			$value = get_post_meta( $post->ID, $key, true );
			if ( isset( $one['sanitize'] ) ) {
				$value = $one['sanitize']( $value );
			}
			$method = sprintf(
				'input_%s',
				$one['type']
			);
			if ( method_exists( $this, $method ) ) {
				echo $this->$method( $key, $value, $one['label'] );
			}
		}
	}

	private function get_src( $post_id ) {
		$src = null;
		if ( isset( $this->images[ $post_id ] ) ) {
			$src = $this->images[ $post_id ];
		}
		$attachment_id = get_post_meta( $post_id, $this->option_name_media, true );
		if ( ! empty( $attachment_id ) ) {
			$src = wp_get_attachment_url( $attachment_id );
		}
		if ( empty( $src ) ) {
			return $src;
		}
		$this->images[ $post_id ] = $src;
		return $src;
	}


	/**
	 * Partner row helper
	 *
	 * @since 1.0.0
	 */
	protected function partner_row( $data = array(), $type = '' ) {
		$data = wp_parse_args(
			$data,
			array(
				'caption' => '{{{data.caption}}}',
			)
		);
		echo '<div class="opi-partner-row">';
		echo '<span class="dashicons dashicons-move"></span>';
		printf(
			'<input type="text" class="text-wide" name="%s[%s][]" value="%s" />',
			esc_attr( $this->option_name_partners ),
			esc_attr( $type ),
			esc_attr( $data['caption'] )
		);
		printf(
			'<button class="trash" type="button" aria-label="%s"><span class="dashicons dashicons-trash"></span></button>',
			esc_attr__( 'Remove Partner', 'wordpress-plugin-stub' )
		);
		echo '</div>';
	}

	/**
	 * get partners types
	 *
	 * @since 1.0.0
	 */
	public function filter_get_partners_types( $types ) {
		return $this->partners_types;
	}
}

