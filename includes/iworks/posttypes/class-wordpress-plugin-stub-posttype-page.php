<?php
/**
 * Class for Post Type: PAGE
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'class-wordpress-plugin-stub-posttype.php';

class iworks_wordpress_plugin_stub_posttype_page extends iworks_wordpress_plugin_stub_posttype_base {

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
		add_action( 'add_meta_boxes_' . $this->posttypes_names[ $this->posttype_name ], array( $this, 'add_meta_boxes' ) );
		add_action( 'load-post-new.php', array( $this, 'admin_enqueue' ) );
		add_action( 'load-post.php', array( $this, 'admin_enqueue' ) );
		/**
		 * WordPress Plugin Stub Hooks
		 */
		/**
		 * settings
		 */
		$this->meta_boxes[ $this->posttypes_names[ $this->posttype_name ] ] = array(
			'page-data' => array(
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

	/**
	 * class settings
	 *
	 * @since 1.0.0
	 */
	public function action_init_settings() {}

	public function action_init_register_post_type() {}
	public function action_init_register_taxonomy() {}

}

