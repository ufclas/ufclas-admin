<?php 

function ufclas_admin_site_info_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-site-info', 'site_info_nonce' );
	
	global $wpdb;
	$data = array();
	
	// Get existing copy of transient data if exists 
	// TODO: 
	//delete_site_transient('ufclas_admin_siteinfo_data');
	if( false === ( $data = get_site_transient('ufclas_admin_siteinfo_data') ) ){
		$status_names = array(
			'1' => 'Public',
			'0' => 'Public, Not Indexed',
			'-1' => 'Private, GatorLink Users',
			'-2' => 'Private',
			'-3' => 'Private, Network Admin Only',
		);
		
		$query = "SELECT * FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND archived='0' AND deleted='0'";
		$results = $wpdb->get_results( $query, ARRAY_A );
		
		foreach($results as $active_site){	
			$id = $active_site['blog_id'];
			$inactive_status = '';
			if( 1 == $active_site['archived'] ){
				$inactive_status = 'Archived';
			}
			if( 1 == $active_site['deleted'] ){
				$inactive_status = 'Deactivated';
			}
			//if($id > 1){
				switch_to_blog( $id );
				$status = ( empty($inactive_status) )? $status_names[$active_site['public']]:$inactive_status;
				$title = sprintf( '<a href="%s" target="_blank">%s</a>', site_url(), get_bloginfo('name') );
				$path = trim($active_site['path'], '/');
				$theme = get_option('stylesheet');
				$plugins = ufclas_admin_list_plugins( get_option('active_plugins', array()) );
				$data[] = array(
					$id, 
					$path,
					$title, 
					get_bloginfo('description'),
					$status,
					$theme,
					$plugins
				);
				restore_current_blog();
			//}
		}
		set_site_transient( 'ufclas_admin_siteinfo_data', $data, 12 * HOUR_IN_SECONDS );
	}

	// Need to encode data to pass an array to JavaScript, 
	// Must not be contain an associative array because Datatables doesn't support objects
	echo json_encode($data);
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_ufca_site_info', 'ufclas_admin_site_info_table' );

function ufclas_admin_info_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Site Information', 'ufclas-admin' ); ?></h2>

    	<table id="info-table" class="display" width="100%">
        	<thead>
            	<tr>
                	<th class="id">ID</th>
                    <th class="path">Path</th>
                    <th class="title">Title</th>
                    <th class="desc">Description</th>
                    <th class="status">Status</th>
                    <th class="theme">Theme</th>
                    <th class="plugins">Plugins</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    	
    </div><!-- .wrap -->
    <?php 
 
}
function ufclas_admin_list_plugins($plugins){
   $list = array();
   foreach ( $plugins as $plugin_path ){
	   $plugin = explode('/', $plugin_path);
	   $list[] = '<span>' . $plugin[0] . '</span>, ';
   }
   
   return sprintf('<ul>%1$s</ul>', join($list));
}
