jQuery(function($){
   	// Create data object
	var data = {};
	data['action'] = ufca_data.action;
	data[ufca_data.nonce_name] = ufca_data.nonce_value;
	
	// Add a loading animation to the table
	$('.ufca-datatable').before( '<div id="loading"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span></div>' );
	
	// Use WordPress to fetch JSON data
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
	})
	.done( function( response ){
		// Initialize DataTable, use array converted from JSON as data source
		$('.ufca-datatable').DataTable({ 
			data: JSON.parse(response),
			dom: 'Blfrtip',
    		buttons: ['copy', 'excel', 'pdf', 'print'],
			lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
		});
	});
	
	// Table fully loaded
	$('.ufca-datatable').on('init.dt', function(){
		$('#loading span').removeClass('glyphicon-refresh-animate');
		$(this).fadeIn();
	});
});