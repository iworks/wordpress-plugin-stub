<?php
/**
 * WordPress Plugin Stub Options Configuration
 *
 * This file contains the configuration options for the WordPress Plugin Stub.
 * It defines the structure of the plugin's options and settings pages.
 *
 * @package    iWorks
 * @subpackage WordPress Plugin Stub
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2025-PLUGIN_TILL_YEAR Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 * @version    1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get plugin options configuration
 *
 * Returns an array containing the configuration for the plugin's options pages
 * and settings.
 *
 * @return array Array of options configuration
 * @since 1.0.0
 */
function iworks_wordpress_plugin_stub_options() {
	/**
	 * Initialize empty options array
	 */
	$options = array();

	/**
	 * Parent page placeholder (uncomment and set as needed)
	 */
	//$parent = SET SOME PAGE;

	/**
	 * Main settings configuration
	 *
	 * Defines the structure of the main options page including:
	 * - Version number
	 * - Page title
	 * - Menu type
	 * - Options array
	 * - Metaboxes array
	 * - Subpages array
	 */
	$options['index'] = array(
		/**
		 * Current version of the options configuration
		 */
		'version'    => '0.0',

		/**
		 * Title of the options page
		 */
		'page_title' => __( 'WordPress Plugin Stub', 'wordpress-plugin-stub' ),

		/**
		 * Menu type for the options page
		 */
		'menu'       => 'options',

		/**
		 * Parent page (commented out - uncomment and set as needed)
		 */
		// 'parent' => $parent,

		/**
		 * Array of options fields
		 */
		'options'    => apply_filters(
			'wordpress-plugin-stub/etc/config/options',
			array(
				array(
					'type'  => 'heading',
					'label' => __( 'Example Header', 'wordpress-plugin-stub' ),
					'since' => '1.0.0',
				),
				array(
					'name'              => 'example_text',
					'type'              => 'text',
					'th'                => __( 'Example Text', 'wordpress-plugin-stub' ),
					'description'       => __( 'Enter some text.', 'wordpress-plugin-stub' ),
					'classes'           => array( 'small-text' ),
					'sanitize_callback' => 'esc_html',
					'since'             => '1.0.0',
				),
				array(
					'name'              => 'example_textarea',
					'type'              => 'textarea',
					'th'                => __( 'Example Textarea', 'wordpress-plugin-stub' ),
					'description'       => __( 'Enter some text.', 'wordpress-plugin-stub' ),
					'classes'           => array( 'code', 'large-text' ),
					'sanitize_callback' => 'wp_kses_post',
					'since'             => '1.0.0',
					'rows'              => 5,
				),
			)
		),

		/**
		 * Array of metaboxes
		 */
		'metaboxes'  => apply_filters( 'wordpress-plugin-stub/etc/config/metaboxes', array() ),

		/**
		 * Array of subpages
		 */
		'pages'      => apply_filters( 'wordpress-plugin-stub/etc/config/pages', array() ),
	);

	/**
	 * Return the complete options configuration
	 */
	return $options;
}
