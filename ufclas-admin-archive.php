<?php 
function ufclas_admin_archive_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	
	?>
	<div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'CLAS Admin Tools', 'ufclas-admin' ); ?></h2>
        
    	<?php
		// Check if user posted anything
		if ( !empty( $_POST[ 'action' ] ) && check_admin_referer( 'ufclas-admin-tools-getusers', '_wpufclasnonce' ) ) {
			
			if ( !empty($_POST['usernames']) ){
				global $wpdb;
				
				// Get list of usernames to archive, removing any periods
				$usernames = str_replace( '.', '', $_POST['usernames'] );
				$archive_users = explode( PHP_EOL, trim($usernames) );
				$archive_users = array_unique( $archive_users );
				
				// Create an array of active sites from query
				$active_users = array();
				$active_sites = $wpdb->get_results("SELECT blog_id, path FROM $wpdb->blogs WHERE public > '-3' AND archived='0' AND deleted='0' ORDER BY path", ARRAY_A );
				foreach($active_sites as $site){
					$active_users[ $site['blog_id'] ] = trim( $site['path'], '/' );
				}
				
				// Check which values match
				$matched_sites = array_intersect($active_users, $archive_users);
				
				// Display results
				echo '<h3>' . __( 'Archived Sites', 'ufclas-admin-tools' ) . '</h3>';
				echo '<style type="text/css">
					#ufclas-admin-tools-form #usernames {min-height:400px;}
					table.ufclas-admin-tools {border-collapse: collapse; min-width: 25%;}
					table.ufclas-admin-tools td, table.ufclas-admin-tools th {border: 1px solid #ccc; padding: 0.5em 0.8em; background-color:#fff; text-align: left;}
					table.ufclas-admin-tools .archived td {background-color: #FFDFDF;}
				</style>';
				echo '<table class="ufclas-admin-tools">';
                echo '<tr><th>Username</th><th>URL</th><th>Action</th></tr>';
				$row_format = '<tr class="%s"><td>%s</td><td>%s</td><td>%s</td></tr>';
				foreach($archive_users as $user){
					if( $id = array_search( $user, $matched_sites ) ){
						update_blog_status( $id, 'archived', '1' );
						printf($row_format, 'archived', $matched_sites[$id], 'Archived');
					}
					else {
						printf($row_format, '', $user, 'Site not found.');
					}
				}
				echo '</table>';
			}

		} 
		else {
			// Nothing posted, display default form
		?>
        	<style type="text/css">
					#ufclas-admin-tools-form #usernames {min-width:300px; min-height:300px;}
			</style>
            <form id="ufclas-admin-tools-form" action="" method="post">
            	<p><label for-"usernames"><?php _e( 'Enter list of usernames, one per line.', 'ufclas-admin' ); ?></label></p>
                <textarea id="usernames" name="usernames"></textarea>
                <?php wp_nonce_field( 'ufclas-admin-tools-getusers', '_wpufclasnonce' ); ?>
				<?php submit_button('Archive Sites'); ?>
                <input type="hidden" name="action" value="getusers" />
            
            </form>
		<?php } ?>
		</div>
        <?php
}