<?php 
/**
 * Creates JSON data to display in Site Forms table, saves data as a transient
 * 
 * @todo Add option to clear transient
 * @todo handle form fields as the to email address
 * @since 0.6.3 
 */
function ufclas_admin_siteforms_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-siteforms', 'siteforms_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();
	
	// Get existing copy of transient data if exists
	if( WP_DEBUG || ( false === ($data = get_site_transient('ufclas_admin_siteforms')) ) ){
		$index = 0;
				
		foreach($sites as $site){	
			switch_to_blog( $site['id'] );
			
			// Get rid of cached forms list
			GFFormsModel::flush_current_forms();
			
			// Get a list of forms, including inactive ones
			$forms = GFAPI::get_forms();
			$sitepath = trim( $site['path'], '/');
			
			if(empty($forms)){
				$data[] = array(
					sprintf("%s (%s)", $site['title'], $sitepath),
					'-',
					__('No forms found', 'ufclas-admin'),
					__('No fields available', 'ufclas-admin'),
					'-',
					'-',
				);
				$index++;
			} else { // Site has forms
	
				foreach($forms as $form){
					error_log( print_r($form, true) );
					
					// Change the site dashboard link to the site forms link
					$site_title = str_replace('/wp-admin/', '/wp-admin/admin.php?page=gf_edit_forms', $site['title']);
					
					// Get the form title and preview link
					$link_title = __('Preview Form', 'ufclas-admin');
					$form_title = sprintf('<a href="%s?gf_page=preview&id=%d" title="%s" target="_blank">%s</a>', 
						$site['path'], 
						$form['id'],
						$link_title, 
						$form['title']
					);
					
					// Get form notification info
					$form_notifications = array();
					foreach($form['notifications'] as $key => $notification){
						$to = $notification['to'];
						if( '{admin_email}' == $to ){
							$to = get_option('admin_email', 'No admin email set');		
						}
						$form_notifications[] = sprintf('%1$s:<br><a href="mailto:%2$s">%2$s</a><br>', $notification['name'], $to);
					}
					
					// Get the list of the form fields and types
					$form_fields = '';
					foreach($form['fields'] as $field){
						$field_label = $field->label;
						$field_type = $field->type;
						
						// Show allowed extensions for file upload fields
						if ( $field_type == 'fileupload' ){
							$extensions = ( $field->allowedExtensions != '' )? $field->allowedExtensions : __('No extensions restricted', 'ufclas-admin');
							$field_type = sprintf('<strong class="text-danger">%s - %s</strong>', $field_type, $extensions);
						}
						// Show captcha fields
						elseif ( $field_type == 'captcha' ){
							$field_type = sprintf('<strong class="text-danger">%s</strong>', $field_type);
						}
						
						$form_fields .= sprintf('%s (%s)<br>', $field_label, $field_type);
					}
					
					$form_status = ( $form['is_active'] )? 'Active':'Inactive';
					
					$data[] = array(
						sprintf("%s<br>(%s)", $site_title, $sitepath),
						$form['id'],
						$form_title,
						$form_fields,
						implode(' ', $form_notifications),
						$form_status,
					);
					$index++;
				}
			}
			
			restore_current_blog();
		}
		set_site_transient( 'ufclas_admin_siteforms', $data, 12 * HOUR_IN_SECONDS );
	}
	/**
	 * Encodes data array to pass to DataTables. DataTables does not support objects or associative arrays
	 */
	echo json_encode($data);
	
	/**
	 * Required to terminate immediately and return a proper response
	 */
	wp_die(); 
}
add_action( 'wp_ajax_ufca_siteforms', 'ufclas_admin_siteforms_table' );

/**
 * Displays content for the Site Forms page
 * 
 * @since 0.6.3 
 */
function ufclas_admin_siteforms_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Site Forms', 'ufclas-admin' ); ?></h2>

    	<table id="siteforms" class="display dataTable ufca-datatable table table-bordered table-hover" width="100%">
        	<thead>
            	<tr>
                	<th class="sitetitle">Site Title</th>
                    <th class="formid">Form ID</th>
                    <th class="formtitle">Form Title</th>
                    <th class="formfields">Form Fields</th>
                    <th class="formnotifications">Notifications</th>
                    <th class="formstatus">Status</th>
                </tr>
        		
            </thead>
            <tbody></tbody>
        </table>
    	
    </div><!-- .wrap -->
    <?php 
}