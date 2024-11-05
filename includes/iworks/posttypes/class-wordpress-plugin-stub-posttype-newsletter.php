<?php

require_once 'class-iworks-post-type.php';

class iWorks_Post_Type_Newsletter extends iWorks_Post_Type {

	private array $data                                     = array();
	private string $post_type_name                          = 'opi_newsletter';
	private string $option_name_button                      = '_newsletter_generate_button';
	private string $option_name_banner_active               = '_newsletter_banner_active';
	private string $option_name_banner_img                  = '_newsletter_banner_img';
	private string $option_name_banner_url                  = '_newsletter_banner_url';
	private string $option_name_banner_title                = '_newsletter_banner_title';
	private string $option_name_news_active                 = '_newsletter_news_active';
	private string $option_name_news                        = '_newsletter_news';
	private string $option_name_news_img                    = '_newsletter_news_img';
	private string $option_name_systems_active              = '_newsletter_systems_active';
	private string $option_name_systems                     = '_newsletter_systems';
	private string $option_name_systems_img                 = '_newsletter_systems_img';
	private string $option_name_events_active               = '_newsletter_events_active';
	private string $option_name_events                      = '_newsletter_events';
	private string $option_name_events_img                  = '_newsletter_events_img';
	private string $option_name_navoica_active              = '_newsletter_navoica_active';
	private string $option_name_navoica                     = '_newsletter_navoica';
	private string $option_name_navoica_img                 = '_newsletter_navoica_img';
	private string $option_name_eu_projects_active          = '_newsletter_eu_projects_active';
	private string $option_name_eu_projects                 = '_newsletter_eu_projects';
	private string $option_name_eu_projects_img             = '_newsletter_eu_projects_img';
	private string $option_name_number_of_month             = '_newsletter_number_of_month';
	private string $option_name_number_of_month_description = '_newsletter_number_of_month_description';
	private string $option_name_questions_active            = '_newsletter_question_active';
	private string $option_name_first_name_last_name        = '_newsletter_first_name_last_name';
	private string $option_name_job_position                = '_newsletter_job_position';
	private string $option_name_questions_img               = '_newsletter_questions_img';
	private string $option_name_question_1                  = '_newsletter_question_1';
	private string $option_name_question_2                  = '_newsletter_question_2';
	private string $option_name_question_3                  = '_newsletter_question_3';
	private string $option_name_answer_1                    = '_newsletter_answer_1';
	private string $option_name_answer_2                    = '_newsletter_answer_2';
	private string $option_name_answer_3                    = '_newsletter_answer_3';
	private string $newsletter_opi_base_dir                 = 'newsletter-opi';
	private string $section_name                            = 'newsletter';
	private string $option_group_newsletter                 = 'newsletter-options';
	private string $newsletter_settings_page_capability     = 'manage_categories';

