<?php
/*

Copyright 2017-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

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
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'iworks_wordpress_plugin_posttypes' ) ) {
	return;
}

class iworks_wordpress_plugin_posttypes {
	protected $post_type_name;
	protected $options;
	protected $fields;
	protected $posttype_objects = array();
	protected $base;

	public function __construct() {
		$this->options = iworks_wordpress_plugin_stub_get_options();
		/**
		 * save post
		 */
		add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 3 );
		$this->base = preg_replace( '/iworks.+/', '', __FILE__ );
		/**
		 * load post types
		 */
		$posttypes_classes_dir = $this->base . 'iworks/posttypes/';
		foreach ( glob( $posttypes_classes_dir . 'class*.php' ) as $filename_with_path ) {
			$filename = basename( $filename_with_path );
			if ( ! preg_match( '/^class-wordpress-plugin-stub-posttype-([a-z]+).php$/', $filename, $matches ) ) {
				continue;
			}
			$post_type_name = $matches[1];
			$filter         = sprintf(
				'wordpress-plugin-stub/load/posttype/%s',
				$post_type_name
			);
			if ( apply_filters( $filter, false ) ) {
				include_once $posttypes_classes_dir . $filename;
				$class_name                                = sprintf( 'iworks_wordpress_plugin_stub_posttype_%s', $post_type_name );
				$this->posttype_objects[ $post_type_name ] = new $class_name();
			}
		}
	}

	public function get_name() {
		return $this->post_type_name;
	}

	protected function get_meta_box_content( $post, $fields, $group ) {
		$content  = '';
		$basename = $this->options->get_option_name( $group );
		foreach ( $fields[ $group ] as $key => $data ) {
			$args = isset( $data['args'] ) ? $data['args'] : array();
			/**
			 * ID
			 */
			$args['id'] = $this->options->get_option_name( $group . '_' . $key );
			/**
			 * name
			 */
			$name = sprintf( '%s[%s]', $basename, $key );
			/**
			 * sanitize type
			 */
			$type = isset( $data['type'] ) ? $data['type'] : 'text';
			/**
			 * get value
			 */
			$value = get_post_meta( $post->ID, $args['id'], true );
			/**
			 * Handle select2
			 */
			if ( ! empty( $value ) && 'select2' == $type ) {
				$value = array(
					'value' => $value,
					'label' => get_the_title( $value ),
				);
			}
			/**
			 * Handle date
			 */
			if ( ! empty( $value ) && 'date' == $type ) {
				$value = date_i18n( 'Y-m-d', $value );
			}
			/**
			 * build
			 */
			$content .= sprintf( '<div class="iworks-5o5-row iworks-5o5-row-%s">', esc_attr( $key ) );
			if ( isset( $data['label'] ) && ! empty( $data['label'] ) ) {
				$content .= sprintf( '<label for=%s">%s</label>', esc_attr( $args['id'] ), esc_html( $data['label'] ) );
			}
			$content .= $this->options->get_field_by_type( $type, $name, $value, $args );
			if ( isset( $data['description'] ) ) {
				$content .= sprintf( '<p class="description">%s</p>', $data['description'] );
			}
			$content .= '</div>';
		}
		echo $content;
	}

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	public function save_post_meta_fields( $post_id, $post, $update, $fields ) {

		/*
		 * In production code, $slug should be set only once in the plugin,
		 * preferably as a class property, rather than in each function that needs it.
		 */
		$post_type = get_post_type( $post_id );

		// If this isn't a Copyricorrect post, don't update it.
		if ( $this->post_type_name != $post_type ) {
			return false;
		}
		foreach ( $fields as $group => $group_data ) {
			$post_key = $this->options->get_option_name( $group );
			if ( isset( $_POST[ $post_key ] ) ) {
				foreach ( $group_data as $key => $data ) {
					$value = isset( $_POST[ $post_key ][ $key ] ) ? $_POST[ $post_key ][ $key ] : null;
					if ( is_string( $value ) ) {
						$value = trim( $value );
					} elseif ( is_array( $value ) ) {
						if (
							isset( $value['integer'] ) && 0 == $value['integer']
							&& isset( $value['fractional'] ) && 0 == $value['fractional']
						) {
							$value = null;
						}
					}
					$option_name = $this->options->get_option_name( $group . '_' . $key );
					if ( empty( $value ) ) {
						delete_post_meta( $post->ID, $option_name );
					} else {
						if ( isset( $data['type'] ) && 'date' == $data['type'] ) {
							$value = strtotime( $value );
						}
						$result = add_post_meta( $post->ID, $option_name, $value, true );
						if ( ! $result ) {
							update_post_meta( $post->ID, $option_name, $value );
						}
						do_action( 'iworks_5o5_posttype_update_post_meta', $post->ID, $option_name, $value, $key, $data );
					}
				}
			}
		}
		return true;
	}

	/**
	 * Check post type
	 *
	 * @since 1.0.0
	 *
	 * @param integer $post_ID Post ID to check.
	 * @returns boolean is correct post type or not
	 */
	public function check_post_type_by_id( $post_ID ) {
		$post = get_post( $post_ID );
		if ( empty( $post ) ) {
			return false;
		}
		if ( $this->post_type_name == $post->post_type ) {
			return true;
		}
		return false;
	}

	/**
	 * Add default class to postbox,
	 */
	public function add_defult_class_to_postbox( $classes ) {
		$classes[] = 'iworks-type';
		return $classes;
	}
}


