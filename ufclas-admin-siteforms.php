<?php 
function ufclas_admin_siteforms_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-siteforms', 'siteforms_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();
	
	// Get existing copy of transient data if exists 
	// @todo option to clear transient
	//delete_site_transient('ufclas_admin_siteforms');
	if( false === ( $data = get_site_transient('ufclas_admin_siteforms') ) ){
				
		foreach($sites as $site){	
			switch_to_blog( $site['id'] );
			
			// Get rid of cached forms list
			GFFormsModel::flush_current_forms();
			
			// Get a list of forms, including inactive ones
			$forms = GFAPI::get_forms();
			
			if(empty($forms)){
				$data[] = array(
					$site['path'],
					$site['title'],
					'-',
					'No forms found',
					'-',
					'-',
					'-'
				);
			} else { // Site has forms
	
			foreach($forms as $form){
				// Get form field labels
				$form_fields = array();
				foreach($form['fields'] as $field){
					$form_fields[] = $field->label;
				}
				
				// Get form notification info
				// @todo handle form fields as the to email address
				$form_notifications = array();
				foreach($form['notifications'] as $key => $notification){
					$to = $notification['to'];
					if( '{admin_email}' == $to ){
						$to = get_option('admin_email', 'No admin email set');		
					}
					$form_notifications[] = $notification['name'] . " (to: " . $to . ")";
				}
				$form_status = ( $form['is_active'] )? 'Active':'Inactive';
				
				$data[] = array(
					$site['path'],
					$site['title'],
					$form['id'],
					$form['title'],
					implode(', ', $form_fields ),
					implode(' ', $form_notifications),
					$form_status,
				);
				
			}
			}
			restore_current_blog();
		}
		set_site_transient( 'ufclas_admin_siteforms', $data, 12 * HOUR_IN_SECONDS );
	}
	// Need to encode data to pass an array to JavaScript, 
	// Must not be contain an associative array because Datatables doesn't support objects
	echo json_encode($data);
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_ufca_siteforms', 'ufclas_admin_siteforms_table' );

function ufclas_admin_siteforms_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Site Forms', 'ufclas-admin' ); ?></h2>

    	<table id="info" class="display dataTable ufca-datatable table table-striped table-bordered table-hover" width="100%">
        	<thead>
            	<tr>
                	<th class="sitepath">Site Path</th>
                    <th class="sitetitle">Site Title</th>
                    <th class="formid">Form ID</th>
                    <th class="formtitle">Form Title</th>
                    <th class="formfields">Field Labels</th>
                    <th class="formnotifications">Notifications</th>
                    <th class="formstatus">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    	
    </div><!-- .wrap -->
    <?php 
 
}