	public function __construct() {
		parent::__construct();
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'number_of_month_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'questions_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'banner_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'news_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'systems_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'events_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'navoica_save' ) );
		add_action( sprintf( 'save_post_%s', $this->post_type_name ), array( $this, 'eu_projects_save' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_media_menu' ) );
		add_action( 'wp_ajax_generate_newsletter', array( $this, 'ajax_generate_newsletter' ) );
		add_action( 'after_setup_theme', array( $this, 'set_data' ) );
		add_action( 'wp_localize_script_opi_pib_theme', array( $this, 'add_newsletter_data' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_menu', array( $this, 'options_page' ) );
		add_filter(
			sprintf( 'option_page_capability_%s', $this->option_group_newsletter ),
			array( $this, 'editor_permissions' ),
			10,
			1
		);
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since 2.4.0
	 */
	public function custom_post_type() {
		$labels = array(
			'name'                  => _x( 'Newsletters', 'Post Type General Name', 'THEME_SLUG' ),
			'singular_name'         => _x( 'Newsletter', 'Post Type Singular Name', 'THEME_SLUG' ),
			'menu_name'             => __( 'Newsletters', 'THEME_SLUG' ),
			'name_admin_bar'        => __( 'Newsletters', 'THEME_SLUG' ),
			'archives'              => __( 'Newsletters', 'THEME_SLUG' ),
			'all_items'             => __( 'Newsletters', 'THEME_SLUG' ),
			'add_new_item'          => __( 'Add New Newsletter', 'THEME_SLUG' ),
			'add_new'               => __( 'Add New', 'THEME_SLUG' ),
			'new_item'              => __( 'New Newsletter', 'THEME_SLUG' ),
			'edit_item'             => __( 'Edit Newsletter', 'THEME_SLUG' ),
			'update_item'           => __( 'Update Newsletter', 'THEME_SLUG' ),
			'view_item'             => __( 'View Newsletter', 'THEME_SLUG' ),
			'view_items'            => __( 'View Newsletter', 'THEME_SLUG' ),
			'search_items'          => __( 'Search Newsletter', 'THEME_SLUG' ),
			'not_found'             => __( 'Not found', 'THEME_SLUG' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'THEME_SLUG' ),
			'items_list'            => __( 'Newsletter list', 'THEME_SLUG' ),
			'items_list_navigation' => __( 'Newsletter list navigation', 'THEME_SLUG' ),
			'filter_items_list'     => __( 'Filter items list', 'THEME_SLUG' ),
		);
		$args   = array(
			'can_export'          => true,
			'capability_type'     => 'page',
			'description'         => __( 'Newsletter', 'THEME_SLUG' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'label'               => __( 'Newsletters', 'THEME_SLUG' ),
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-media-document',
			'public'              => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => apply_filters( 'opi_post_type_show_in_menu' . $this->post_type_name, 'edit.php' ),
			'show_in_nav_menus'   => false,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'supports'            => array( 'title' ),
		);
		register_post_type( $this->post_type_name, $args );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'opi-generate-newsletter-button',
			_x( 'Generate newsletter', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_generate_newsletter' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-banner',
			_x( 'Banner', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_banner' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-number-of-month',
			_x( 'Number of month', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_number_of_month' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-news',
			_x( 'News', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_news' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-systems',
			_x( 'Our systems', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_systems' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-events',
			_x( 'Events', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_events' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-navoica',
			_x( 'Navoica', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_navoica' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-eu-projects',
			_x( 'EU projects', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_eu_projects' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);

		add_meta_box(
			'opi-newsletter-questions',
			_x( '3 questions to', 'newsletter', 'THEME_SLUG' ),
			array( $this, 'html_questions' ),
			array( $this->post_type_name ),
			'normal',
			'default'
		);
	}

	public function html_generate_newsletter( $post ) {
		wp_nonce_field( $this->option_name_button, 'generate_newsletter_nonce' );
		printf(
			'<button id="%s" type="button" class="button">%s</button>',
			'generate-newsletter',
			_x( 'Generate newsletter zip package', 'newsletter', 'THEME_SLUG' ),
		);

		$dialog_template = '<div id="newsletter-dialog" title="%s"></div>';
		printf( $dialog_template, esc_html_x( 'Correct errors', 'newsletter', 'THEME_SLUG' ) );
	}

	public function html_banner( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'banner' ), 'banner_nonce' );
		$section_active = $this->get_value( $post->ID, $this->option_name_banner_active );
		$banner_img     = $this->get_value( $post->ID, $this->option_name_banner_img );
		$banner_link    = $this->get_value( $post->ID, $this->option_name_banner_url );
		$banner_title   = $this->get_value( $post->ID, $this->option_name_banner_title );

		$this->html_section_active( $this->option_name_banner_active, $section_active );
		$this->html_section_image( 'banner', $this->option_name_banner_img, $banner_img );

		printf(
			'<label>%s <input type="text" name="%s" value="%s" style="width: 100%%;"></label>',
			esc_html_x(
				'Link - at the beginning, enter https:// eg. https://opi.org.pl',
				'newsletter',
				'THEME_SLUG'
			),
			esc_attr( $this->option_name_banner_url ),
			$banner_link ? esc_html( $banner_link ) : ''
		);

		printf(
			'<label>%s <input type="text" name="%s" value="%s" style="width: 100%%;"></label>',
			esc_html_x( 'Title - description of the event.', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_banner_title ),
			$banner_title ? esc_html( $banner_title ) : ''
		);
	}

	public function banner_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'banner_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'banner' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$banner_active = $_REQUEST[ $this->option_name_banner_active ] ?? 0;
		$banner_img    = $_REQUEST[ $this->option_name_banner_img ] ?? 0;
		$banner_link   = $_REQUEST[ $this->option_name_banner_url ] ? trim( $_REQUEST[ $this->option_name_banner_url ] ) : 0;
		$banner_title  = $_REQUEST[ $this->option_name_banner_title ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_banner_active,
			intval( $banner_active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_banner_img,
			intval( $banner_img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_banner_url,
			filter_var(
				$banner_link,
				FILTER_VALIDATE_URL
			) === false ? '' : sanitize_url( $banner_link )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_banner_title,
			wp_strip_all_tags( $banner_title )
		);
	}

	public function html_news( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'news' ), 'news_nonce' );
		$section_active    = $this->get_value( $post->ID, $this->option_name_news_active );
		$news              = $this->get_value( $post->ID, $this->option_name_news );
		$news_img          = $this->get_value( $post->ID, $this->option_name_news_img );
		$another_item_text = esc_html_x( 'Add another news', 'newsletter news', 'THEME_SLUG' );
		$remove_item_text  = esc_html_x( 'Remove news', 'newsletter news', 'THEME_SLUG' );

		$this->html_section_active( $this->option_name_news_active, $section_active );
		$this->html_section_image( 'news', $this->option_name_news_img, $news_img );
		$this->html_section_items( 'news', $this->option_name_news, $news, $another_item_text, $remove_item_text );
	}

	public function news_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'news_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'news' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$news_active = $_REQUEST[ $this->option_name_news_active ] ?? 0;
		$news_items  = $_REQUEST[ $this->option_name_news ] ?? 0;
		$news_img    = $_REQUEST[ $this->option_name_news_img ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_news_active,
			intval( $news_active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_news_img,
			intval( $news_img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_news,
			$this->sanitized_items_data( $news_items )
		);
	}

	public function html_systems( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'systems' ), 'systems_nonce' );
		$section_active    = $this->get_value( $post->ID, $this->option_name_systems_active );
		$systems_items     = $this->get_value( $post->ID, $this->option_name_systems );
		$systems_img       = $this->get_value( $post->ID, $this->option_name_systems_img );
		$another_item_text = esc_html_x( 'Add another system', 'newsletter news', 'THEME_SLUG' );
		$remove_item_text  = esc_html_x( 'Remove system', 'newsletter news', 'THEME_SLUG' );

		$this->html_section_active( $this->option_name_systems_active, $section_active );
		$this->html_section_image( 'systems', $this->option_name_systems_img, $systems_img );
		$this->html_section_items(
			'systems',
			$this->option_name_systems,
			$systems_items,
			$another_item_text,
			$remove_item_text
		);
	}

	public function systems_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'systems_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'systems' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$systems_active = $_REQUEST[ $this->option_name_systems_active ] ?? 0;
		$systems_items  = $_REQUEST[ $this->option_name_systems ] ?? 0;
		$systems_img    = $_REQUEST[ $this->option_name_systems_img ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_systems_active,
			intval( $systems_active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_systems_img,
			intval( $systems_img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_systems,
			$this->sanitized_items_data( $systems_items )
		);
	}

	public function html_events( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'events' ), 'events_nonce' );
		$section_active    = $this->get_value( $post->ID, $this->option_name_events_active );
		$events_items      = $this->get_value( $post->ID, $this->option_name_events );
		$events_img        = $this->get_value( $post->ID, $this->option_name_events_img );
		$another_item_text = esc_html_x( 'Add another event', 'newsletter news', 'THEME_SLUG' );
		$remove_item_text  = esc_html_x( 'Remove event', 'newsletter news', 'THEME_SLUG' );

		$this->html_section_active( $this->option_name_events_active, $section_active );
		$this->html_section_image( 'events', $this->option_name_events_img, $events_img );
		$this->html_section_items(
			'events',
			$this->option_name_events,
			$events_items,
			$another_item_text,
			$remove_item_text
		);
	}

	public function events_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'events_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'events' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$events_active = $_REQUEST[ $this->option_name_events_active ] ?? 0;
		$events_items  = $_REQUEST[ $this->option_name_events ] ?? 0;
		$events_img    = $_REQUEST[ $this->option_name_events_img ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_events_active,
			intval( $events_active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_events_img,
			intval( $events_img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_events,
			$this->sanitized_items_data( $events_items )
		);
	}

	public function html_navoica( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'navoica' ), 'navoica_nonce' );
		$active            = $this->get_value( $post->ID, $this->option_name_navoica_active );
		$items             = $this->get_value( $post->ID, $this->option_name_navoica );
		$img               = $this->get_value( $post->ID, $this->option_name_navoica_img );
		$another_item_text = esc_html_x( 'Add another navoica element', 'newsletter news', 'THEME_SLUG' );
		$remove_item_text  = esc_html_x( 'Remove navoica element', 'newsletter news', 'THEME_SLUG' );

		$this->html_section_active( $this->option_name_navoica_active, $active );
		$this->html_section_image( 'navoica', $this->option_name_navoica_img, $img );
		$this->html_section_items(
			'navoica',
			$this->option_name_navoica,
			$items,
			$another_item_text,
			$remove_item_text
		);
	}

	public function navoica_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'navoica_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'navoica' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$active = $_REQUEST[ $this->option_name_navoica_active ] ?? 0;
		$items  = $_REQUEST[ $this->option_name_navoica ] ?? 0;
		$img    = $_REQUEST[ $this->option_name_navoica_img ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_navoica_active,
			intval( $active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_navoica_img,
			intval( $img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_navoica,
			$this->sanitized_items_data( $items )
		);
	}

	public function html_eu_projects( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'eu_projects' ), 'eu_projects_nonce' );
		$active            = $this->get_value( $post->ID, $this->option_name_eu_projects_active );
		$items             = $this->get_value( $post->ID, $this->option_name_eu_projects );
		$img               = $this->get_value( $post->ID, $this->option_name_eu_projects_img );
		$another_item_text = esc_html_x( 'Add another EU project', 'newsletter news', 'THEME_SLUG' );
		$remove_item_text  = esc_html_x( 'Remove EU project', 'newsletter news', 'THEME_SLUG' );

		$this->html_section_active( $this->option_name_eu_projects_active, $active );
		$this->html_section_image( 'eu-projects', $this->option_name_eu_projects_img, $img );
		$this->html_section_items(
			'eu-projects',
			$this->option_name_eu_projects,
			$items,
			$another_item_text,
			$remove_item_text
		);
	}

	public function eu_projects_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'eu_projects_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'eu_projects' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$active = $_REQUEST[ $this->option_name_eu_projects_active ] ?? 0;
		$items  = $_REQUEST[ $this->option_name_eu_projects ] ?? 0;
		$img    = $_REQUEST[ $this->option_name_eu_projects_img ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_eu_projects_active,
			intval( $active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_eu_projects_img,
			intval( $img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_eu_projects,
			$this->sanitized_items_data( $items )
		);
	}

	public function html_number_of_month( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'number_of_month' ), 'number_of_month_nonce' );
		$value = $this->get_value( $post->ID, $this->option_name_number_of_month );
		printf(
			'<label>%s <input type="text" name="%s" value="%s" style="width: 100%%;"></label>',
			esc_html_x( 'Number of month', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_number_of_month ),
			$value ? esc_html( $value ) : ''
		);

		$value = $this->get_value( $post->ID, $this->option_name_number_of_month_description );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Explain what this number means and what it refers to', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_number_of_month_description ),
			$value ? esc_textarea( $value ) : ''
		);
	}

	public function number_of_month_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'number_of_month_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'number_of_month' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$this->update_meta(
			$post_id,
			$this->option_name_number_of_month,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_number_of_month ] )
		);
		$this->update_meta(
			$post_id,
			$this->option_name_number_of_month_description,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_number_of_month_description ] )
		);
	}

	public function html_questions( $post ) {
		wp_nonce_field( sprintf( '%s_%s', __CLASS__, 'questions' ), 'questions_nonce' );

		$value_questions_active = $this->get_value( $post->ID, $this->option_name_questions_active );
		printf(
			'<label style="display: block;">%s <input type="checkbox" name="%s" value="1" %s /></label>',
			esc_html_x( 'Add a section to your newsletter', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_questions_active ),
			checked( intval( $value_questions_active ), 1, false )
		);

		$questions_img = $this->get_value( $post->ID, $this->option_name_questions_img );
		$this->html_section_image( 'questions', $this->option_name_questions_img, $questions_img );

		$value_first_name_last_name = $this->get_value( $post->ID, $this->option_name_first_name_last_name );
		printf(
			'<label style="display: block;">%s <input type="text" name="%s" value="%s" style="width: 100%%;"></label>',
			esc_html_x( 'First name and last name', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_first_name_last_name ),
			$value_first_name_last_name ? esc_html( $value_first_name_last_name ) : ''
		);

		$value_job_position = $this->get_value( $post->ID, $this->option_name_job_position );
		printf(
			'<label style="display: block;">%s <input type="text" name="%s" value="%s" style="width: 100%%;"></label>',
			esc_html_x( 'Job position', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_job_position ),
			$value_job_position ? esc_html( $value_job_position ) : ''
		);

		$value_question_1 = $this->get_value( $post->ID, $this->option_name_question_1 );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Question 1', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_question_1 ),
			$value_question_1 ? esc_textarea( $value_question_1 ) : ''
		);

		$value_answer_1 = $this->get_value( $post->ID, $this->option_name_answer_1 );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Answer 1', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_answer_1 ),
			$value_answer_1 ? esc_textarea( $value_answer_1 ) : ''
		);

		$value_question_2 = $this->get_value( $post->ID, $this->option_name_question_2 );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Question 2', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_question_2 ),
			$value_question_2 ? esc_textarea( $value_question_2 ) : ''
		);

		$value_answer_2 = $this->get_value( $post->ID, $this->option_name_answer_2 );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Answer 2', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_answer_2 ),
			$value_answer_2 ? esc_textarea( $value_answer_2 ) : ''
		);

		$value_question_3 = $this->get_value( $post->ID, $this->option_name_question_3 );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Question 3', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_question_3 ),
			$value_question_3 ? esc_textarea( $value_question_3 ) : ''
		);

		$value_answer_3 = $this->get_value( $post->ID, $this->option_name_answer_3 );
		printf(
			'<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
			esc_html_x( 'Answer 3', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $this->option_name_answer_3 ),
			$value_answer_3 ? esc_textarea( $value_answer_3 ) : ''
		);

		//      printf( '<label>%s<textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>',
		//          esc_html__( 'Explain what this number means and what it refers to', 'THEME_SLUG' ),
		//          esc_attr( $this->option_name_number_of_month_description ),
		//          $value ? esc_attr( $value ) : ''
		//      );
	}

	public function questions_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$nonce = $this->sanitize_nonce( 'questions_nonce' );
		if ( ! wp_verify_nonce( $nonce, sprintf( '%s_%s', __CLASS__, 'questions' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$questions_active = $_REQUEST[ $this->option_name_questions_active ] ?? 0;
		$questions_img    = $_REQUEST[ $this->option_name_questions_img ] ?? 0;

		$this->update_meta(
			$post_id,
			$this->option_name_questions_active,
			intval( $questions_active )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_questions_img,
			intval( $questions_img )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_first_name_last_name,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_first_name_last_name ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_job_position,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_job_position ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_question_1,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_question_1 ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_question_2,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_question_2 ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_question_3,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_question_3 ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_answer_1,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_answer_1 ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_answer_2,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_answer_2 ] )
		);

		$this->update_meta(
			$post_id,
			$this->option_name_answer_3,
			wp_strip_all_tags( $_REQUEST[ $this->option_name_answer_3 ] )
		);
	}

	/**
	 * Enqueue plugin assets.
	 *
	 * @since 2.4.0
	 */
	public function admin_enqueue( $hook ) {
		$screen = get_current_screen();

		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}
		if ( $this->post_type_name !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script( strtolower( __CLASS__ ) );
	}

	public function load_media_menu( $hook ) {
		$screen = get_current_screen();
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}
		if ( $this->post_type_name !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
	}

	/**
	 * Register plugin assets.
	 *
	 * @since 2.4.0
	 */
	public function register() {
		wp_register_script(
			strtolower( __CLASS__ ),
			get_stylesheet_directory_uri() . '/assets/scripts/admin-newsletter.js',
			array(
				'jquery',
			),
			$this->version,
			true
		);

		wp_localize_script(
			strtolower( __CLASS__ ),
			'newsletter_admin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'newsletter_download_text'       => _x(
						'Download package',
						'newsletter admin',
						'THEME_SLUG'
					),
					'no_preheader'                   => _x(
						'The preheader is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_newsletter_title'            => _x(
						'Newsletter title is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_subtitle'                    => _x(
						'Newsletter subtitle is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_heading'                     => _x(
						'Heading is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_number_of_month'             => _x(
						'The month number is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_number_of_month_description' => _x(
						'There is no description of the number of the month',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_picture'                     => _x(
						'Picture is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_link'                        => _x(
						'The url is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_title'                       => _x(
						'Link title is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_content'                     => _x(
						'No news content',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_person_name'                 => _x(
						'Name is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_job_position'                => _x(
						'No job position',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_question'                    => _x(
						'The question is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'no_answer'                      => _x(
						'The answer is missing',
						'newsletter admin validation',
						'THEME_SLUG'
					),
					'section'                        => _x(
						'Section',
						'newsletter admin validation',
						'THEME_SLUG'
					),
				),
			)
		);
	}

	/**
	 * Check if excerpt post exists
	 * @since 2.4.0
	 */
	public function ajax_generate_newsletter(): void {
		global $wp_filesystem;
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $this->option_name_button ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Something went wrong.',
						'THEME_SLUG'
					),
				)
			);
		}

		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$section_images                   = array();
		$images_directory_name            = 'images';
		$post                             = get_post( $post_id );
		$file_name                        = sprintf( '%s', sanitize_title( $post->post_title ) );
		$file_name_zip                    = sprintf( '%s-%s.zip', sanitize_title( $post->post_title ), time() );
		$file_path                        = $this->newsletter_opi_base_dir . '/' . $file_name;
		$upload_dir                       = wp_upload_dir();
		$newsletter_opi_directory_basedir = $upload_dir['basedir'] . '/' . $file_path;

		$preheader                   = $this->get_section_value( 'preheader' );
		$newsletter_title            = $post->post_title;
		$subtitle                    = $this->get_section_value( 'subtitle' );
		$number_of_month             = $this->orphans(
			$this->get_value(
				$post_id,
				$this->option_name_number_of_month
			)
		);
		$number_of_month_description = $this->orphans(
			$this->get_value(
				$post_id,
				$this->option_name_number_of_month_description
			)
		);

		$errors = array();

		if ( ! $preheader ) {
			$errors[] = 'preheader';
		}

		if ( ! $newsletter_title ) {
			$errors[] = 'newsletter_title';
		}

		if ( ! $subtitle ) {
			$errors[] = 'subtitle';
		}

		if ( $errors ) {
			wp_send_json_error(
				array(
					'errors'  => $errors,
					'section' => _x( 'Settings', 'newsletter', 'THEME_SLUG' ),
				)
			);
		}

		if ( ! $number_of_month ) {
			$errors[] = 'number_of_month';
		}

		if ( ! $number_of_month_description ) {
			$errors[] = 'number_of_month_description';
		}

		if ( $errors ) {
			wp_send_json_error(
				array(
					'errors'  => $errors,
					'section' => _x( 'Number of month', 'newsletter', 'THEME_SLUG' ),
				)
			);
		}

		$placeholders = array(
			'{{preheader}}'                   => $preheader,
			'{{newsletter_title}}'            => $newsletter_title,
			'{{newsletter_subtitle}}'         => $subtitle,
			'{{number_of_month}}'             => $number_of_month,
			'{{number_of_month_description}}' => $number_of_month_description,
		);

		$value_news_banner_active = $this->get_value( $post->ID, $this->option_name_banner_active );
		$value_questions_active   = $this->get_value( $post->ID, $this->option_name_questions_active );
		$value_systems_active     = $this->get_value( $post->ID, $this->option_name_systems_active );
		$value_events_active      = $this->get_value( $post->ID, $this->option_name_events_active );
		$value_navoica_active     = $this->get_value( $post->ID, $this->option_name_navoica_active );
		$value_eu_projects_active = $this->get_value( $post->ID, $this->option_name_eu_projects_active );
		$value_news_active        = $this->get_value( $post->ID, $this->option_name_news_active );

		$template_heading         = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_heading_section.html' );
		$template_end             = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_end.html' );
		$template_item            = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_item.html' );
		$template_img             = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_img.html' );
		$template_spacer_1        = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_spacer_1.html' );
		$template_spacer_2        = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_spacer_2.html' );
		$template_header          = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_header.html' );
		$template_header_img      = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_header_img.html' );
		$template_number_of_month = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_number_of_month.html' );
		$template_banner          = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_banner.html' );
		$template                 = file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_part_start.html' );

		if ( 1 === intval( $value_news_banner_active ) ) {
			$errors        = array();
			$img_id        = $this->get_value( $post->ID, $this->option_name_banner_img );
			$link          = $this->get_value( $post->ID, $this->option_name_banner_url );
			$title         = $this->get_value( $post->ID, $this->option_name_banner_title );
			$section_image = wp_get_attachment_image_src( $img_id, 'full' );

			if ( ! $link ) {
				$errors[] = 'url';
			}

			if ( ! $title ) {
				$errors[] = 'title';
			}

			if ( ! $section_image ) {
				$errors[] = 'image';
			}

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( 'Banner', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $img_id;
			$banner           = $template_banner;
			$banner           = str_replace(
				'{{banner_img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$banner
			);
			$banner           = str_replace(
				'{{banner_img_height}}',
				$this->image_height( $section_image ),
				$banner
			);
			$banner           = str_replace(
				'{{banner_href_url}}',
				esc_url( $link ),
				$banner
			);
			$banner           = str_replace(
				'{{banner_title}}',
				esc_html( $title ),
				$banner
			);
			//$template         .= $template_spacer_1;
			$template .= $template_header;
			$template .= $template_header_img;
			$template .= $template_number_of_month;
			$template .= $banner;
		} else {
			$template .= $template_header;
			$template .= $template_header_img;
			$template .= $template_number_of_month;
		}

		if ( 1 === intval( $value_news_active ) ) {
			$items         = $this->get_value( $post->ID, $this->option_name_news );
			$img_id        = $this->get_value( $post->ID, $this->option_name_news_img );
			$section_image = wp_get_attachment_image_src( $img_id, 'full' );
			$heading       = $this->get_section_value( 'heading_news' );

			$errors = $this->validate_news_items( $items, $section_image, $heading );

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( 'News', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $img_id;
			$template        .= str_replace( '{{section_title}}', $heading, $template_heading );
			$img_temp         = $template_img;
			$img_temp         = str_replace(
				'{{img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$img_temp
			);
			$img_temp         = str_replace( '{{height}}', $this->image_height( $section_image ), $img_temp );
			$template        .= $img_temp;
			$template        .= $this->html_links( $items, $template_item, $template_spacer_2 );
			$template        .= $template_spacer_1;
		}

		if ( 1 === intval( $value_systems_active ) ) {
			$items         = $this->get_value( $post->ID, $this->option_name_systems );
			$img_id        = $this->get_value( $post->ID, $this->option_name_systems_img );
			$section_image = wp_get_attachment_image_src( $img_id, 'full' );
			$heading       = $this->get_section_value( 'heading_systems' );

			$errors = $this->validate_news_items( $items, $section_image, $heading );

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( 'Our systems', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $img_id;
			$template        .= str_replace( '{{section_title}}', $heading, $template_heading );
			$img_temp         = $template_img;
			$img_temp         = str_replace(
				'{{img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$img_temp
			);
			$img_temp         = str_replace( '{{height}}', $this->image_height( $section_image ), $img_temp );
			$template        .= $img_temp;
			$template        .= $this->html_links( $items, $template_item, $template_spacer_2 );
			$template        .= $template_spacer_1;
		}

		if ( 1 === intval( $value_events_active ) ) {
			$items         = $this->get_value( $post->ID, $this->option_name_events );
			$img_id        = $this->get_value( $post->ID, $this->option_name_events_img );
			$section_image = wp_get_attachment_image_src( $img_id, 'full' );
			$heading       = $this->get_section_value( 'heading_events' );

			$errors = $this->validate_news_items( $items, $section_image, $heading );

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( 'Events', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $img_id;
			$template        .= str_replace( '{{section_title}}', $heading, $template_heading );
			$img_temp         = $template_img;
			$img_temp         = str_replace(
				'{{img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$img_temp
			);
			$img_temp         = str_replace( '{{height}}', $this->image_height( $section_image ), $img_temp );
			$template        .= $img_temp;
			$template        .= $this->html_links( $items, $template_item, $template_spacer_2 );
			$template        .= $template_spacer_1;
		}

		if ( 1 === intval( $value_navoica_active ) ) {
			$items         = $this->get_value( $post->ID, $this->option_name_navoica );
			$img_id        = $this->get_value( $post->ID, $this->option_name_navoica_img );
			$section_image = wp_get_attachment_image_src( $img_id, 'full' );
			$heading       = $this->get_section_value( 'heading_navoica' );

			$errors = $this->validate_news_items( $items, $section_image, $heading );

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( 'Navoica', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $img_id;
			$template        .= str_replace( '{{section_title}}', $heading, $template_heading );
			$img_temp         = $template_img;
			$img_temp         = str_replace(
				'{{img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$img_temp
			);
			$img_temp         = str_replace( '{{height}}', $this->image_height( $section_image ), $img_temp );
			$template        .= $img_temp;
			$template        .= $this->html_links( $items, $template_item, $template_spacer_2 );
			$template        .= $template_spacer_1;
		}

		if ( 1 === intval( $value_eu_projects_active ) ) {
			$items         = $this->get_value( $post->ID, $this->option_name_eu_projects );
			$img_id        = $this->get_value( $post->ID, $this->option_name_eu_projects_img );
			$section_image = wp_get_attachment_image_src( $img_id, 'full' );
			$heading       = $this->get_section_value( 'heading_eu_projects' );

			$errors = $this->validate_news_items( $items, $section_image, $heading );

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( 'EU projects', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $img_id;
			$template        .= str_replace( '{{section_title}}', $heading, $template_heading );
			$img_temp         = $template_img;
			$img_temp         = str_replace(
				'{{img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$img_temp
			);
			$img_temp         = str_replace( '{{height}}', $this->image_height( $section_image ), $img_temp );
			$template        .= $img_temp;
			$template        .= $this->html_links( $items, $template_item, $template_spacer_2 );
			$template        .= $template_spacer_1;
		}

		if ( 1 === intval( $value_questions_active ) ) {
			$errors                 = array();
			$value_questions_img_id = $this->get_value( $post->ID, $this->option_name_questions_img );
			$section_image          = wp_get_attachment_image_src( $value_questions_img_id, 'full' );
			$name                   = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_first_name_last_name
				)
			);
			$job_position           = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_job_position
				)
			);
			$question_1             = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_question_1
				)
			);
			$question_2             = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_question_2
				)
			);
			$question_3             = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_question_3
				)
			);
			$answer_1               = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_answer_1
				)
			);
			$answer_2               = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_answer_2
				)
			);
			$answer_3               = $this->orphans(
				$this->get_value(
					$post->ID,
					$this->option_name_answer_3
				)
			);

			$heading = $this->get_section_value( 'heading_questions' );

			if ( ! $heading ) {
				$errors[] = 'heading';
			}

			if ( ! $name ) {
				$errors[] = 'person_name';
			}

			if ( ! $job_position ) {
				$errors[] = 'job_position';
			}

			if ( ! $question_1 ) {
				$errors[] = 'question';
			}

			if ( ! $answer_2 ) {
				$errors[] = 'answer';
			}

			if ( ! $question_2 ) {
				$errors[] = 'question';
			}

			if ( ! $answer_2 ) {
				$errors[] = 'answer';
			}

			if ( ! $question_3 ) {
				$errors[] = 'question';
			}

			if ( ! $answer_3 ) {
				$errors[] = 'answer';
			}

			if ( ! $section_image ) {
				$errors[] = 'image';
			}

			if ( $errors ) {
				wp_send_json_error(
					array(
						'errors'  => $errors,
						'section' => _x( '3 questions to', 'newsletter', 'THEME_SLUG' ),
					)
				);
			}

			$section_images[] = $value_questions_img_id;
			$template        .= $template_heading;
			$img_temp         = $template_img;
			$img_temp         = str_replace(
				'{{img_url}}',
				sprintf( '%s/%s', $images_directory_name, $this->get_file_name( $section_image[0] ) ),
				$img_temp
			);
			$img_temp         = str_replace(
				'{{height}}',
				$this->image_height( $section_image ),
				$img_temp
			);
			$template        .= $img_temp;
			$template        .= file_get_contents( get_stylesheet_directory() . '/assets/newsletter/template_questions.html' );

			$placeholders['{{first_name_last_name}}'] = $name;
			$placeholders['{{job_position}}']         = $job_position;
			$placeholders['{{question_1}}']           = $question_1;
			$placeholders['{{question_2}}']           = $question_2;
			$placeholders['{{question_3}}']           = $question_3;
			$placeholders['{{answer_1}}']             = $answer_1;
			$placeholders['{{answer_2}}']             = $answer_2;
			$placeholders['{{answer_3}}']             = $answer_3;

			$template  = str_replace( '{{section_title}}', $heading, $template );
			$template .= $template_spacer_1;
		}

		$template .= $template_end;

		$wp_filesystem->delete( $newsletter_opi_directory_basedir, true );
		if ( ! file_exists( $newsletter_opi_directory_basedir ) ) {
			wp_mkdir_p( $newsletter_opi_directory_basedir );
		}

		$new_file_path = sprintf(
			'%s/%s.html',
			$newsletter_opi_directory_basedir,
			$file_name
		);

		$file_url = sprintf(
			'%s/%s/%s/%s',
			$upload_dir['baseurl'],
			$this->newsletter_opi_base_dir,
			$file_name,
			$file_name_zip
		);

		$file = fopen( $new_file_path, 'w' ) or die( 'Unable to open file!' );
		foreach ( $placeholders as $key => $value ) {
			$template = str_replace( $key, $value, $template );
		}
		fwrite( $file, $template );
		fclose( $file );

		$this->recurse_copy(
			get_stylesheet_directory() . '/assets/newsletter/images',
			$newsletter_opi_directory_basedir,
			$images_directory_name
		);

		if ( file_exists( get_stylesheet_directory() . '/assets/newsletter/images' ) ) {
			if ( $section_images ) {
				foreach ( $section_images as $img_id ) {
					$image_server_path = wp_get_original_image_path( $img_id );
					$file_info         = $this->get_file_info( $image_server_path );
					copy(
						$image_server_path,
						$newsletter_opi_directory_basedir . '/' . $images_directory_name . '/' . $file_info['basename']
					);
				}
			}
		}

		if ( ! extension_loaded( 'zip' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html_x(
						'Zip module is not installed.',
						'newsletter',
						'THEME_SLUG'
					),
				)
			);
		}

		$path_to_zip = $newsletter_opi_directory_basedir . '/' . $file_name_zip;

		if ( file_exists( $path_to_zip ) ) {
			wp_delete_file( $path_to_zip );
		}

		$zip = new ZipArchive;
		if ( $zip->open( $path_to_zip, ZipArchive::CREATE ) === true ) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $newsletter_opi_directory_basedir ),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			if ( $files ) {
				foreach ( $files as $name => $file ) {
					if ( ! $file->isDir() ) {
						$filePath     = $file->getRealPath();
						$relativePath = substr( $filePath, strlen( $newsletter_opi_directory_basedir ) + 1 );
						$zip->addFile( $filePath, $relativePath );
					}
				}
			}
			$zip->close();
		}

		wp_send_json_success(
			array(
				'url' => $file_url,
			)
		);
	}

	function recurse_copy(
		string $source_directory,
		string $destination_directory,
		string $child_folder = ''
	): void {
		$directory = opendir( $source_directory );

		if ( is_dir( $destination_directory ) === false ) {
			mkdir( $destination_directory );
		}

		if ( $child_folder !== '' ) {
			if ( is_dir( "$destination_directory/$child_folder" ) === false ) {
				mkdir( "$destination_directory/$child_folder" );
			}

			while ( ( $file = readdir( $directory ) ) !== false ) {
				if ( $file === '.' || $file === '..' ) {
					continue;
				}

				if ( is_dir( "$source_directory/$file" ) === true ) {
					recurse_copy( "$source_directory/$file", "$destination_directory/$child_folder/$file" );
				} else {
					copy( "$source_directory/$file", "$destination_directory/$child_folder/$file" );
				}
			}

			closedir( $directory );

			return;
		}

		while ( ( $file = readdir( $directory ) ) !== false ) {
			if ( $file === '.' || $file === '..' ) {
				continue;
			}

			if ( is_dir( "$source_directory/$file" ) === true ) {
				recurse_copy( "$source_directory/$file", "$destination_directory/$file" );
			} else {
				copy( "$source_directory/$file", "$destination_directory/$file" );
			}
		}

		closedir( $directory );
	}

	private function get_value( $post_id, $meta_key ) {
		$value = get_post_meta( $post_id, $meta_key, true );
		if ( empty( $value ) ) {
			return '';
		}

		return $value;
	}

	public function set_data() {
		if ( ! function_exists( 'get_privacy_policy_url' ) ) {
			return;
		}
		$this->data = array(
			'email_error_empty'        => _x( 'Enter your email address.', 'newsletter', 'THEME_SLUG' ),
			'email_error_invalid'      => _x( 'The e-mail address is invalid.', 'newsletter', 'THEME_SLUG' ),
			'statute_error_required'   => _x(
				'Mark the consent to activate the subscription in accordance with the regulations.',
				'newsletter',
				'THEME_SLUG'
			),
			'acymailing_callout_close' => _x(
				'Close the window with the subscription to the newsletter',
				'newsletter',
				'THEME_SLUG'
			),
		);
	}

	public function add_newsletter_data( $data ) {
		$data['newsletter'] = $this->data;

		return $data;
	}

	private function html_section_image( $section, $option, $val ) {
		?>
		<div class="description-wide" style="margin: 5px 0;">
			<label for="image-for-
			<?php
			printf( '%s', esc_attr( $section ) );
			?>
			">
				<span class="description" style="display:block;">
				<?php
					printf( '%s', esc_html_x( 'Picture', 'newsletter', 'THEME_SLUG' ) );
				?>
				</span>

				<input type="hidden" class="section-image" name="
				<?php
				printf( '%s', $option );
				?>
				" value="
				<?php
				printf( '%s', esc_attr( $val ) );
				?>
				"/>

				<button type="button" class="button button-secondary add_image"
						id="image-for-
						<?php
						printf( '%s', esc_attr( $section ) );
						?>
						">
						<?php
						printf( '%s', esc_html_x( 'Add picture', 'newsletter', 'THEME_SLUG' ) );
						?>
						</button>
				<button type="button" class="button button-secondary remove_image"
						id="
						<?php
						printf( 'remove-image-for-%s', esc_attr( $section ) );
						?>
						">
						<?php
						printf( '%s', esc_html_x( 'Remove picture', 'newsletter', 'THEME_SLUG' ) );
						?>
						</button>

				<?php
				if ( ! empty( $val ) ) :
					?>
					<?php
					echo wp_get_attachment_image( $val, 'thumbnail' );
					?>
					<?php
				else :
					?>
					<img src="" alt="">
					<?php
				endif;
				?>
			</label>
		</div>
		<?php
	}

	/**
	 * Count new image height
	 *
	 * @param $image
	 *
	 * @return float
	 */
	private function image_height( $image ): float {
		$height = $image[2] * 100 / $image[1];
		$height = $height / 100;

		return round( $height * 760 );
	}

	private function html_section_active( $option, $val ) {
		printf(
			'<label style="display: block;">%s <input type="checkbox" name="%s" value="1" %s /></label>',
			esc_html_x( 'Add a section to your newsletter', 'newsletter', 'THEME_SLUG' ),
			esc_attr( $option ),
			checked( intval( $val ), 1, false )
		);
	}

	private function html_section_items( $section, $option, $val, $another_item_text, $remove_item_text ) {
		$remove_button         = '<button class="%s">%s</button>';
		$main_div_style        = 'margin-bottom: 40px;';
		$remove_button_classes = array( 'button', 'remove-newsletter-item' );
		$item_classes          = array( 'item' );
		$title_text            = esc_html_x( 'Title', 'newsletter news', 'THEME_SLUG' );
		$content_text          = esc_html_x( 'Content', 'newsletter news', 'THEME_SLUG' );
		$link_text             = esc_html_x(
			'Link - at the beginning, enter https:// eg. https://opi.org.pl',
			'newsletter news',
			'THEME_SLUG'
		);
		$title_field           = '<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>';
		$content_field         = '<label>%s <textarea name="%s" rows="4" cols="50" style="width: 100%%;">%s</textarea></label>';
		$url_field             = '<label>%s <input type="text" name="%s" value="%s" style="width: 100%%;"></label>';

		if ( $val ) {
			foreach ( $val as $key => $item ) {
				printf(
					'<div id="%s-%s" class="%s" style="%s">',
					esc_attr( $section ),
					esc_attr( $key ),
					esc_attr( implode( ' ', $item_classes ) ),
					esc_attr( $main_div_style )
				);
				printf(
					$title_field,
					$title_text,
					sprintf( '%s[%s][%s]', esc_attr( $option ), esc_attr( $key ), esc_attr( 'title' ) ),
					isset( $item['title'] ) ? esc_html( $item['title'] ) : ''
				);
				printf(
					$content_field,
					$content_text,
					sprintf( '%s[%s][%s]', esc_attr( $option ), esc_attr( $key ), esc_attr( 'content' ) ),
					isset( $item['content'] ) ? esc_html( $item['content'] ) : ''
				);
				printf(
					$url_field,
					$link_text,
					sprintf( '%s[%s][%s]', esc_attr( $option ), esc_attr( $key ), esc_attr( 'url' ) ),
					isset( $item['url'] ) ? esc_url( $item['url'] ) : ''
				);
				printf(
					$remove_button,
					esc_attr( implode( ' ', $remove_button_classes ) ),
					$remove_item_text
				);
				echo '</div>';
			}
		} else {
			printf(
				'<div id="%s-first" class="%s" style="%s">',
				esc_attr( $section ),
				esc_attr( implode( ' ', $item_classes ) ),
				esc_attr( $main_div_style )
			);
			printf(
				$title_field,
				$title_text,
				sprintf( '%s[%s][%s]', esc_attr( $option ), esc_attr( 'first' ), esc_attr( 'title' ) ),
				''
			);
			printf(
				$content_field,
				$content_text,
				sprintf( '%s[%s][%s]', esc_attr( $option ), esc_attr( 'first' ), esc_attr( 'content' ) ),
				''
			);
			printf(
				$url_field,
				$link_text,
				sprintf( '%s[%s][%s]', esc_attr( $option ), esc_attr( 'first' ), esc_attr( 'url' ) ),
				''
			);
			printf(
				$remove_button,
				esc_attr( implode( ' ', $remove_button_classes ) ),
				$remove_item_text
			);
			echo '</div>';
		}
		printf(
			'<button id="another-%s" type="button" class="button add-another-item">%s</button>',
			esc_attr( $section ),
			$another_item_text,
		);
	}

	private function sanitized_items_data( $data ): array {
		$sanitized_data = array();
		if ( $data ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = array();
				$temp2        = array();
				foreach ( $value as $key_val => $item_val ) {
					switch ( $key_val ) {
						case 'content':
						case 'title':
							$temp2[ $key_val ] = wp_strip_all_tags( $item_val );
							break;
						case 'url':
							$item_val          = trim( $item_val );
							$temp2[ $key_val ] = filter_var(
								$item_val,
								FILTER_VALIDATE_URL
							) === false ? '' : sanitize_url( $item_val );
							break;
					}
				}
				$sanitized_data[ $key ] = $temp2;
			}
		}

		return $sanitized_data;
	}

	private function html_links( $items, $template_item, $template_spacer_2 ) {
		if ( ! $items ) {
			return;
		}
		$value_news_count = count( $items );
		$i                = 0;
		$links_html       = '';
		foreach ( $items as $key => $item ) {
			$temp        = $template_item;
			$temp        = $this->orphans( str_replace( '{{title}}', esc_html( $item['title'] ), $temp ) );
			$temp        = $this->orphans( str_replace( '{{content}}', esc_html( $item['content'] ), $temp ) );
			$temp        = str_replace( '{{url}}', esc_url( $item['url'] ), $temp );
			$links_html .= $temp;
			if ( ++ $i !== $value_news_count ) {
				$links_html .= $template_spacer_2;
			}
		}

		return $links_html;
	}

	private function get_file_info( $path ): array {
		if ( ! $path ) {
			return array();
		}

		return pathinfo( $path );
	}

	private function get_file_name( $path ): string {
		$file_info = $this->get_file_info( $path );
		if ( ! $file_info ) {
			return '';
		}

		return $file_info['basename'];
	}

	private function orphans( $string ): string {
		return preg_replace( '/\s(\S)\s+/', ' $1&nbsp;', $string );
	}

	/**
	 * custom option and settings
	 */
	public function settings_init() {
		register_setting(
			$this->option_group_newsletter,
			$this->section_name,
			array( $this, 'newsletter_options_sanitize' )
		);

		add_settings_section(
			'newsletter_settings',
			_x( 'Heading settings', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'section_headings_cb' ),
			'newsletter-settings'
		);

		add_settings_field(
			'field_preheader',
			_x( 'Preheader', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_preheader_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_preheader',
			)
		);

		add_settings_field(
			'field_subtitle',
			_x( 'Subtitle', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_subtitle_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_newsletter_subtitle',
			)
		);

		add_settings_field(
			'field_heading_news',
			_x( 'News', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_heading_news_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_heading_news',
			)
		);

		add_settings_field(
			'field_heading_systems',
			_x( 'Systems', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_heading_systems_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_heading_systems',
			)
		);

		add_settings_field(
			'field_heading_events',
			_x( 'Events', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_heading_events_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_heading_events',
			)
		);

		add_settings_field(
			'field_heading_navoica',
			_x( 'Navoica', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_heading_navoica_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_heading_navoica',
			)
		);

		add_settings_field(
			'field_heading_eu_projects',
			_x( 'EU projects', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_heading_eu_projects_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_heading_eu_projects',
			)
		);

		add_settings_field(
			'field_heading_questions',
			_x( 'Questions', 'newsletter option page', 'THEME_SLUG' ),
			array( $this, 'field_newsletter_heading_questions_form_cb' ),
			'newsletter-settings',
			'newsletter_settings',
			array(
				'label_for' => 'field_heading_questions',
			)
		);
	}

	public function section_headings_cb( $args ) {
		?>
		<p id="
		<?php
		echo esc_attr( $args['id'] );
		?>
		">
		<?php
			esc_html_e( 'Enter the headlines', 'THEME_SLUG' );
		?>
		</p>
		<?php
	}

	public function field_newsletter_preheader_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'preheader',
			esc_attr( $this->get_section_value( 'preheader' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter subtitle of newsletter.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_subtitle_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'subtitle',
			esc_attr( $this->get_section_value( 'subtitle' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter subtitle of newsletter.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_heading_news_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'heading_news',
			esc_attr( $this->get_section_value( 'heading_news' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter heading of news section.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_heading_systems_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'heading_systems',
			esc_attr( $this->get_section_value( 'heading_systems' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter heading of systems section.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_heading_events_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'heading_events',
			esc_attr( $this->get_section_value( 'heading_events' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter heading of events section.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_heading_navoica_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'heading_navoica',
			esc_attr( $this->get_section_value( 'heading_navoica' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter heading of navoica section.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_heading_eu_projects_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'heading_eu_projects',
			esc_attr( $this->get_section_value( 'heading_eu_projects' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter heading of EU projects section.', 'THEME_SLUG' )
		);
	}

	public function field_newsletter_heading_questions_form_cb( $args ) {
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" id="%s" style="width: 100%%;" />',
			$this->section_name,
			'heading_questions',
			esc_attr( $this->get_section_value( 'heading_questions' ) ),
			$args['label_for']
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Enter heading of questions section.', 'THEME_SLUG' )
		);
	}

	public function options_page() {
		add_submenu_page(
			'options-general.php',
			_x( 'Newsletter settings', 'newsletter', 'THEME_SLUG' ),
			_x( 'Newsletter settings', 'newsletter', 'THEME_SLUG' ),
			apply_filters( 'newsletters_settings_page_capability', $this->newsletter_settings_page_capability ),
			'newsletter-settings',
			array( $this, 'options_page_html' )
		);
	}

	/**
	 * Add submenu in admin callback
	 *
	 * @return void
	 */
	public function options_page_html(): void {
		if ( ! current_user_can(
			apply_filters(
				'newsletters_settings_page_capability',
				$this->newsletter_settings_page_capability
			)
		) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>
			<?php
				echo esc_html( get_admin_page_title() );
			?>
			</h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wporg"
				settings_fields( $this->option_group_newsletter );
				// output setting sections and their fields
				// (sections are registered for "wporg", each field is registered to a specific section)
				do_settings_sections( 'newsletter-settings' );
				// output save settings button
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	private function get_section_value( $name = null ) {
		$value = get_option( $this->section_name );
		if ( null === $name ) {
			return wp_parse_args(
				$value,
				$this->defaults
			);
		}
		if ( isset( $value[ $name ] ) ) {
			return $value[ $name ];
		}

		return null;
	}

	public function newsletter_options_sanitize( $input ): array {
		$new_input = array();
		if ( isset( $input['preheader'] ) ) {
			$new_input['preheader'] = sanitize_text_field( $input['preheader'] );
		}

		if ( isset( $input['subtitle'] ) ) {
			$new_input['subtitle'] = sanitize_text_field( $input['subtitle'] );
		}

		if ( isset( $input['heading_news'] ) ) {
			$new_input['heading_news'] = sanitize_text_field( $input['heading_news'] );
		}

		if ( isset( $input['heading_systems'] ) ) {
			$new_input['heading_systems'] = sanitize_text_field( $input['heading_systems'] );
		}

		if ( isset( $input['heading_events'] ) ) {
			$new_input['heading_events'] = sanitize_text_field( $input['heading_events'] );
		}

		if ( isset( $input['heading_navoica'] ) ) {
			$new_input['heading_navoica'] = sanitize_text_field( $input['heading_navoica'] );
		}

		if ( isset( $input['heading_eu_projects'] ) ) {
			$new_input['heading_eu_projects'] = sanitize_text_field( $input['heading_eu_projects'] );
		}

		if ( isset( $input['heading_questions'] ) ) {
			$new_input['heading_questions'] = sanitize_text_field( $input['heading_questions'] );
		}

		return $new_input;
	}

	public function editor_permissions( $capability ): string {
		return 'edit_pages';
	}

	private function sanitize_nonce( $key ): string {
		$nonce = $_REQUEST[ $key ] ?? '';
		$nonce = wp_strip_all_tags( $nonce );

		return htmlspecialchars( $nonce, ENT_QUOTES );
	}

	private function validate_news_items( $items, $image, $heading ): array {
		$errors = array();
		if ( $items ) {
			foreach ( $items as $item ) {
				if ( ! $item['title'] ) {
					$errors[] = 'title';
				}
				if ( ! $item['content'] ) {
					$errors[] = 'content';
				}
				if ( ! $item['url'] ) {
					$errors[] = 'url';
				}
			}
		}

		if ( ! $image ) {
			$errors[] = 'image';
		}

		if ( ! $heading ) {
			$errors[] = 'heading';
		}

		return $errors;
	}
}

