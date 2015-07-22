jQuery(function($){
   	// Create data object
	var data = {};
	data['action'] = ufca_data.action;
	data[ufca_data.nonce_name] = ufca_data.nonce_value;
	
	// Use WordPress to fetch data
	$.post(ajaxurl, data, function(response) {
			// Display data in a customizable table
			$('.ufca-datatable').DataTable({ 
				'data': JSON.parse(response),
				'dom': 'T<"clear">lfrtip',
				'tableTools': { 
					'sSwfPath': ufca_data.plugin_url + '/lib/jquery.datatables/copy_csv_xls.swf',
					'aButtons': [ "copy", "csv", "print" ]
				}
			});
			$('.ufca-datatable').fadeIn('fast');
	});
});