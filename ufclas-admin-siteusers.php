<?php 

function ufclas_admin_siteusers_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-siteusers', 'siteusers_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();
	
	// Get existing copy of transient data if exists 
	// TODO: 
	//delete_site_transient('ufclas_admin_siteusers');
	if( false === ( $data = get_site_transient('ufclas_admin_siteusers') ) ){
			
		foreach($sites as $site){	
			switch_to_blog( $site['id'] );
			$user_query = new WP_User_Query( array( 'blog_id' => $site['id'], 'fields' => array('ID') ) );
			$users = $user_query->get_results();

			$users_roles = array();
			foreach($users as $user){
				$user_data = get_userdata( $user->ID );
				$user_roles = $user_data->roles;
				
				$data[] = array(
					$user_data->ID,
					str_replace( '@ufl.edu', '', $user_data->user_login ),
					$user_data->first_name,
					$user_data->last_name,
					implode( ", ", $user_data->roles),
					$site['path'],
					$site['title'],
				);
			}
			
			restore_current_blog();
		}
		set_site_transient( 'ufclas_admin_siteusers', $data, 12 * HOUR_IN_SECONDS );
	}

	// Need to encode data to pass an array to JavaScript, 
	// Must not be contain an associative array because Datatables doesn't support objects
	echo json_encode($data);
	
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_ufca_siteusers', 'ufclas_admin_siteusers_table' );

function ufclas_admin_siteusers_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Site User Information', 'ufclas-admin' ); ?></h2>

    	<table id="siteusers" class="display ufca-datatable" width="100%">
        	<thead>
            	<tr>
                	<th class="userid">User ID</th>
                    <th class="username">Username</th>
                    <th class="firstname">First Name</th>
                    <th class="lastname">Last Name</th>
                    <th class="roles">Roles</th>
                    <th class="sitepath">Site Path</th>
                    <th class="sitetitle">Site Title</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    	
    </div><!-- .wrap -->
    <?php 
 
}
