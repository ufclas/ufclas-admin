<?php 


function ufclas_admin_site_info_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-info', 'info_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();
	
	// Get existing copy of transient data if exists 
	// TODO: 
	delete_site_transient('ufclas_admin_siteinfo_data');
	//delete_site_transient('ufclas_admin_siteinfo');
	if( false === ( $data = get_site_transient('ufclas_admin_siteinfo') ) ){
		
		foreach($sites as $site){	
			switch_to_blog( $site['id'] );
			$theme = get_option('stylesheet');
			$plugins = ufclas_admin_list_plugins( get_option('active_plugins', array()) );
			$data[] = array(
				$site['id'],
				$site['path'],
				$site['title'],
				$site['description'],
				$site['status'],
				$theme,
				$plugins
			);
			restore_current_blog();
		}
		set_site_transient( 'ufclas_admin_siteinfo', $data, 12 * HOUR_IN_SECONDS );
	}
	// Need to encode data to pass an array to JavaScript, 
	// Must not be contain an associative array because Datatables doesn't support objects
	echo json_encode($data);
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_ufca_info', 'ufclas_admin_site_info_table' );

function ufclas_admin_info_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Site Information', 'ufclas-admin' ); ?></h2>

    	<table id="info" class="display ufca-datatable" width="100%">
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

function ufclas_admin_get_sites(){
	global $wpdb;
	$data = array();
	
	//delete_site_transient('ufclas_admin_sites');
	if( false === ( $data = get_site_transient('ufclas_admin_sites') ) ){
		$status_names = array(
			'1' => 'Public',
			'0' => 'Public, Not Indexed',
			'-1' => 'Private, GatorLink Users',
			'-2' => 'Private',
			'-3' => 'Private, Network Admin Only',
		);
		
		$query = "SELECT * FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'";
		$results = $wpdb->get_results( $query, ARRAY_A );
		
		foreach($results as $site){	
			$id = $site['blog_id'];
			$inactive_status = '';
			if( 1 == $site['archived'] ){
				$inactive_status = 'Archived';
			}
			if( 1 == $site['deleted'] ){
				$inactive_status = 'Deactivated';
			}
			switch_to_blog( $id );
			$status = ( empty($inactive_status) )? $status_names[$site['public']]:$inactive_status;
			$title = sprintf( '<a href="%s" target="_blank">%s</a>', admin_url(), get_bloginfo('name') );
			$path = $site['path'];
			$data[$id] = array(
				'id' => $id, 
				'path' => $path,
				'title' => $title, 
				'description' => get_bloginfo('description'),
				'status' => $status
			);
			restore_current_blog();
		}
		
		set_site_transient( 'ufclas_admin_sites', $data, 12 * HOUR_IN_SECONDS );
	}
	return $data;
}
