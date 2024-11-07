<?php

defined( 'ABSPATH' ) || exit;

require_once dirname( ( dirname( __FILE__ ) ) ) . '/class-wordpress-plugin-stub-base.php';

abstract class iworks_wordpress_plugin_stub_posttype_base extends iworks_wordpress_plugin_stub_base {

	/**
	 * Post Type Name
	 *
	 * @since 1.0.0
	 */
	protected string $posttype_name;

	/**
	 * Taxonomy Name
	 *
	 * @since 1.0.0
	 */
	protected string $taxonomy_name;

	/**
	 * Media Option Name
	 *
	 * @since 1.0.0
	 */
	protected string $option_name_media = '_iw_media';

	protected array $posttypes_names = array();

	protected array $taxonomies_names = array();

	protected array $meta_boxes = array();

	/**
	 * post meta prefix
	 */
	protected $post_meta_prefix = '_';

	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'action_init_register_post_type' ), 0 );
		add_action( 'init', array( $this, 'action_init_register_taxonomy' ), 0 );
		add_action( 'save_post', array( $this, 'action_save_post_meta' ), 10, 3 );
		/**
		 * WordPress Plugin Stub Hooks
		 */
		/**
		 * Settings
		 */
		$this->posttypes_names  = apply_filters(
			'iworks/wordpress-plugin-stub/posttypes_names/array',
			$this->posttypes_names
		);
		$this->taxonomies_names = apply_filters(
			'iworks/wordpress-plugin-stub/taxonomies_names/array',
			$this->taxonomies_names
		);
	}

	abstract public function action_init_register_post_type();
	abstract public function action_init_register_taxonomy();

	/**
	 * Register the Post Type Name in the Class Parent Class.
	 *
	 * @since 1.0.0
	 */
	protected function register_class_custom_posttype_name( $posttype_name, $prefix = '' ) {
		if ( ! empty( $prefix ) ) {
			$prefix = sprintf( '%s_', $prefix );
		}
		$this->posttypes_names[ $posttype_name ] = $prefix . $posttype_name;
	}

	/**
	 * Register the Taxonomy Name in the Class Parent Class.
	 *
	 * @since 1.0.0
	 */
	protected function register_class_custom_taxonomy_name( $taxonomy_name, $prefix = '', $sufix = '' ) {
		if ( ! empty( $prefix ) ) {
			$prefix = sprintf( '%s_', $prefix );
		}
		if ( ! empty( $sufix ) ) {
			$sufix = sprintf( '_%s', $sufix );
		}
		$this->taxonomies_names[ $taxonomy_name ] = $prefix . $taxonomy_name . $sufix;
	}

	/**
	 * Save entry action
	 *
	 * @since 2.0.0
	 */
	public function action_save_post( $post_id, $post, $update ) {
		$this->save_meta( $post_id, $post, $update, $this->posttype_name );
	}

	protected function get_select_array( $post_type, $atts = array() ) {
		$args      = wp_parse_args(
			$atts,
			array(
				'order'          => 'ASC',
				'orderby'        => 'title',
				'posts_per_page' => -1,
				'post_type'      => $post_type,
				'post_status'    => 'publish',
			)
		);
		$list[0]   = __( '&mdash; Select &mdash;', 'wordpress-plugin-stub' );
		$the_query = new WP_Query( $args );
		foreach ( $the_query->posts as $post ) {
			$list[ $post->ID ] = $post->post_title;
		}
		return $list;
	}

	public function add_meta_boxes( $post ) {
		/**
		 * check available fields
		 */
		if ( empty( $this->meta_boxes ) ) {
			return;
		}
		foreach ( $this->meta_boxes as $meta_box_key => $meta_box_data ) {
			foreach ( $meta_box_data as $id => $data ) {
				add_meta_box(
					$id,
					$data['title'],
					array( $this, 'render_meta_box_content' ),
					$post->post_type,
					isset( $data['context'] ) ? $data['context'] : 'advanced',
					isset( $data['priority'] ) ? $data['priority'] : 'default',
					array(
						'posttype_name' => $post->post_type,
						'id'            => $id,
					)
				);
			}
		}
	}

	private function render_meta_select( $post, $one ) {
		$value = get_post_meta( $post->ID, $one['name'], true );
		echo '<p>';
		echo '<label>';
		if ( isset( $one['label'] ) ) {
			echo $one['label'];
			echo '<br />';
		}
		printf(
			'<select name="%s">',
			esc_attr( $one['name'] )
		);
		printf( '<option value="">%s</option>', __( '&mdash; Select &mdash;', 'wordpress-plugin-stub' ) );
		foreach ( $one['options'] as $option_value => $option_name ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option_value ),
				selected( $option_value, $value, false ),
				$option_name
			);
		}
		echo '</select>';
		echo '</label>';
		echo '</p>';
	}

	private function render_meta_radio( $post, $one ) {
		$value = get_post_meta( $post->ID, $one['name'], true );
		echo '<p>';
		if ( isset( $one['label'] ) ) {
			echo '<label>';
			echo $one['label'];
			echo '<br />';
			echo '</label>';
		}
		echo '</p>';
		printf(
			'<ul name="%s">',
			esc_attr( $one['name'] )
		);
		foreach ( $one['options'] as $option_value => $option_name ) {
			printf(
				'<li><label><input name="%s" type="radio" value="%s" %s>%s</label></li>',
				esc_attr( $one['name'] ),
				esc_attr( $option_value ),
				checked( $option_value, $value, false ),
				$option_name
			);
		}
		echo '</ul>';
	}

	protected function render( $post, $one ) {
		$method = sprintf( 'render_meta_%s', $one['type'] );
		if ( method_exists( $this, $method ) ) {
			$this->$method( $post, $one );
			return;
		}
		$value   = get_post_meta( $post->ID, $one['name'], true );
		$classes = array(
			'iworks-field',
			sprintf( 'iworks-field-%s', $one['type'] ),
		);
		if ( isset( $one['classes'] ) ) {
			$classes = wp_parse_args(
				$one['classes'],
				$classes
			);
		}
		printf( '<div class="%s">', esc_attr( implode( ' ', $classes ) ) );
		echo '<p>';
		echo '<label>';
		if ( isset( $one['label'] ) ) {
			echo $one['label'];
			echo '<br />';
		}
		printf(
			'<input type="%s" value="%s" name="%s" class="large-text" />',
			esc_attr( $one['type'] ),
			esc_attr( $value ),
			esc_attr( $one['name'] )
		);
		echo '</label>';
		if ( isset( $one['description'] ) ) {
			printf( '<span class="description">%s</span>', $one['description'] );
		}
		echo '</p>';
		echo '</div>';
	}

	public function render_meta_box_content( $post, $args ) {
		$posttype_name = $args['args']['posttype_name'];
		$id            = $args['args']['id'];
		wp_nonce_field( $id, $this->get_post_meta_name( $id ) );
		foreach ( $this->meta_boxes[ $posttype_name ][ $id ]['fields'] as $one ) {
			$this->render( $post, $one );
		}
	}

	protected function save_meta( $post_id, $post, $update, $post_type ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( get_post_type( $post ) !== $post_type ) {
			return;
		}
		if ( ! isset( $this->meta_boxes[ $post_type ] ) ) {
			return;
		}
		foreach ( $this->meta_boxes[ $post_type ] as $id => $data ) {
			$nonce_name  = $this->get_post_meta_name( $id );
			$nonce_value = filter_input( INPUT_POST, $nonce_name );
			if ( ! wp_verify_nonce( $nonce_value, $this->nonce_value ) ) {
				return;
			}
			foreach ( $data['fields'] as $one ) {
				$value = '';
				switch ( $one['type'] ) {
					case 'url':
						$value = filter_input( INPUT_POST, $one['name'], FILTER_SANITIZE_URL );
						break;
					default:
						$value = wp_kses_post( filter_input( INPUT_POST, $one['name'], FILTER_UNSAFE_RAW ) );
						break;
				}
				$this->update_meta( $post_id, $one['name'], $value );
			}
		}
	}
	public function filter_add_menu_order_column( $columns ) {
		$columns['menu_order'] = __( 'Order', 'wordpress-plugin-stub' );
		return $columns;
	}

	public function action_add_menu_order_value( $column, $post_id ) {
		switch ( $column ) {
			case 'menu_order':
				printf(
					'<span class="alignright">%d</span>',
					get_post_field( $column, $post_id )
				);
				return;
		}
	}

	protected function get_taxonomy( $taxonomy_name ) {
		if ( ! isset( $this->taxonomies_names[ $taxonomy_name ] ) ) {
			$this->taxonomies_names = apply_filters(
				'iworks/wordpress-plugin-stub/taxonomies_names/array',
				$this->taxonomies_names
			);
		}
		if ( isset( $this->taxonomies_names[ $taxonomy_name ] ) ) {
			return  $this->taxonomies_names[ $taxonomy_name ];
		}
		return new WP_Error( 'taxonomy', esc_html__( 'Selected Taxonomy dosn\'t exists.', 'wordpress-plugin-stub' ) );
	}

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	public function action_save_post_meta( $post_id, $post, $update ) {
		/*
		 * In production code, $slug should be set only once in the plugin,
		 * preferably as a class property, rather than in each function that needs it.
		 */
		$post_type = get_post_type( $post_id );
		/**
		 *  If this isn't a correct post, don't update it.
		 */
		if ( $this->posttype_name != $post_type ) {
			return;
		}
		/**
		 * check available fields
		 */
		if ( empty( $this->meta_boxes ) ) {
			return;
		}
		foreach ( $this->meta_boxes as $meta_box_key => $meta_box_data ) {
			if ( ! isset( $meta_box_data['fields'] ) ) {
				continue;
			}
			if ( ! is_array( $meta_box_data['fields'] ) ) {
				continue;
			}
			foreach ( $meta_box_data['fields'] as $group => $group_data ) {
				$post_key = $this->options->get_option_name( $group );
				l( $post_key );
				// do_action( 'iworks/wordpress-plugin-stub/postmeta/update', $post->ID, $option_name, $value, $key, $data );
			}
		}
	}

	protected function get_media_html( $post_ID ) {
		$content = '';
		$value   = get_post_meta( $post_ID, $this->option_name_media );
		if ( ! empty( $value ) && is_array( $value ) ) {
			$value = array_unique( $value );
			foreach ( $value as $attachment_ID ) {
				$data     = $this->get_attachment_data( $attachment_ID );
				$content .= sprintf(
					'<p class="filtry-publication-url filtry-publication-url-%s-%s"><a href="%s" rel="alternate">%s</a></p>',
					esc_attr( $data['type'] ),
					esc_attr( $data['subtype'] ),
					esc_attr( $data['url'] ),
					esc_html( empty( $data['caption'] ) ? $data['url'] : $data['caption'] )
				);
			}
		}
		return $content;
	}
	protected function get_post_meta_name( $name ) {
		return sprintf(
			'_iw_%s',
			esc_attr( $name )
		);
	}
}

