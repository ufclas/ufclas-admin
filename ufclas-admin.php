<?php
/*
Plugin Name: UF CLAS - Admin Tools
Plugin URI: http://it.clas.ufl.edu/
Description: Management Tools for UF CLAS.
Version: 0.2.1
Author: Priscilla Chapman (CLAS IT)
Author URI: http://it.clas.ufl.edu/
License: GPL2
*/

// Include required classes
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( dirname( __FILE__) . '/class-reports-list-table.php' );

// Include admin page functions
require_once( dirname( __FILE__) . '/ufclas-admin-reports.php' );
require_once( dirname( __FILE__) . '/ufclas-admin-info.php' );
require_once( dirname( __FILE__) . '/ufclas-admin-archive.php' );

// Add Menu iteme to network admin dashboard
function ufclas_admin_register_menu(){
	add_menu_page('CLAS Admin Tools', 'CLAS Admin', 'manage_network_options', 'ufclas-admin', 'ufclas_admin_page');
	add_submenu_page('ufclas-admin','Site Info', 'Site Info', 'manage_network_options', 'ufclas-admin-info', 'ufclas_admin_info_page');
	//add_submenu_page('ufclas-admin','Reports', 'Reports', 'manage_network_options', 'ufclas-admin-reports', 'ufclas_admin_reports_page');
	//add_submenu_page('ufclas-admin','Bulk Archive Sites', 'Bulk Archive', 'manage_network_options', 'ufclas-admin-archive', 'ufclas_admin_archive_page');
}
add_action('network_admin_menu', 'ufclas_admin_register_menu');

function ufclas_admin_scripts( $hook ) {
	// Site info page
	if ( 'clas-admin_page_ufclas-admin-info' == $hook ) {
        // Datatables, TableTools scripts and styles
		wp_enqueue_style( 'datatables', '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css', array(), '1.10.7', 'screen');
    	wp_enqueue_script( 'datatables', '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js', array('jquery'), '1.10.7', true);
		wp_enqueue_style( 'tabletools', '//cdn.datatables.net/tabletools/2.2.4/css/dataTables.tableTools.css', array('datatables'), '2.2.4', 'screen');
    	wp_enqueue_script( 'tabletools', '//cdn.datatables.net/tabletools/2.2.4/js/dataTables.tableTools.min.js', array('jquery','datatables'), '2.2.4', true);
		
		// Plugin scripts and files
		wp_enqueue_style( 'ufclas-admin', plugins_url( '/css/ufclas-admin.css' , __FILE__ ), array('datatables'), '', 'screen');
		wp_enqueue_script( 'ufclas-admin', plugins_url( '/js/ufclas-admin.js' , __FILE__ ), array('datatables'), '', true);
		wp_localize_script('ufclas-admin', 'ufca_data', array(
			'site_info_nonce' => wp_create_nonce( "ufca-get-site-info"),
			'plugin_url' => plugins_url( '' , __FILE__ )
		));
    }
}
add_action( 'admin_enqueue_scripts', 'ufclas_admin_scripts' );

function ufclas_admin_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	?>
	<div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'CLAS Admin Tools', 'ufclas-admin' ); ?></h2>
    </div>
	<?php
}



