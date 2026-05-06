<?php 


function ufclas_admin_site_info_table() {
	// Verify the request to prevent processing requests external of the blog. 
	check_ajax_referer( 'ufca-get-info', 'info_nonce' );
	
	global $wpdb;
	$data = array();
	$sites = ufclas_admin_get_sites();
	
	// Get existing copy of transient data if exists 
	if( WP_DEBUG || ( false === ($data = get_site_transient('ufclas_admin_siteinfo')) ) ){
		
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
				$site['registered'],
				$site['last_updated'],
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
    
    <div class="wrap container-fluid">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Site Information', 'ufclas-admin' ); ?></h2>
        
    	<table id="info" class="display dataTable ufca-datatable table table-bordered table-hover" width="100%">
        	<thead>
            	<tr>
                	<th class="id">ID</th>
                    <th class="path">Path</th>
                    <th class="title">Title</th>
                    <th class="desc">Description</th>
                    <th class="status">Status</th>
                    <th class="registered">Registered</th>
                    <th class="last-updated">Last Updated</th>
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
	
	if( WP_DEBUG || ( false === ($data = get_site_transient('ufclas_admin_sites')) ) ){
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
			$title = sprintf( '<a href="%s" target="_blank" title="%s">%s</a>', admin_url(), __('Site Dashboard', 'ufclas-admin'),  get_bloginfo('name') );
			$site_url = get_site_url( $id );

			$data[$id] = array(
				'id' => $id,
				'path' => $site_url,
				'title' => $title,
				'description' => get_bloginfo('description'),
				'status' => $status,
				'registered' => mysql2date('Y-m-d', $site['registered']),
				'last_updated' => mysql2date('Y-m-d', $site['last_updated']),
				'theme' => get_option('stylesheet')
			);
			restore_current_blog();
		}
		
		set_site_transient( 'ufclas_admin_sites', $data, 12 * HOUR_IN_SECONDS );
	}
	return $data;
}

/**
 * Classify each site into a Unit Group per the SharePoint Website Management
 * Database doc rules. Returns the input array with three new keys per site:
 *   - unit_group_type  (Mercury|Staged|Migrated|Unmigrated|Archived|Unclassified)
 *   - unit_group_title (canonical group title)
 *   - unit_group_id    ('g-<canonical blog_id>')
 *
 * @since 0.9.0
 * @param array $sites  Output of ufclas_admin_get_sites(), keyed by blog_id.
 * @return array Same array with classification fields added.
 */
function ufclas_admin_classify_sites( $sites ) {
	// Pre-process: extract clean comparable fields per site.
	$processed = array();
	foreach ( $sites as $id => $site ) {
		$processed[ $id ] = array(
			'clean_title' => strip_tags( $site['title'] ),
			'clean_url'   => rtrim( strtolower( $site['path'] ), '/' ),
			'status'      => $site['status'],
			'theme'       => isset( $site['theme'] ) ? strtolower( $site['theme'] ) : '',
		);
	}

	// Pass 1: identify Migrated pairs (one title with " OLD", a partner with the base).
	$migrated_pairs = array();
	foreach ( $processed as $id => $p ) {
		if ( substr( $p['clean_title'], -4 ) === ' OLD' ) {
			$base = substr( $p['clean_title'], 0, -4 );
			foreach ( $processed as $partner_id => $partner ) {
				if ( $partner_id !== $id && $partner['clean_title'] === $base ) {
					$migrated_pairs[ $base ] = array(
						'old_id'     => $id,
						'current_id' => $partner_id,
					);
					break;
				}
			}
		}
	}

	// Pass 2: count exact title occurrences.
	$title_counts = array();
	foreach ( $processed as $p ) {
		$t = $p['clean_title'];
		$title_counts[ $t ] = isset( $title_counts[ $t ] ) ? $title_counts[ $t ] + 1 : 1;
	}

	// Pass 3: classify each site (rules are mutually exclusive, first match wins).
	foreach ( $sites as $id => &$site ) {
		$p     = $processed[ $id ];
		$title = $p['clean_title'];
		$url   = $p['clean_url'];

		$type         = 'Unclassified';
		$group_title  = $title;
		$canonical_id = $id;

		// Rule 1a: this site IS the OLD half of a Migrated pair.
		if ( substr( $title, -4 ) === ' OLD' ) {
			$base = substr( $title, 0, -4 );
			if ( isset( $migrated_pairs[ $base ] ) ) {
				$type         = 'Migrated';
				$group_title  = $base;
				$canonical_id = $migrated_pairs[ $base ]['current_id'];
			}
		}
		// Rule 1b: this site IS the current half of a Migrated pair.
		elseif ( isset( $migrated_pairs[ $title ] ) ) {
			$type         = 'Migrated';
			$group_title  = $title;
			$canonical_id = $migrated_pairs[ $title ]['current_id']; // = $id
		}
		// Rule 2: Unmigrated — title appears exactly twice with .ufl.edu+UF-CLAS-DEPT and -mercury split.
		elseif ( isset( $title_counts[ $title ] ) && $title_counts[ $title ] === 2 ) {
			$partner_id = null;
			$partner    = null;
			foreach ( $processed as $other_id => $other ) {
				if ( $other_id !== $id && $other['clean_title'] === $title ) {
					$partner_id = $other_id;
					$partner    = $other;
					break;
				}
			}
			if ( $partner ) {
				$self_is_real      = ( substr( $url, -8 ) === '.ufl.edu' ) && ( $p['theme'] === 'uf-clas-dept' );
				$self_is_staged    = ( substr( $url, -8 ) === '-mercury' );
				$partner_is_real   = ( substr( $partner['clean_url'], -8 ) === '.ufl.edu' ) && ( $partner['theme'] === 'uf-clas-dept' );
				$partner_is_staged = ( substr( $partner['clean_url'], -8 ) === '-mercury' );

				if ( ( $self_is_real && $partner_is_staged ) || ( $self_is_staged && $partner_is_real ) ) {
					$type         = 'Unmigrated';
					$group_title  = $title;
					$canonical_id = $self_is_real ? $id : $partner_id;
				}
			}
		}
		// Rule 3: Archived — title appears once, status is Archived.
		elseif ( isset( $title_counts[ $title ] ) && $title_counts[ $title ] === 1 && $p['status'] === 'Archived' ) {
			$type = 'Archived';
		}
		// Rule 4: Mercury — title appears once, not archived, URL ends '.ufl.edu'.
		elseif ( isset( $title_counts[ $title ] ) && $title_counts[ $title ] === 1 && substr( $url, -8 ) === '.ufl.edu' ) {
			$type = 'Mercury';
		}
		// Rule 5: Staged — title appears once, not archived, URL ends '-mercury'.
		elseif ( isset( $title_counts[ $title ] ) && $title_counts[ $title ] === 1 && substr( $url, -8 ) === '-mercury' ) {
			$type = 'Staged';
		}
		// Else: stays Unclassified.

		$site['unit_group_type']  = $type;
		$site['unit_group_title'] = $group_title;
		$site['unit_group_id']    = 'g-' . $canonical_id;
	}
	unset( $site ); // break the foreach-by-ref reference.

	return $sites;
}
