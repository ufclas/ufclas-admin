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
		
		if (pagenow == 'toplevel_page_ufclas-admin-main-network'){
			// Initialize Donut chart using D3.js
			var responseData = JSON.parse(response); responseData = responseData[0];
			var chartDataTotal = d3.sum(responseData);
			
			// Use table headings as labels
			var chartData = [];
			$('#admin th').each(function(index){
				chartData.push({label: $(this).text(), count: responseData[index]});
			});
			
			var width = 960,
				height = 500,
				radius = Math.min(width, height) / 2;
			
			var color = d3.scale.ordinal()
				.range(["#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c"]);
			
			var svg = d3.select("#chart").append("svg")
				.attr("width", width)
				.attr("height", height)
				.append("g")
				.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
			
			svg.style('opacity', 0)
				.transition()
				.duration(400)
				.style('opacity', 1);
			
			var arc = d3.svg.arc()
				.outerRadius(radius - 10)
				.innerRadius(radius - 70);
			
			var pie = d3.layout.pie()
				.sort(null)
				.value(function(d) { return d.count; });
			
			var g = svg.selectAll(".arc")
				.data(pie(chartData))
				.enter().append("g")
				.attr("class", "arc");
			
			  g.append("path")
				  .attr("d", arc)
				  .style("fill", function(d) { return color(d.data.label); });
			
			  g.append("text")
				.attr("transform", function(d) { 
					return "translate(" + arc.centroid(d) + ")"; 
				})
				.attr("dy", ".35em")
				.text(function(d) {
					if(d.data.count == 0) { return '';}
					else {return d.data.label + ' (' + d3.round(100*d.data.count/chartDataTotal, 1) + '%)';}
				});
		}
			  
	});
	// Remove loading animation when table fully loaded
	$('.ufca-datatable').on('init.dt', function(){
		$('#loading span').removeClass('glyphicon-refresh-animate');
		$(this).fadeIn();
	});
});