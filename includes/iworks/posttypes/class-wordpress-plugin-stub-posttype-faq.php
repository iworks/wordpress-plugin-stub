<?php
/**
 * Class for custom Post Type: FAQ
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_faq extends iworks_wordpress_plugin_stub_posttype_base {

	private $list = array();

	public function __construct() {
		parent::__construct();
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->posttype_name = preg_replace( '/^iworks_wordpress_plugin_stub_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name, 'iw_' );
		/**
		 * WordPress Hooks
		 */
		add_filter( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_columns', array( $this, 'filter_add_menu_order_column' ) );
		add_action( 'manage_' . $this->posttypes_names[ $this->posttype_name ] . '_posts_custom_column', array( $this, 'action_add_menu_order_value' ), 10, 2 );
		add_shortcode( 'iworks-faq-list', array( $this, 'shortcode_list' ) );
		add_filter( 'wp_localize_script_iworks_theme', array( $this, 'filter_wp_localize_script_iworks_theme' ) );
		add_filter( 'iworks_post_type_faq_terms_options_list', array( $this, 'get_options_list_array' ) );
		/**
		 * Settings
		 */
		$this->taxonomies[ $this->posttype_name ] = 'iw_faq_cat';
	}

	/**
	 * Register FAQs custom post type
	 */
	public function action_init_register_post_type() {

		$labels = array(
			'name'               => _x( 'FAQs', 'Post Type General Name', 'wordpress-plugin-stub' ),
			'singular_name'      => _x( 'FAQ', 'Post Type Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'          => _x( 'FAQs', 'Menu Name', 'wordpress-plugin-stub' ),
			'name_admin_bar'     => _x( 'FAQ', 'Admin Bar Name', 'wordpress-plugin-stub' ),
			'parent_item_colon'  => __( 'Parent FAQ:', 'wordpress-plugin-stub' ),
			'all_items'          => __( 'FAQs', 'wordpress-plugin-stub' ),
			'add_new_item'       => __( 'Add New FAQ', 'wordpress-plugin-stub' ),
			'add_new'            => __( 'Add New', 'wordpress-plugin-stub' ),
			'new_item'           => __( 'New FAQ', 'wordpress-plugin-stub' ),
			'edit_item'          => __( 'Edit FAQ', 'wordpress-plugin-stub' ),
			'update_item'        => __( 'Update FAQ', 'wordpress-plugin-stub' ),
			'view_item'          => __( 'View FAQ', 'wordpress-plugin-stub' ),
			'search_items'       => __( 'Search FAQ', 'wordpress-plugin-stub' ),
			'not_found'          => __( 'Not found', 'wordpress-plugin-stub' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'wordpress-plugin-stub' ),
		);

		$args = array(
			'label'               => __( 'faq', 'wordpress-plugin-stub' ),
			'description'         => __( 'Frequently Asked Questions', 'wordpress-plugin-stub' ),
			'labels'              => apply_filters( 'iworks/theme/register_post_type/faq/labels', $labels ),
			'supports'            => apply_filters(
				'iworks/theme/register_post_type/faq/subpackage',
				array(
					'title',
					'page-attributes',
					'editor',
				),
			),
			'hierarchical'        => true,
			'public'              => true,
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 10,
			'menu_icon'           => 'dashicons-format-chat',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rest_base'           => apply_filters(
				'iworks/theme/register_post_type/faq/rest_base',
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'faqs' : __( 'faqs', 'wordpress-plugin-stub' )
			),
		);
		register_post_type(
			$this->posttypes_names[ $this->posttype_name ],
			apply_filters(
				'iworks/theme/register_post_type/faq/arguments',
				$args
			)
		);
	}

	/**
	 * Register FAQ Group custom taxonomy
	 */
	public function action_init_register_taxonomy() {

		$labels = array(
			'name'                       => _x( 'FAQ Groups', 'Taxonomy General Name', 'wordpress-plugin-stub' ),
			'singular_name'              => _x( 'FAQ Group', 'Taxonomy Singular Name', 'wordpress-plugin-stub' ),
			'menu_name'                  => __( 'Groups', 'wordpress-plugin-stub' ),
			'all_items'                  => __( 'All FAQ Groups', 'wordpress-plugin-stub' ),
			'parent_item'                => __( 'Parent FAQ Group', 'wordpress-plugin-stub' ),
			'parent_item_colon'          => __( 'Parent FAQ Group:', 'wordpress-plugin-stub' ),
			'new_item_name'              => __( 'New FAQ Group Name', 'wordpress-plugin-stub' ),
			'add_new_item'               => __( 'Add New FAQ Group', 'wordpress-plugin-stub' ),
			'edit_item'                  => __( 'Edit FAQ Group', 'wordpress-plugin-stub' ),
			'update_item'                => __( 'Update FAQ Group', 'wordpress-plugin-stub' ),
			'view_item'                  => __( 'View FAQ Group', 'wordpress-plugin-stub' ),
			'separate_items_with_commas' => __( 'Separate FAQ Groups with commas', 'wordpress-plugin-stub' ),
			'add_or_remove_items'        => __( 'Add or remove FAQ Groups', 'wordpress-plugin-stub' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'wordpress-plugin-stub' ),
			'popular_items'              => __( 'Popular FAQ Groups', 'wordpress-plugin-stub' ),
			'search_items'               => __( 'Search FAQ Groups', 'wordpress-plugin-stub' ),
			'not_found'                  => __( 'Not Found', 'wordpress-plugin-stub' ),
		);

		$args = array(
			'labels'              => apply_filters( 'iworks/theme/register_post_type//labels', $labels ),
			'hierarchical'        => true,
			'public'              => true,
			'exclude_from_search' => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_admin_column'   => true,
			'show_in_nav_menus'   => true,
			'show_tagcloud'       => false,
			'show_in_rest'        => true,
			'rest_base'           => apply_filters(
				'iworks/theme/register_taxonomy/faq/rest_base',
				defined( 'ICL_SITEPRESS_VERSION' ) ? 'faq_groups' : __( 'faq_groups', 'wordpress-plugin-stub' )
			),
		);

		register_taxonomy(
			$this->taxonomies[ $this->posttype_name ],
			array( $this->posttypes_names[ $this->posttype_name ] ),
			apply_filters( 'iworks/theme/register_taxonomy/faq/arguments', $args )
		);
	}

	public function shortcode_list( $atts, $content = '' ) {
		$args = shortcode_atts(
			array(
				'term_id' => false,
				'tag'     => 'aside',
				'header'  => 'show',
				'dd'      => 'show',
				'id'      => sprintf( 'iworks-faq-%s', md5( time() + mt_rand( 0, mt_getrandmax() ) ) ),
			),
			$atts,
			'iworks_post_type_faq_shortcode_list'
		);
		if ( empty( $args['term_id'] ) ) {
			return $content;
		}
		$term = get_term( $args['term_id'], $this->taxonomy_name['faq'] );
		if ( empty( $term ) ) {
			return $content;
		}
		$query_args = array(
			'post_type'      => $this->posttype_name['faq'],
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => $this->taxonomy_name['faq'],
					'field'    => 'slug',
					'terms'    => $term->slug,
				),
			),
		);
		$the_query  = new WP_Query( $query_args );
		if ( ! $the_query->have_posts() ) {
			return $content;
		}
		/**
		 * classes
		 */
		$classes  = array(
			'iworks-faq',
			sprintf( 'iworks-faq-header-%s', $args['header'] ),
			sprintf( 'iworks-faq-dd-%s', $args['dd'] ),
			sprintf( 'iworks-faq-tag-%s', $args['tag'] ),
		);
		$content .= sprintf(
			'<%s class="%s" id="%s">',
			$args['tag'],
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $args['id'] )
		);
		if ( 'show' === $args['header'] ) {
			$content .= '<div class="iworks-faq-header">';
			$content .= sprintf( '<h2 class="iworks-faq-header-title">%s</h2>', $term->name );
			if ( 'show' === $args['header-button'] ) {
				$content .= sprintf(
					'<button class="iworks-faq-header-toggle" aria-label="%s" aria-expanded="false" data-target-id="%s" data-expanded="false">%s</button>',
					esc_attr__( 'Expand All', 'wordpress-plugin-stub' ),
					esc_attr( $args['id'] ),
					esc_html__( 'Expand All', 'wordpress-plugin-stub' )
				);
			}
			if ( 'show' === $args['description'] ) {
				$description = term_description( $args['term_id'] );
				if ( ! is_wp_error( $description ) ) {
					$description = trim( $description );
					if ( $description ) {
						$content .= '<div class="polon-faq-header-description">';
						$content .= wpautop( $description );
						$content .= '</div>';
					}
				}
			}
			$content .= '</div>';
		}
		$content .= '<div class="iworks-faq-container">';
		$content .= '<dl class="iworks-faq-list">';
		$i        = 1;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$button   = '';
			$classes  = array( 'iworks-faq-item-dd' );
			$dd_attrs = array();
			if ( 'folded' === $args['dd'] ) {
				$id         = sprintf( 'iworks-faq-item-dt-%d', $i++ );
				$button     = sprintf(
					' <button class="iworks-faq-list-toggle" aria-expanded="false" aria-controls="%s" data-target-id="%s" aria-label="%s"><span class="sr-only">%s</span></button>',
					esc_attr( $id ),
					esc_attr( $id ),
					esc_attr(
						sprintf(
							__( 'Expand %s', 'wordpress-plugin-stub' ),
							get_the_title()
						)
					),
					esc_html(
						sprintf(
							__( 'Expand %s', 'wordpress-plugin-stub' ),
							get_the_title()
						)
					)
				);
				$dd_attrs[] = 'hidden';
				$dd_attrs[] = sprintf( 'id="%s"', esc_attr( $id ) );
			}

			$content .= sprintf(
				'<dt class="iworks-faq-item-dt"><span class="iworks-faq-list-toggle">%s</span>%s</dt>',
				esc_html( get_the_title() ),
				$button
			);
			$content .= sprintf(
				'<dd class="%s"%s>%s</dd>',
				esc_attr( implode( ' ', $classes ) ),
				implode( ' ', $dd_attrs ),
				get_the_content()
			);
		}
		$content .= '</dl>';
		wp_reset_postdata();
		$content .= '</div>';
		$content .= sprintf( '</%s>', $args['tag'] );
		return $content;
	}

	public function filter_wp_localize_script_iworks_theme( $data ) {
		$data['i18n']['modules']['faq'] = array(
			'button' => array(
				'expand_all'   => esc_html__( 'Expand All', 'wordpress-plugin-stub' ),
				'collapse_all' => esc_html__( 'Collapse All', 'wordpress-plugin-stub' ),
			),
		);
		return $data;
	}

	/**
	 * Get taxonomy list
	 *
	 * @param array $list options list
	 *
	 * @return string $content
	 */
	public function get_options_list_array( $list ) {
		if ( ! empty( $this->list ) ) {
			return $this->list;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $this->taxonomy_name['faq'],
				'hide_empty' => false,
			)
		);
		$list  = array(
			'0' => esc_html__( '&mdash; Select &mdash;', 'wordpress-plugin-stub' ),
		);
		foreach ( $terms as $term ) {
			$list[ $term->term_id ] = $term->name;
		}
		$this->list = $list;
		return $list;
	}
}


