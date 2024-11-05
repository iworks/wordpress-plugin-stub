<?php

require_once 'class-iworks-post-type.php';

class iWorks_Post_Type_Post extends iWorks_Post_Type {

	/**
	 * Post Type
	 *
	 * @since 2.0.0
	 * @var string $post_type Post Type
	 */
	protected $post_type = 'post';

	/**
	 * Option name, used to save data on postmeta table.
	 *
	 * @since 2.0.0
	 * @var string $option_name_post_gallery Option name post_gallery.
	 */
	private $option_name_post_gallery = '_opi_post_gallery';

	public function __construct() {
		parent::__construct();
		add_action( 'add_meta_boxes_' . $this->post_type, array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'load-post-new.php', array( $this, 'admin_enqueue' ) );
		add_action( 'load-post.php', array( $this, 'admin_enqueue' ) );
		add_action( 'save_post', array( $this, 'save_post_gallery' ) );
		add_filter( 'get_the_terms', array( $this, 'add_hash' ), 10, 3 );
		add_filter( 'opi_related_list', array( $this, 'get_related' ), 10, 2 );
	}

	/**
	 * Related posts.
	 *
	 * @since 1.3.4
	 *
	 * @return string
	 */
	public function get_related( $content, $post_ID ) {
		if ( ! class_exists( 'RP4WP_Post_Link_Manager' ) ) {
			return $content;
		}
		$lm            = new RP4WP_Post_Link_Manager();
		$related_posts = $lm->get_children( $post_ID, array( 'posts_per_page' => 3 ) );
		if ( count( $related_posts ) < 1 ) {
			return $content;
		}
		$content .= '<div class="related-posts">';
		$content .= sprintf( '<p class="related-posts-title">%s</p>', esc_html__( 'Related posts:', 'THEME_SLUG' ) );
		$content .= '<div class="related-posts-container">';
		$i        = 0;
		foreach ( $related_posts as $post ) {
			if ( 0 < $i++ ) {
				$content .= '<span class="separator"></span>';
			}
			// Setup the postdata
			setup_postdata( $post );
			// Output the linked post
			$content .= '<div class="element">';
			if ( 1 == RP4WP::get()->settings->get_option( 'display_image' ) ) {
				if ( has_post_thumbnail( $post->ID ) ) {
					$content .= "<div class='related-post-image'>";
					$content .= "<a href='" . get_permalink( $post->ID ) . "'>";
					$content .= get_the_post_thumbnail( $post->ID, $this->thumbnail_size_related );
					$content .= '</a>';
					$content .= '</div>';
				}
			}
			$content .= '<div class="related-post-date">';
			$content .= get_the_date( 'd.m.Y' );
			$content .= '</div>';
			$content .= '<h4 class="related-title">';
			$content .= sprintf(
				'<a href="%s">%s</a>',
				get_permalink( $post->ID ),
				$post->post_title
			);
			$content .= '</h4>';
			$content .= '<div class="excerpt">';
			$content .= get_the_excerpt( $post->ID );
			$content .= '</div>';
			$content .= '</div>';
		}
		// Reset the postdata
		wp_reset_postdata();
		// Close the wrapper div
		$content .= '</div>';
		$content .= '</div>';
		return $content;
	}

	/**
	 * Filters the list of terms attached to the given post.
	 *
	 * @since 1.3.4
	 *
	 * @param WP_Term[]|WP_Error $terms Array of attached terms, or WP_Error on failure.
	 * @param int $post_ID  Post ID.
	 * @param string $taxonomy Name of the taxonomy.
	 */
	public function add_hash( $terms, $post_ID, $taxonomy ) {
		if ( empty( $terms ) ) {
			return $terms;
		}
		if ( 'post_tag' !== $taxonomy ) {
			return $terms;
		}
		if ( 'post' !== get_post_type( $post_ID ) ) {
			return $terms;
		}
		foreach ( $terms as $one ) {
			if ( preg_match( '/^#/', $one->name ) ) {
				continue;
			}
			$one->name = '#' . $one->name;
		}
		return $terms;
	}

