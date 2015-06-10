jQuery(function($){
   	var data = {
		'action': 'ufca_site_info',
		'site_info_nonce': ufca_data.site_info_nonce
	};
	// Use WordPress to fetch data
	$.post(ajaxurl, data, function(response) {
			// Display data in a customizable table
			$('#info-table').DataTable({ 
				'data': JSON.parse(response),
				'dom': 'T<"clear">lfrtip',
				'tableTools': { 
					'sSwfPath': ufca_data.plugin_url + '/lib/jquery.datatables/copy_csv_xls.swf',
					'aButtons': [ "copy", "csv", "print" ]
				}
			});
			$('#info-table').fadeIn('fast');
	});
});