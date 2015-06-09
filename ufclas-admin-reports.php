<?php 
function ufclas_admin_reports_page(){
	if ( !current_user_can( 'manage_network_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
    
    <div class="wrap">
		<div id="icon-tools" class="icon32"></div>
		<h2><?php _e( 'Reports', 'ufclas-admin' ); ?></h2>
        
    <?php
	$reports_list = new Reports_List_Table();
	$reports_list->prepare_items();
	$reports_list->display();
	?>
    </div>
    <?php 
 
}