	/**
	 * Register meta box for images
	 *
	 * @since 2.0.0
	 */
	public function add_meta_boxes() {
		$post_types = array( $this->post_type );
		add_meta_box(
			'opi-post_gallery',
			__( 'Post gallery', 'THEME_SLUG' ),
			array( $this, 'html_post_gallery' ),
			apply_filters( 'opi_metabox_post_post_gallery', $post_types ),
			'normal',
			'default'
		);
		$this->add_meta_box_meta_description( $this->post_type );
	}

	/**
	 * HTML helper for meta box post_gallerys.
	 *
	 * @since 1.0.1
	 *
	 * @param WP_Post $post Edited post object.
	 */
	public function html_post_gallery( $post ) {
		wp_nonce_field( $this->option_name_post_gallery . '_nonce', 'post_gallery_nonce' );
		$value = intval( get_post_meta( $post->ID, $this->option_name_post_gallery, true ) );
		echo '<div class="opi-pictures-list form-table">';
		$value = get_post_meta( $post->ID, $this->option_name_post_gallery );
		foreach ( $value as $attachement_id ) {
			$src = wp_get_attachment_image_src( $attachement_id, 'small-thumbnail' );
			if ( empty( $src ) ) {
				continue;
			}
			$this->html_post_gallery_row( $attachement_id, $src[0] );
		}
		/**
		 * empty, add empty row then
		 */
		if ( empty( $value ) ) {
			$this->html_post_gallery_row();
		}
		echo '</div>';
		printf(
			'<button class="button opi-pictures-list-add-new">%s</button>',
			esc_html__( 'Add picture', 'THEME_SLUG' )
		);
		echo '<script type="text/html" id="tmpl-opi-picture-author-row">';
		$this->html_post_gallery_row( '{{{data.value}}}', '{{{data.src}}}' );
		echo '</script>';
	}

	/**
	 * One row helper for picture author.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Row value.
	 */
	private function html_post_gallery_row( $value = '', $src = '' ) {
		?>
<div class="image-wrapper<?php echo esc_attr( empty( $src ) ? '' : ( 0 < $value ? ' has-file' : ' has-old-file' ) ); ?>">
	<div role="button" class="opi-image-preview" style="background-image: url(<?php echo esc_attr( $src ); ?>);"></div>
	<button type="button" class="button-select-image" aria-label="
		<?php
		esc_attr_e(
			'Select file',
			'THEME_SLUG'
		);
		?>
																	"><span class="dashicons dashicons-format-image"></span></button>
	<button type="button" class="image-reset" aria-label="<?php esc_attr_e( 'Remove file', 'THEME_SLUG' ); ?>"><span class="dashicons dashicons-trash"></span></button>
	<input type="hidden" name="<?php echo esc_attr( $this->option_name_post_gallery ); ?>[]" value="<?php echo esc_attr( $value ); ?>" class="attachment-id" />
</div>
		<?php
	}
	/**
	 * Save pictures authors data.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public function save_post_gallery( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, 'post_gallery_nonce' );
		if ( ! wp_verify_nonce( $nonce, $this->option_name_post_gallery . '_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		delete_post_meta( $post_id, $this->option_name_post_gallery );
		$data = $_POST[ $this->option_name_post_gallery ];
		foreach ( $data as $one ) {
			$value = intval( $one );
			if ( 0 < $value ) {
				add_post_meta( $post_id, $this->option_name_post_gallery, $value );
			}
		}
	}

	/**
	 * Register plugin assets.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		wp_register_style(
			strtolower( __CLASS__ ),
			$this->url . '/assets/css/admin/post.css',
			array(),
			$this->version
		);
		wp_register_script(
			strtolower( __CLASS__ ),
			$this->url . '/assets/scripts/admin-post.js',
			array(
				'jquery',
				'jquery-ui-sortable',
				'media-query',
			),
			$this->version,
			true
		);
	}

	/**
	 * Enqueue plugin assets.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue() {
		wp_enqueue_style( strtolower( __CLASS__ ) );
		wp_enqueue_script( strtolower( __CLASS__ ) );
	}
}
