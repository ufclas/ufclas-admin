jQuery(function($){
   	var data = {
		'action': 'ufca_site_info',
		'site_info_nonce': ufca_data.site_info_nonce
	};
	// Use WordPress to fetch data
	$.post(ajaxurl, data, function(response) {
			// Display data in a customizable table
			$('#info-table').DataTable({ 'data': JSON.parse(response) });
			$('#info-table').fadeIn('fast');
	});
});