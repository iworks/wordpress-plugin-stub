<?php
/**
 * Class for Post Type: PAGE
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_page extends iworks_wordpress_plugin_stub_posttype_base {

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
		/**
		 * Post Type Name
		 *
		 * @since 1.0.0
		 */
		$this->posttype_name = preg_replace( '/^iworks_wordpress_plugin_stub_posttype_/', '', __CLASS__ );
		$this->register_class_custom_posttype_name( $this->posttype_name );
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'load-post-new.php', array( $this, 'admin_enqueue' ) );
		add_action( 'load-post.php', array( $this, 'admin_enqueue' ) );
		add_action( 'save_post', array( $this, 'save_icon' ) );
		// add_action( 'save_post', array( $this, 'save_meta_description' ) );
		add_action( 'wp_ajax_nopriv_opi_systems', array( $this, 'ajax_get_opi_systems' ) );
		add_action( 'wp_ajax_opi_systems', array( $this, 'ajax_get_opi_systems' ) );
		/**
		 * WordPress Plugin Stub Hooks
		 */
		add_filter( 'opi_pib_theme_system_icon', array( $this, 'get_system_icon' ), 10, 3 );
		add_filter( 'opi_pib_get_systems', array( $this, 'get_all_systems' ), 10, 2 );
		add_filter( 'opi_pib_theme_system_url', array( $this, 'get_system_url' ), 10, 2 );
		/**
		 * settings
		 */
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'opinion-data' => array(
				'title'  => __( 'Opinion Data', 'wordpress-plugin-stub' ),
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

	public function action_init_register_post_type() {}
	public function action_init_register_taxonomy() {}

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
							__( 'Read more about the system: %s', 'wordpress-plugin-stub' ),
							get_the_title()
						)
					),
					esc_html__( 'Find out more', 'wordpress-plugin-stub' )
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
							__( 'Logo: %s', 'wordpress-plugin-stub' ),
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
						__( 'Logo: %s', 'wordpress-plugin-stub' ),
						get_the_title( $post_id )
					)
				)
			);
		}
		return sprintf(
			'<span class="logo"><img src="%s" alt="%s" class="logo" /></span>',
			esc_url( $src ),
			sprintf(
				__( 'Logo %s', 'wordpress-plugin-stub' ),
				get_the_title( $post_id )
			)
		);
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
		$content = __( 'There is no entries!', 'wordpress-plugin-stub' );
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
			wp_send_json_error( __( 'Security check failed!', 'wordpress-plugin-stub' ) );
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
							$button_text = __( 'See other models', 'wordpress-plugin-stub' );
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
				wpautop( esc_html__( 'Something went wrong!', 'wordpress-plugin-stub' ) )
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
				$button_text = 'en' === $language ? esc_html( 'Show all' ) : esc_html__( 'Show all', 'wordpress-plugin-stub' );
				switch ( $type ) {
					case 'systems':
						$button_text = 'en' === $language ? esc_html( 'Show all systems' ) : esc_html__( 'Show all systems', 'wordpress-plugin-stub' );
						break;
					case 'trainings':
						$button_text = 'en' === $language ? esc_html( 'See training offer' ) : esc_html__( 'See training offer', 'wordpress-plugin-stub' );
						break;
					case 'publications':
						$button_text = 'en' === $language ? esc_html( 'Show all publications' ) : esc_html__( 'Show all publications', 'wordpress-plugin-stub' );
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

