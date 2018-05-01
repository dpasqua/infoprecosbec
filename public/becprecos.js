class BecPrecos 
{
	constructor(document)
    {
  		// global
		this.markers = [];
		this.map = null;
		this.raio = null;
		this.center = null;
        this.document = document;
    }    

    /* inicializa mapa */
  	initMap() 
  	{
		var mapProp = {
		  zoom:11,
		  disableDefaultUI: true
		};
  		this.map = new google.maps.Map(this.document.getElementById("googleMap"),mapProp);
	}

	/* center map */
	mapCenter(myCenter)
	{
		// mapa
		this.center = new google.maps.LatLng(myCenter[0], myCenter[1]);
		this.map.setCenter(this.center);

		var infowindow = new google.maps.InfoWindow({
		  content: myCenter[2]
		})

		var self = this;
		var marker = new google.maps.Marker({position: this.center, map: this.map});
		marker.addListener('click', function() {
		    infowindow.open(this.map, marker);
		    self.selecionaTableRow(2);
		});

		infowindow.open(this.map, marker);
	}

	/* gera raio */
	geraRaio(_raio)
	{
		this.limpaRaio();
	 
		// raio de 1km
		this.raio = new google.maps.Circle({
			center: this.center,
			radius: _raio,
			strokeColor:"#e50000",
			strokeOpacity:0.8,
			strokeWeight:2,
			fillColor:"#e50000",
			fillOpacity:0.1
		});

		switch(_raio) {
			case 25000:
				this.map.setZoom(10);
				break;
			case 50000:
			this.map.setZoom(9);
				break;
			case 100000:
				this.map.setZoom(8);
				break;
		}
		this.raio.setMap(this.map);
	}

	setaZoom(zoom)
	{
		this.map.setZoom(zoom);
	}

	limpaRaio()
	{
		if(this.raio != null) {
			this.raio.setMap(null);	
		}
	}

	/* gera marks no mapa*/
	geraMarks(points)
	{
		this.markers.forEach(m => {
			m.setMap(null);
		});

		this.markers = [];
		var self = this;
		points.forEach(p => {
			var pi = new google.maps.LatLng(p[0], p[1]);
			var m = new google.maps.Marker({ position: pi });

			m.addListener('click', function() {
				var infowindow = new google.maps.InfoWindow({
				  content: p[2]
				})
				infowindow.open(this.map, m);
				self.selecionaTableRow(p[3] + 1);
			});

			this.markers.push(m);
		}) 

		var markerCluster = new MarkerClusterer(this.map, this.markers,
		  {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
	}

	selecionaTableRow(idx)
	{
		// limpa class de todas tr
		$('#myTable tr').each(function(i, row) {
			var $row = $(row);
			$row.removeClass('table-success');			
		})
		// marca tr
		$('#myTable tr:eq(' + idx + ')').addClass('table-success');
	}

	/* preenche tabela */
	parseTable(data)
	{
	  $('#myTable > tbody').empty();
	    var text = '';
	    data.forEach(d => {
	      text += '<tr>';
	      text += '<td>' + d[0] +'</td>';
	      text += '<td>' + d[1] +'</td>';
	      text += '<td>' + d[2] +'</td>';
	      text += '<td>' + d[3] +'</td>';
	      text += '<td>' + d[4] +'</td>';
	      text += '<td>' + d[5] +'</td>';
	      text += '<td>' + d[6] +'</td>';
	      text += '</tr>';
	  });
	  $('#myTable > tbody:last-child').append(text);
	  this.selecionaTableRow(2);
	}

	/* grafico comparativo precos medios*/
	chart1(dataTable) 
	{
		// Define the chart to be drawn.
		var data = google.visualization.arrayToDataTable(dataTable);
		  
		// Set chart options
		var options = {
			title : 'Comparativo de preços médios praticados relacionado a quantidade de OCs',
			vAxis: {title: 'Valores em Reais'},
			hAxis: {title: '2018'},
			seriesType: 'bars',
			series: {1: {type:'line'}, 2: {type: 'line'}},
			width:700,
			height:300,
			pointsVisible: true
		};

	    // Instantiate and draw the chart.
	    var chart = new google.visualization.ComboChart(this.document.getElementById('graph_comparativo_preco_medio_qtde_oc'));
	    chart.draw(data, options);
	}

	/* grafico unidades compradas por regiao geografica */
	chart2(tableData) 
	{
		// Define the chart to be drawn.
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Browser');
		data.addColumn('number', 'Percentage');
		data.addRows(tableData);
		   
		// Set chart options
		var options = {
		   'title':'Unidades Compradas por Região Geográfica',
		   'width':700,
		   'height':400,
		   is3D:true
		};

		// Instantiate and draw the chart.
		var chart = new google.visualization.PieChart(this.document.getElementById('graph_unidade_compradora_regiao'));
		chart.draw(data, options);
	}

	chart3(dataTable) 
	{
		// data the chart to be drawn.
		var dataTableHeader = [ ['Cidade', 'Valores', { role: 'style' }] ];

		var data = google.visualization.arrayToDataTable(
		  dataTableHeader.concat(dataTable)
		);
		   
		// Set chart options
		var options = {'title' : 'Top 10 Municípios que mais compraram',
		   hAxis: {
		      title: 'Valores'
		   },
		   vAxis: {
		      title: 'Municípios'
		   },   
		   'width':700,
		   'height':400,
		   'legend': {position: 'none'},
		   pointsVisible: true
		};

		// Instantiate and draw the chart.
		var chart = new google.visualization.BarChart(this.document.getElementById('graph_comparativo_preco_medio'));
		chart.draw(data, options);
	}
}