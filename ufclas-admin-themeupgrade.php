<?php 
/**
 * Get theme and site data for the theme upgrade page
 * @since 0.5.0
 */
function ufclas_admin_themeupgrade_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-themeupgrade', 'themeupgrade_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();
    
	// Get existing copy of transient data if exists 
	// @todo option to clear transient
	//delete_site_transient('ufclas_admin_themeupgrade');
	if( false === ( $data = get_site_transient('ufclas_admin_themeupgrade') ) ){
				
		foreach($sites as $site){	
			switch_to_blog( $site['id'] );
            
            // Get theme information
            $theme = wp_get_theme();
            $theme_name = $theme->get_stylesheet();
            $upgradeable_themes = array('ufclaspeople');
			 
            // Only list sites with the matched theme
            if( in_array($theme_name, $upgradeable_themes) ){
				$plugins = ufclas_admin_themeupgrade_plugins( get_option('active_plugins', array()) );
				$widgets = ufclas_admin_list_widgets( get_option('sidebars_widgets') );
				
				$data[] = array(
					$site['id'],
					$site['path'],
					$theme_name,
                    $site['status'],
					$widgets,
					$plugins,
				);
            }
			restore_current_blog();
        }
		set_site_transient( 'ufclas_admin_themeupgrade', $data, 2 * HOUR_IN_SECONDS );
	}
	// Need to encode data to pass an array to JavaScript, 
	// Must not be contain an associative array because Datatables doesn't support objects
	echo json_encode($data);
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_ufca_themeupgrade', 'ufclas_admin_themeupgrade_table' );

/**
 * Display page content for the Theme upgrade page
 * @since 0.5.0
 */
function ufclas_admin_themeupgrade_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Theme Upgrade Information', 'ufclas-admin' ); ?></h2>
		<p>Note: This information only updates every 2 hours.</p>
    	<table id="themeupgrade" class="display ufca-datatable" width="100%">
        	<thead>
            	<tr>
                    <th class="id">ID</th>
                    <th class="path">Path</th>
                    <th class="theme">Theme</th>
                    <th class="status">Status</th>
                    <th class="widgets">Widgets</th>
                    <th class="plugins">Plugins</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    	
    </div><!-- .wrap -->
    <?php 
}
/**
 * Format sidebar widgets list
 * @since 0.5.0
 */
function ufclas_admin_list_widgets( $widgets ){
    $list = '';
    foreach ( $widgets as $sidebar => $widgets ){
        if( !empty($widgets) && is_array($widgets) ){
            $list .= '<div><strong>' . $sidebar . '</strong>: ' . join(', ', $widgets) . '</div>';
        }
    }
   return $list;
}

/**
 * Format plugin list for a site, excluding any that are network activated
 * @since 0.5.0
 */
function ufclas_admin_themeupgrade_plugins( $plugins ){
	$count = count($plugins);
	for($i=0; $i<$count; $i++){
		if( is_plugin_active_for_network($plugins[$i]) ){
			unset($plugins[$i]);
		}
	}
	return ufclas_admin_list_plugins( $plugins );	
}