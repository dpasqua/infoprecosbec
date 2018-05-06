class BecPrecos
{
    constructor(document)
    {
        // global
        this.markerCluster = null;
        this.marker = null;
        this.map = null;
        this.raio = null;
        this.center = null;
        this.document = document;

        this.chart1 = null;
        this.chart2 = null;
        this.chart3 = null;
        this.chart4 = null;
        this.chart5 = null;
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
        if(this.marker != null) {
            this.marker.setMap(null);
        }

        this.marker = new google.maps.Marker({position: this.center, map: this.map});
        this.marker.addListener('click', function() {
            infowindow.open(this.map, marker);
            self.selecionaTableRow(2);
        });

        infowindow.open(this.map, this.marker);
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
            case 5000:
                this.map.setZoom(12);
                break;
            case 10000:
                this.map.setZoom(11);
                break;
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
        if(this.markerCluster != null) {
            this.markerCluster.clearMarkers();
        }

        var markers = [];
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

            markers.push(m);
        })

        this.markerCluster = new MarkerClusterer(this.map, markers,
            {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
    }

    selecionaTableRow(idx)
    {
        // limpa class de todas tr
        $('#myTable tr').each(function(i, row) {
            var $row = $(row);
            $row.removeClass('lime');
            $row.removeClass('lighten-4');
        })

        // marca tr
        $('#myTable tr:eq(' + idx + ')').addClass('lime');
        $('#myTable tr:eq(' + idx + ')').addClass('lighten-4');
    }

    selecionaTableFornecedorRow(idx)
    {
        // limpa class de todas tr
        $('#myTableFornecedor tr').each(function(i, row) {
            var $row = $(row);
            $row.removeClass('lime');
            $row.removeClass('lighten-4');
        })

        // marca tr
        $('#myTableFornecedor tr:eq(' + idx + ')').addClass('lime');
        $('#myTableFornecedor tr:eq(' + idx + ')').addClass('lighten-4');
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

    parseTableFornecedor(data)
    {
        $('#myTableFornecedor > tbody').empty();
        var text = '';
        data.forEach(d => {
            text += '<tr>';
            text += '<td>' + d[0] +'</td>';
            text += '<td>' + d[1] +'</td>';
            text += '<td>' + d[2] +'</td>';
            text += '<td>' + d[3] +'</td>';
            text += '<td>' + d[4] +'</td>';
            text += '</tr>';
        });
        $('#myTableFornecedor > tbody:last-child').append(text);
        this.selecionaTableFornecedorRow(2);
    }

    // chart1
    newChart1(ctx, dataChart)
    {
        if(this.chart1 != null) {
            this.chart1.destroy();
        }

        this.chart1 = new Chart(ctx, {
            type: 'bar',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    stacked: true }]
            },
            options: {
                legend: {
                    display: true,
                },
                title: {
                    display: true,
                    text: 'Comparativo de preços médios praticados relacionado a quantidade de OCs'
                }
            },
            data: {
                datasets: [
                    {
                        label: 'Qtd. OC',
                        data: dataChart.qtde_oc,
                        backgroundColor: dataChart.bgcolor,
                        borderColor: [
                            'rgba(54, 162, 235, 1)'
                        ],
                    },
                    {
                        label: 'Preço mais baixo licitado',
                        data: dataChart.preco_min,
                        type: 'line',
                        borderColor: [
                            'rgba(255, 99, 132, 1)'
                        ],
                    },
                    {
                        label: 'Preço médio licitado',
                        data: dataChart.preco_medio,
                        type: 'line',
                        borderColor: [
                            'rgba(255, 206, 86, 1)'
                        ],
                    }
                ],
                labels: dataChart.labels
            }
        });
    }

    newChart4(ctx4, dataChart)
    {
        var mixedChart = new Chart(ctx4, {
            type: 'bar',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    stacked: true }]
            },
            options: {
                legend: {
                    display: true,
                },
                title: {
                    display: true,
                    text: 'Preços médios por regiões'
                }
            },
            data: {
                datasets: [
                    {
                        label: 'Preço Médio',
                        data: dataChart.preco_medio,
                        backgroundColor: dataChart.bgcolor,
                        borderColor: [
                            'rgba(54, 162, 235, 1)'
                        ]
                    }
                ],
                labels: dataChart.labels
            }
        });
    }

    // chart2
    newChart2(ctx2, dataChart)
    {
        if(this.chart2 != null) {
            this.chart2.destroy();
        }

        this.chart2 = new Chart(ctx2,{
            type: 'pie',
            responsive: true,
            maintainAspectRatio: true,
            options: {
                legend: {
                    display: true
                },
                title: {
                    display: true,
                    text: 'Unidades Compradas por Região Geográfica'
                },
                labels: ['label'],
                tooltips: {
                    mode: 'index'
                }
            },
            data: {
                datasets: [
                    {
                        label:  [
                            'Porcentagem'
                        ],
                        data: dataChart.porcentagem,
                        backgroundColor: [
                            'blue',
                            'red',
                            'orange',
                            'green',
                            'purple',
                            'cyan',
                            'pink',
                            'darkGreen',
                            'darkRed',
                            'darkBlue',
                            'grey'
                        ]
                    }
                ],
                labels: dataChart.labels
            }
        });
    }

    // chart3
    newChart3(ctx3, dataChart)
    {
        var myBarChart = new Chart(ctx3, {
            type: 'horizontalBar',
            responsive: true,
            maintainAspectRatio: true,
            options: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: 'Top 10 municípios que mais compraram'
                }
            },
            data: {
                datasets: [
                    {
                        data: dataChart.data,
                        backgroundColor: [
                            '#7030A0',
                            '#0F2D69',
                            '#89BC01',
                            '#00B0F0',
                            '#00B050',
                            '#92D050',
                            '#FFFF00',
                            '#FFC000',
                            '#FF0000',
                            '#C74444'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)'
                        ],
                    }
                ],
                labels: dataChart.labels
            },
        });
    }

    // chart2
    newChart5(ctx5, dataChart)
    {
        var myPieChart = new Chart(ctx5,{
            type: 'pie',
            responsive: true,
            maintainAspectRatio: true,
            options: {
                legend: {
                    display: true
                },
                title: {
                    display: true,
                    text: 'Unidades Compradas por Região Geográfica'
                },
                labels: ['label'],
                tooltips: {
                    mode: 'index',
                    callbacks: {
                        afterLabel: function(tooltipItem, data) {
                            var sum = data.datasets.reduce((sum, dataset) => {
                                return sum + dataset.data[tooltipItem.index];
                            }, 0);
                            var percent = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] / sum * 100;
                            percent = percent.toFixed(2); // make a nice string
                            return data.datasets[tooltipItem.datasetIndex].label + ': ' + percent + '%';
                        }
                    }
                }
            },
            data: {
                datasets: [
                    {
                        label:  [
                            'Porcentagem'
                        ],
                        data: dataChart.porcentagem,
                        backgroundColor: [
                            'blue',
                            'red',
                            'orange',
                            'green'
                        ]
                    }
                ],
                labels: dataChart.labels
            }
        });
    }
}