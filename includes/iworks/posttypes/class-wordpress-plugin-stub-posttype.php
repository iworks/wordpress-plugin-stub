<?php

defined( 'ABSPATH' ) || exit;

require_once dirname( ( dirname( __FILE__ ) ) ) . '/class-wordpress-plugin-stub-base.php';

abstract class iworks_wordpress_plugin_stub_posttype_base extends iworks_wordpress_plugin_stub_base {

	protected $post_type_name = array(
		'faq'     => 'faq',
		'opinion' => 'iworks_opinion',
		'person'  => 'iworks_person',
		'project' => 'iworks_project',
	);

	protected $taxonomy_name = array(
		'faq'         => 'faq_cat',
		'person_role' => 'on_role',
	);

	protected $meta_boxes = array();

	/**
	 * post meta prefix
	 */
	protected $post_meta_prefix = '_';

	public function __construct() {
		parent::__construct();
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	abstract public function register_post_type();
	abstract public function register_taxonomy();

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
		$list[0]   = __( '&mdash; Select &mdash;', 'THEME_SLUG' );
		$the_query = new WP_Query( $args );
		foreach ( $the_query->posts as $post ) {
			$list[ $post->ID ] = $post->post_title;
		}
		return $list;
	}

	protected function add_meta_boxes( $post_type ) {
		if ( empty( $this->meta_boxes ) ) {
			return;
		}
		if ( ! isset( $this->meta_boxes[ $post_type ] ) ) {
			return;
		}
		foreach ( $this->meta_boxes[ $post_type ] as $id => $data ) {
			add_meta_box(
				$id,
				$data['title'],
				array( $this, 'render_meta_box_content' ),
				$post_type,
				isset( $data['context'] ) ? $data['context'] : 'advanced',
				isset( $data['priority'] ) ? $data['priority'] : 'default',
				array(
					'post_type_name' => $post_type,
					'id'             => $id,
				)
			);
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
		printf( '<option value="">%s</option>', __( '&mdash; Select &mdash;', 'THEME_SLUG' ) );
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
		$post_type_name = $args['args']['post_type_name'];
		$id             = $args['args']['id'];
		wp_nonce_field( $this->nonce_value, $this->get_post_meta_name( $id ) );
		foreach ( $this->meta_boxes[ $post_type_name ][ $id ]['fields'] as $one ) {
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
		$columns['menu_order'] = __( 'Order', 'THEME_SLUG' );
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
}

