<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function iworks_wordpress_plugin_stub_options() {
	$options = array();
	//$parent = SET SOME PAGE;
	/**
	 * main settings
	 */
	$options['index'] = array(
		'version'    => '0.0',
		'page_title' => __( 'Configuration', 'wordpress-plugin-stub' ),
		'menu'       => 'options',
		// 'parent' => $parent,
		'options'    => array(),
		'metaboxes'  => array(),
		'pages'      => array(),
	);
	return $options;
}

