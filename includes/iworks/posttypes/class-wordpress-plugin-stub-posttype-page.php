<?php

require_once 'class-iworks-post-type.php';

class iWorks_Post_Type_Page extends iWorks_Post_Type {

	/**
	 * Option name, used to save data on postmeta table.
	 *
	 * @since 1.0.0
	 * @var string $option_name_icon Option name ICON.
	 */
	private $option_name_icon = '_opi_icon';

	/**
	 * Option name, used to save data on postmeta table.
	 *
	 * @since 1.0.0
	 * @var string $option_name_url Option name URL.
	 */
	private $option_name_url = '_opi_url';

	/**
	 * Option name, used to save data on postmeta table.
	 *
	 * @since 1.0.0
	 * @var string $option_name_class Option name class.
	 */
	private $option_name_class = '_opi_class';

	/**
	 * Option name, used to save data on postmeta table.
	 *
	 * @since 1.0.0
	 * @var string $option_name_numbers Option name numbers.
	 */
	private $option_name_numbers = '_opi_numbers';

	public function __construct() {
		parent::__construct();
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'load-post-new.php', array( $this, 'admin_enqueue' ) );
		add_action( 'load-post.php', array( $this, 'admin_enqueue' ) );
		add_action( 'save_post', array( $this, 'save_icon' ) );
		add_action( 'save_post', array( $this, 'save_meta_description' ) );
		add_action( 'wp_ajax_nopriv_opi_systems', array( $this, 'ajax_get_opi_systems' ) );
		add_action( 'wp_ajax_opi_systems', array( $this, 'ajax_get_opi_systems' ) );
		add_filter( 'opi_pib_theme_system_icon', array( $this, 'get_system_icon' ), 10, 3 );
		add_filter( 'opi_pib_get_systems', array( $this, 'get_all_systems' ), 10, 2 );
		add_filter( 'opi_pib_theme_system_url', array( $this, 'get_system_url' ), 10, 2 );
	}

	public function get_all_systems( $content, $parent_id ) {
		$the_query = $this->get_subpages( $parent_id, -1 );
		$content   = '<div class="systems">';
		// The Loop
		$i = 0;
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$content .= sprintf(
				'<article class="%s" id="post-%d">',
				esc_attr( implode( ' ', get_post_class() ) ),
				esc_attr( get_the_ID() )
			);
			$content .= $this->get_system_icon( '', get_the_ID() );
			$content .= get_the_content();
			$url      = esc_url( get_post_meta( get_the_ID(), $this->option_name_url, true ) );
			if ( ! empty( $url ) ) {
				$content .= sprintf(
					'<a class="button button-small button-invert button-light" href="%s" title="%s">%s</a>',
					$url,
					esc_attr(
						sprintf(
							__( 'Read more about the system: %s', 'THEME_SLUG' ),
							get_the_title()
						)
					),
					esc_html__( 'Find out more', 'THEME_SLUG' )
				);
			}
			$content .= '</article>';
			if ( 0 == ++$i % 3 ) {
				$content .= '<div class="split"></div>';
			}
		}
		$content .= '</div>';
		/* Restore original Post Data */
		wp_reset_postdata();
		return $content;
	}

	public function get_system_url( $content, $post_id ) {
		$url = esc_url( get_post_meta( $post_id, $this->option_name_url, true ) );
		if ( empty( $url ) ) {
			return $content;
		}
		return $url;
	}

	public function get_system_icon( $content, $post_id, $svg = 'url' ) {
		$src = $this->get_src( $post_id );
		if ( empty( $src ) ) {
			return $content;
		}
		$url = $this->get_system_url( null, $post_id );
		/**
		 * return SVG into html
		 */
		if ( 'src' === $svg && preg_match( '/\.svg$/', $src ) ) {
			$attachment_id = get_post_meta( $post_id, $this->option_name_icon, true );
			$image         = get_attached_file( $attachment_id );
			$svg           = $this->get_file( $image );
			if ( ! empty( $svg ) ) {
				if ( empty( $url ) ) {
					return $svg;
				}
				$string = sprintf(
					'<svg role="img" aria-label="Logo: %s"',
					esc_attr( get_the_title( $post_id ) )
				);
				$svg    = preg_replace( '/<svg/', $string, $svg );
				return sprintf(
					'<span title="%s" class="logo">%s</span>',
					esc_attr(
						sprintf(
							__( 'Logo: %s', 'THEME_SLUG' ),
							get_the_title( $post_id )
						)
					),
					$svg
				);
			}
		}
		if ( empty( $url ) ) {
			return sprintf(
				'<img src="%s" alt="%s" class="logo" />',
				esc_url( $src ),
				esc_attr(
					sprintf(
						__( 'Logo: %s', 'THEME_SLUG' ),
						get_the_title( $post_id )
					)
				)
			);
		}
		return sprintf(
			'<span class="logo"><img src="%s" alt="%s" class="logo" /></span>',
			esc_url( $src ),
			sprintf(
				__( 'Logo %s', 'THEME_SLUG' ),
				get_the_title( $post_id )
			)
		);
	}

	/**
	 * Register meta box for pictures authors.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		$post_types = array( $this->post_type );
		add_meta_box(
			'opi-icon',
			__( 'Post data', 'THEME_SLUG' ),
			array( $this, 'html_icon' ),
			apply_filters( 'opi_metabox_post_icon', $post_types ),
			'normal',
			'default'
		);
		$this->add_meta_box_meta_description( $this->post_type );
	}

	private function get_src( $post_id ) {
		$src = null;
		if ( isset( $this->images[ $post_id ] ) ) {
			$src = $this->images[ $post_id ];
		}
		$attachment_id = get_post_meta( $post_id, $this->option_name_icon, true );
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
	 * Save pictures authors data.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_id Post ID.
	 */
	public function save_icon( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = filter_input( INPUT_POST, 'icon_nonce', FILTER_DEFAULT );
		if ( ! wp_verify_nonce( $nonce, $this->option_name_icon . '_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		delete_post_meta( $post_id, $this->option_name_icon );
		$value = filter_input( INPUT_POST, $this->option_name_icon . '_image_id', FILTER_SANITIZE_NUMBER_INT );
		if ( 0 < $value ) {
			add_post_meta( $post_id, $this->option_name_icon, $value, true );
		}
		/**
		 * URL
		 */
		$value = filter_input( INPUT_POST, $this->option_name_url, FILTER_SANITIZE_URL );
		$this->update_meta( $post_id, $this->option_name_url, $value );
		/**
		 * class
		 */
		$value = filter_input( INPUT_POST, $this->option_name_class, FILTER_DEFAULT );
		$this->update_meta( $post_id, $this->option_name_class, $value );
		/**
		 * numbers
		 */
		$value = filter_input( INPUT_POST, $this->option_name_numbers, FILTER_DEFAULT );
		$this->update_meta( $post_id, $this->option_name_numbers, $value );
	}

	/**
	 * HTML helper for meta box icons.
	 *
	 * @since 1.0.1
	 *
	 * @param WP_Post $post Edited post object.
	 */
	public function html_icon( $post ) {
		wp_nonce_field( $this->option_name_icon . '_nonce', 'icon_nonce' );
		$value = intval( get_post_meta( $post->ID, $this->option_name_icon, true ) );
		$src   = $this->get_src( $post->ID );
		if ( empty( $src ) ) {
			$src = '';
		}
		wp_enqueue_media();
		?>
<div class="image-wrapper<?php echo esc_attr( empty( $src ) ? '' : ( 0 < $value ? ' has-file' : ' has-old-file' ) ); ?>">
	<p>
		<button type="button" class="button button-select-image"><?php esc_html_e( 'Upload image', 'THEME_SLUG' ); ?></button>
		<button type="button" class="image-reset" aria-label="<?php esc_attr_e( 'Remove file', 'THEME_SLUG' ); ?>"><span class="dashicons dashicons-trash"></span></button>
	</p>
	<div class="ai-upload-image" aria-hidden="true">
	<div role="button" class="ai-image-preview" style="background-image: url(<?php echo esc_attr( $src ); ?>);"></div>
	</div>
	<input type="hidden" name="<?php echo esc_attr( $this->option_name_icon ); ?>_image_id" value="<?php echo esc_attr( $value ); ?>" class="attachment-id" />
</div>
		<?php
		/**
		 * url
		 */
		$value = esc_url( get_post_meta( $post->ID, $this->option_name_url, true ) );
		?>
<div class="url-wrapper">
	<label><?php esc_html_e( 'Enter target url (works only for "systems tab").', 'THEME_SLUG' ); ?><br />
	<input type="url" name="<?php echo esc_attr( $this->option_name_url ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text code" />
	</label>
</div>
		<?php
		/**
		 * class
		 */
		$value = esc_attr( get_post_meta( $post->ID, $this->option_name_class, true ) );
		?>
<div class="class-wrapper">
	<label><?php esc_html_e( 'Extra classes (works only for "systems tab").', 'THEME_SLUG' ); ?><br />
	<input type="class" name="<?php echo esc_attr( $this->option_name_class ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text code" />
	</label>
</div>
		<?php
		/**
		 * numbers
		 */
		$value = esc_attr( get_post_meta( $post->ID, $this->option_name_numbers, true ) );
		?>
<div class="numbers-wrapper">
	<label><?php esc_html_e( 'Show numbers (works only for "systems tab").', 'THEME_SLUG' ); ?><br />
	<input type="numbers" name="<?php echo esc_attr( $this->option_name_numbers ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text code" />
	</label>
</div>
		<?php
	}

	/**
	 * Register plugin assets.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		wp_register_style(
			strtolower( __CLASS__ ),
			get_stylesheet_directory_uri() . '/assets/css/admin/page.css',
			array(),
			$this->version
		);
		wp_register_script(
			strtolower( __CLASS__ ),
			get_stylesheet_directory_uri() . '/assets/scripts/admin-page.js',
			array(
				'jquery',
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

	private function get_subpages( $post_parent, $posts_per_page = 3 ) {
		$args    = array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'post_parent'      => $post_parent,
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'posts_per_page'   => $posts_per_page,
			'suppress_filters' => true,
		);
		$content = __( 'There is no entries!', 'THEME_SLUG' );
		return new WP_Query( $args );
	}

	public function ajax_get_opi_systems() {
		$id             = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$language       = filter_input( INPUT_POST, 'language', FILTER_DEFAULT );
		$nonce          = filter_input( INPUT_POST, 'nonce', FILTER_DEFAULT );
		$type           = filter_input( INPUT_POST, 'type', FILTER_DEFAULT );
		$posts_per_page = filter_input( INPUT_POST, 'posts_per_page', FILTER_DEFAULT );
		$action         = $this->get_nonce_name( $id, 'systems' );
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error( __( 'Security check failed!', 'THEME_SLUG' ) );
		}
		if ( empty( $posts_per_page ) ) {
			$posts_per_page = 3;
		}
		if ( empty( $type ) ) {
			$type = 'default';
		}
		if ( ! empty( $language ) ) {
			global $sitepress;
			if ( is_object( $sitepress ) ) {
				$sitepress->switch_lang( $language );
			}
		}
		/**
		 * button
		 */
		$add_button  = true;
		$button_text = $button_url = false;
		$content     = '';
		/**
		 * get content
		 */
		switch ( $type ) {
			case 'publications':
				$args    = array(
					'posts_per_page' => $posts_per_page,
					'wp_doing_ajax'  => true,
					'language'       => $language,
				);
				$content = apply_filters( 'opi_pib_get_systems_publications', '', $args );
				break;
			case 'projects':
				$args    = array(
					'posts_per_page' => $posts_per_page,
					'wp_doing_ajax'  => true,
					'language'       => $language,
				);
				$content = apply_filters( 'opi_pib_get_opi_projects', '', $args );
				break;
			case 'trainings':
				$content = get_the_content( null, false, $id );
				break;
			case 'publishing':
				$options = $this->get_front_page_settings();
				if (
					is_array( $options )
				&& isset( $options['main-page-publishing'] )
				&& 'page' === get_post_type( $options['main-page-publishing'] )
				) {
					$post_id  = apply_filters( 'wpml_object_id', $options['main-page-publishing'] );
					$post     = get_post( $post_id );
					$content .= sprintf(
						'<article class="%s" id="post-%d">',
						esc_attr( implode( ' ', get_post_class( '', $options['main-page-publishing'] ) ) ),
						$options['main-page-publishing']
					);
					$content .= apply_filters( 'the_content', $post->post_content );
					$content .= '</article>';
				}
				break;
			default:
				$the_query = $this->get_subpages( $id, $posts_per_page );
				// The Loop
				if ( $the_query->have_posts() ) {
					ob_start();
					$i = 1;
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						set_query_var( 'i', $i++ );
						get_template_part( 'template-parts/content', 'system' );
					}
					$content = ob_get_contents();
					ob_end_clean();
				} else {
					$post = get_post( $id );
					if ( is_a( $post, 'WP_Post' ) ) {
						$class = get_post_meta( $id, $this->option_name_class, true );
						if ( 'git-readme' === $class ) {
							$content .= '<div class="git-readme">';
						}
						$content     .= sprintf(
							'<span class="section-title">%s</span>',
							get_the_title( $id )
						);
						$content     .= sprintf(
							'<article class="%s" id="post-%d">',
							esc_attr( implode( ' ', get_post_class( '', $id ) ) ),
							$id
						);
						$post_content = apply_filters( 'the_content', $post->post_content );
						if ( 'git-readme' === $class ) {
							$split              = '<h3';
							$post_content_array = preg_split( '/' . $split . '/', $post_content );
							if ( is_array( $post_content_array ) && ! empty( $post_content_array ) ) {
								for ( $i = 1; $i < 4; $i++ ) {
									if ( isset( $post_content_array[0] ) ) {
										if ( ! empty( $post_content_array ) ) {
											if ( 1 < $i ) {
												$content .= $split;
											}
											$content .= array_shift( $post_content_array );
										}
									}
								}
							}
						} else {
							$content .= $post_content;
						}
						$content .= '</article>';
						if ( 'git-readme' === $class ) {
							$content    .= '</div>';
							$button_text = __( 'See other models', 'THEME_SLUG' );
						}
					}
				}
		}
		$classes = array(
			$type,
		);
		if ( empty( $content ) ) {
			$content   = sprintf(
				'<div>%s</div>',
				wpautop( esc_html__( 'Something went wrong!', 'THEME_SLUG' ) )
			);
			$classes[] = 'error';
			$classes[] = 'notice';
		}
		$class = get_post_meta( $id, $this->option_name_class, true );
		if ( ! empty( $class ) ) {
			$classes[] = $class;
		}
		$content = sprintf(
			'<div class="opi-system-container %s"><div class="%s">%s</div>',
			esc_attr( $type ),
			implode( ' ', get_post_class( $classes, $id ) ),
			$content
		);
		/**
		 * button
		 */
		if ( $add_button ) {
			if ( empty( $button_text ) ) {
				$button_text = 'en' === $language ? esc_html( 'Show all' ) : esc_html__( 'Show all', 'THEME_SLUG' );
				switch ( $type ) {
					case 'systems':
						$button_text = 'en' === $language ? esc_html( 'Show all systems' ) : esc_html__( 'Show all systems', 'THEME_SLUG' );
						break;
					case 'trainings':
						$button_text = 'en' === $language ? esc_html( 'See training offer' ) : esc_html__( 'See training offer', 'THEME_SLUG' );
						break;
					case 'publications':
						$button_text = 'en' === $language ? esc_html( 'Show all publications' ) : esc_html__( 'Show all publications', 'THEME_SLUG' );
						break;
				}
			}
			$url = '';
			if ( 0 < $id ) {
				$url = get_permalink( $id );
			}
			$url = apply_filters( 'opi_pib_theme_system_tab_button_more_url', $url, $type, $id, $language );
			if ( $url ) {
				$content .= sprintf(
					'<p class="more %s"><a href="%s" class="button">%s</a></p>',
					esc_attr( $type ),
					$url,
					apply_filters( 'opi_pib_theme_system_tab_button_more_text', $button_text, $type, $id, $language )
				);
			}
		}
		$content .= '</div>';
		/* Restore original Post Data */
		wp_reset_postdata();
		wp_send_json_success( array( 'html' => $content ) );
	}

}

