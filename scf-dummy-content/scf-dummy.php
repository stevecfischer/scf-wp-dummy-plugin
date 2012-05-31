<?php
/**
Plugin Name: SCF Dummy Content
Description: Quickly populate your site with dummy content
Version: 1.8
Author: SmartyDog
Author URI: http://www.smartydogdesigns.com/scf-dummy-plugin/
License: GPLv2 or later
*/

/*!
 * @TODO for version 1.8
 * 		1. testing testing and more testing
 * 		2. clean up code
 *   		a. delete any functions not being used
 *   		b. look for places to optimize
 *		3. give terms their own number input.
 *		4. Documentation!!!!
 *		5. need a better solution for titling posts. its too complicated now.
 *			a. maybe several fields in a row like:
 *				i. input -- dropdown(shortcode options Ex. %%cpt%%) -- input -- counter(required)
 *
 *
 * \author Steve (3/20/2012)
 */

if (is_admin()) { 
	 
	if (!defined('SCF_DUMMY_PATH')) {
		define('SCF_DUMMY_PATH', dirname(__FILE__).'/');
	}

	// Load admin functionality	
	require_once SCF_DUMMY_PATH.'scf-dummy-class.php';

	// Set-up Action and Filter Hooks
	register_activation_hook(__FILE__, 'scfdc_add_defaults');
	register_deactivation_hook(__FILE__, 'scfdc_delete_plugin_options');
	register_uninstall_hook(__FILE__, 'scfdc_delete_plugin_options');
	add_action('admin_init', 'scfdc_init' );
	add_action('admin_menu', 'scfdc_add_options_page');
	add_filter( 'plugin_action_links', 'scfdc_plugin_action_links', 10, 2 );

	if (is_admin()) {
		// Load admin functionality	
		require_once SCF_DUMMY_PATH.'scf-dummy-options-page.php';
	}

	function scfdc_validate_options($input) {
	   if( empty($input['upload_image']) ) {
		  unset($input['upload_image']);
	   }
	   $input['content'] =  wp_filter_nohtml_kses($input['content']);
	   return $input;
	}

	// Display a Settings link on the main Plugins page
	function scfdc_plugin_action_links( $links, $file ) {
	   if ( $file == plugin_basename( __FILE__ ) ) {
		  $scfdc_links = '<a href="'.get_admin_url().'options-general.php?page=scf-dummy-content/scf-dummy-options-page.php">'.__('Settings').'</a>';
		  // make the 'Settings' link appear first
		  array_unshift( $links, $scfdc_links );
	   }
	   return $links;
	}

	$scf_dummy = new scf_dummy(); // call our class

	/*
	===============================
	===============================
	*/
	/*
	===============================
	===============================
	*/
	/*
	===============================
	===============================
	*/
	function image_upload_admin_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script('scf-upload', WP_PLUGIN_URL.'/scf-dummy-content/js/scf.jquery.plugin.js', array('jquery','media-upload','thickbox'));
		wp_enqueue_script('scf-upload');
	}

	function image_upload_admin_styles() {
		wp_enqueue_style('thickbox');
		wp_register_style( 'scf-stylesheet', WP_PLUGIN_URL.'/scf-dummy-content/css/style.css' );
		wp_enqueue_style( 'scf-stylesheet' );
	}

	if (isset($_GET['page']) && $_GET['page'] == 'scf-dummy-content/scf-dummy-options-page.php') {
		add_action('admin_print_scripts', 'image_upload_admin_scripts');
		add_action('admin_print_styles', 'image_upload_admin_styles');
	}
}//END if admin