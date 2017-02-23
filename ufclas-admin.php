<?php
/*
Plugin Name: UFCLAS Admin Tools
Plugin URI: https://it.clas.ufl.edu/
Description: Management/Reporting Tools for UF CLAS.
Version: 0.6.3
Author: Priscilla Chapman (CLAS IT)
Author URI: https://it.clas.ufl.edu/
Build Date: 20170223
*/

// Include admin page functions
require_once( dirname( __FILE__) . '/ufclas-admin-info.php' );
require_once( dirname( __FILE__) . '/ufclas-admin-siteusers.php' );
require_once( dirname( __FILE__) . '/ufclas-admin-siteforms.php' );
require_once( dirname( __FILE__) . '/ufclas-admin-themeupgrade.php' );

/**
 * Add CLAS Admin Tools menu items to the network dashboard
 * @since 0.5.0
 */
function ufclas_admin_register_menu(){
	add_menu_page('CLAS Admin Tools', 'CLAS Admin', 'manage_network_options', 'ufclas-admin-main', 'ufclas_admin_main_page');
	add_submenu_page('ufclas-admin-main','Site Info', 'Site Info', 'manage_network_options', 'ufclas-admin-info', 'ufclas_admin_info_page');
	add_submenu_page('ufclas-admin-main','Site Users', 'Site Users', 'manage_network_options', 'ufclas-admin-siteusers', 'ufclas_admin_siteusers_page');
	
	// Only show if Gravity Forms is network activated
	if( is_plugin_active_for_network('gravityforms/gravityforms.php') ){
		add_submenu_page('ufclas-admin-main','Site Forms', 'Site Forms', 'manage_network_options', 'ufclas-admin-siteforms', 'ufclas_admin_siteforms_page');
	}
    
    // Only show the theme upgrade page if theme is network activated
	$themes = array_keys( wp_get_themes(array('allowed' => 'network')) );
	if( in_array('ufclaspeople', $themes) ){
    	add_submenu_page('ufclas-admin-main','Theme Upgrade', 'Theme Upgrade', 'manage_network_options', 'ufclas-admin-themeupgrade', 'ufclas_admin_themeupgrade_page');
	}
}
add_action('network_admin_menu', 'ufclas_admin_register_menu');

/**
 * Add scripts and styles to plugin pages, set JavaScript variables
 * @since 0.5.0
 */
function ufclas_admin_scripts( $hook ) {
	// Admin pages to add scripts
	$pages = array(
		'toplevel_page_ufclas-admin-main',
		'clas-admin_page_ufclas-admin-info',
		'clas-admin_page_ufclas-admin-siteusers',
		'clas-admin_page_ufclas-admin-siteforms',
		'clas-admin_page_ufclas-admin-themeupgrade',
	);
	if ( in_array( $hook, $pages) ) {
        // Plugin scripts and files
		wp_enqueue_style( 'bootstrap', plugins_url( '/lib/bootstrap/css/bootstrap.min.css' , __FILE__ ), array(), NULL, 'all');
		wp_enqueue_script( 'bootstrap', plugins_url( '/lib/bootstrap/js/bootstrap.min.js' , __FILE__ ), array('jquery'), NULL, true);
		
		// Datatables and extensions - see https://www.datatables.net/download/
		wp_enqueue_style( 'datatables', plugins_url('/lib/jquery.datatables/dataTables.min.css', __FILE__ ), array('bootstrap'), NULL, 'screen');
    	wp_enqueue_script( 'datatables', plugins_url('/lib/jquery.datatables/dataTables.min.js', __FILE__ ), array('jquery','bootstrap'), NULL, true);
		
		// Plugin scripts and files
		wp_enqueue_style( 'ufclas-admin', plugins_url( '/css/ufclas-admin.css' , __FILE__ ), array('datatables'), NULL, 'screen');
		wp_enqueue_script( 'ufclas-admin', plugins_url( '/js/ufclas-admin.js' , __FILE__ ), array('datatables'), NULL, true);
		
		// Set Javascript variables according to hook name
		$page_keyword = explode('-', $hook);
		$page_keyword = array_pop( $page_keyword );
		wp_localize_script('ufclas-admin', 'ufca_data', array(
			'action' => "ufca_{$page_keyword}",
			'nonce_name' => "{$page_keyword}_nonce",
			'nonce_value' => wp_create_nonce( "ufca-get-{$page_keyword}"), 
			'plugin_url' => plugins_url( '' , __FILE__ )
		));
    }
	if ($hook == 'toplevel_page_ufclas-admin-main'){
		// D3.js chart library
		wp_enqueue_script( 'd3', plugins_url('/lib/d3/d3.min.js', __FILE__ ), array('jquery',), NULL, true);
	}
}
add_action( 'admin_enqueue_scripts', 'ufclas_admin_scripts' );

/**
 * Display CLAS Admin tools content
 * @since 0.5.0
 */
function ufclas_admin_main_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	?>
	<div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'CLAS Admin Tools', 'ufclas-admin' ); ?></h2>
        
        <p><?php _e( 'Total Sites by Status', 'ufclas-admin' ); ?></p>
        <div class="container">
        	<div id="chart"></div>
        </div>
        <table id="admin" class="display dataTable ufca-datatable table table-bordered table-hover" width="100%">
        	<thead>
            	<tr>
                	<th>Public</th>
                    <th>Public, Not Indexed</th>
                    <th>Private, GatorLink Users</th>
                    <th>Private, Network Admin Only</th>
                    <th>Private</th>
                    <th>Archived</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
	<?php
}


function ufclas_admin_main_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-main', 'main_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();

	// Get existing copy of transient data if exists 
	// @todo option to clear transient
	//delete_site_transient('ufclas_admin_siteforms');
	if( false === ( $data = get_site_transient('ufclas_admin') ) ){ 
		
		$status_count = array(
			'Public' => 0,
			'Public, Not Indexed' => 0,
			'Private, GatorLink Users' => 0,
			'Private, Network Admin Only' => 0,
			'Private' => 0,
			'Archived' => 0,
		);
		
		foreach($sites as $site){	
			$status_count[ $site['status'] ] += 1;
		}
		
		$data[] = array_values( $status_count );
		
		set_site_transient( 'ufclas_admin', $data, 12 * HOUR_IN_SECONDS );
	}
	// Need to encode data to pass an array to JavaScript, 
	// Must not be contain an associative array because Datatables doesn't support objects
	echo json_encode($data);
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_ufca_main', 'ufclas_admin_main_table' );


