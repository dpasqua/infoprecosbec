<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hackathon</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">

    <!-- jquery -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- materialize -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>

    <!-- chart -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>

    <!-- project js e css-->
    <script type="text/javascript" src="/becprecos.js"></script>
    <link rel="stylesheet" href="/becprecos.css"></link>

</head>
<body>
<nav>
    <div class="nav-wrapper center-align">
        <span class="brand-logo">InfoPreços BEC</span>
    </div>
</nav>
<div class="container">
    <div class="row">

        <div class="s12">
            <ul class="collapsible">
                <li class="active">
                    <div class="collapsible-header"><i class="material-icons">web</i>Formulário</div>
                    <div class="collapsible-body">
                        <div class="row">
                            <div class="col s12">
                                <div class="row">
                                    <form id="formBuscar" name="formBuscar">
                                        <div class="input-field col s12 m6">
                                            <i class="material-icons prefix">description</i>
                                            <input type="text" name="produto" id="inputProduto" class="autocomplete" placeholder="Digite aqui o produto desejado">
                                            <label for="inputProduto">Produto</label>
                                        </div>
                                        <div class="input-field col s12 m6">
                                            <i class="material-icons prefix">location_on</i>
                                            <input type="text" name="uc" id="inputUC" class="autocomplete" placeholder="Digite o nome ou código da UC/UGE que será usado como referência">
                                            <label for="inputUC">UC</label>
                                        </div>
                                        <div class="input-field col s12 m6 l3">
                                            <i class="material-icons prefix">event_available</i>
                                            <input type="text" name="data_inicial" class="datepicker" id="inputDataInicial" placeholder="Data mais recente">
                                            <label for="inputDataInicial">Data Inicial</label>
                                        </div>
                                        <div class="input-field col s12 m6 l3">
                                            <i class="material-icons prefix">event_busy</i>
                                            <input type="text" name="data_final" class="datepicker" id="inputDataFinal" placeholder="Data mais antiga">
                                            <label for="inputDataFinal">Data Final</label>
                                        </div>
                                        <div class="input-field col s12 m6 l4">
                                            <i class="material-icons prefix">location_searching</i>
                                            <select name="raio" class="form-control" id="selectRaio">
                                                <option value="5000">5km</option>
                                                <option value="10000">10km</option>
                                                <option value="25000">25km</option>
                                                <option value="50000">50km</option>
                                                <option value="100000">100km</option>
                                            </select>
                                            <label for="selectRaio">Raio da Busca</label>
                                        </div>
                                        <div class="input-field col s12 m6 l2">
                                            <button type="button" onclick="submitBusca()" class="btn waves-effect" style="width: 100%;"><i class="material-icons left">search</i>Buscar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div id="loader"></div>
        <div id="resultDiv">
            <div class="s12">
                <ul class="tabs" style="overflow-x: hidden;">
                    <li class="tab col s4"><a href="#resultado" class="active">Resultado</a></li>
                    <li class="tab col s4"><a href="#infograficos">Infográficos</a></li>
                    <li class="tab col s4"><a href="#fornecedores">Fornecedores</a></li>
                </ul>
            </div>
            <div class="s12">
                <div id="resultado">
                    <div id="googleMap" style="height:400px; margin: 25px 0;"></div>
                    <table id="myTable" class="striped highlight responsive-table">
                        <thead>
                        <tr>
                            <th scope="col">COD UC - Órgão</th>
                            <th scope="col">Qtd. OC</th>
                            <th scope="col">Qtd. Unit.</th>
                            <th scope="col">Valor Max. Unit.</th>
                            <th scope="col">Valor Min. Unit.</th>
                            <th scope="col">Média</th>
                            <th scope="col">Ata</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div id="infograficos">
                    <div class="card">
                        <div class="card-content">
                            <div class="row">
                                <div class="col s12 m4">
                                    <div class="valign-wrapper">
                                        <p class="center-align">O valor de compra unitário mais baixo deste produto foi registrado em <b id="unitario_min_mes"></b> pelo valor de <span class="red-text" id="unitario_min_vl"></span></h4></p>
                                    </div>
                                </div>
                                <div class="col s12 m8">
                                    <canvas id="myChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <div class="row">
                                <div class="col s12 m4">
                                    <p class="center-align">A localidade que mais comprou esse produto foi a região intermediária de <b id="localidade_max_regiao1"></b>, seguido pelas regiões de <b id="localidade_max_regiao2"></b> e <b id="localidade_max_regiao3"></b></p>
                                </div>
                                <div class="col s12 m8">
                                    <canvas id="myChart2"></canvas>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s12 m10">
                                    <canvas id="myChart4"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <div class="row">
                                <div class="col s12 m4">
                                    <p class="center-align">Os órgãos do município de <b id="investimento_municipio"></b> investiram juntos um montante de <b id="investimento_valor"></span> no produto pesquisado.</p>
                                </div>
                                <div class="col s12 m8">
                                    <canvas id="myChart3"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="fornecedores">
                    <div class="card">
                        <div class="row">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12 m4">
                                        <p>No período pesquisado foram executados <b id="oc_num"></b> OC (Ordens de Compra) para este produto no portal BEC. Participaram <b id="fornecedores_participantes"></b> diferentes fornecedores sendo <b id="vencedores_diferentes"></b> diferentes vencedores.</p>
                                        <ul>
                                            <li id="fornecedores_epp"></li>
                                            <li id="fornecedores_outros"></li>
                                        </ul>
                                    </div>
                                    <div class="col s12 m8">
                                        <canvas id="myChart5"></canvas>
                                    </div>
                                    <table id="myTableFornecedor" class="striped highlight responsive-table">
                                        <thead>
                                        <tr>
                                            <th scope="col">Razão Social</th>
                                            <th scope="col">CNPJ</th>
                                            <th scope="col">Porte</th>
                                            <th scope="col">Menor Valor</th>
                                            <th scope="col">Preço Médio</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    // global
    var becprecos = new BecPrecos(document);

    // inicializa mapa
    function initMap() {
        becprecos.initMap();
    }

    function submitBusca() {
        $('#loader').show();
        $('#resultDiv').hide();

        $.post("/becprecos/buscar", $('#formBuscar').serialize() )
            .done(function (data) {
                /* mapa */
                becprecos.mapCenter(data.mapa.center);
                becprecos.geraMarks(data.mapa.points);

                if(data.mapa.raio > 0) {
                    becprecos.geraRaio(parseInt(data.mapa.raio));
                } else {
                    becprecos.limpaRaio();
                    becprecos.setaZoom(11);
                }

                /* table */
                becprecos.parseTable(data.table);

                /* table Fornecedor */
                becprecos.parseTableFornecedor(data.tableFornecedor);

                /* charts */
                var ctx = document.getElementById('myChart').getContext('2d');
                becprecos.newChart1(ctx, data.chart1);
                var ctx2 = document.getElementById('myChart2').getContext('2d');
                becprecos.newChart2(ctx2, data.chart2);
                var ctx3 = document.getElementById('myChart3').getContext('2d');
                becprecos.newChart3(ctx3, data.chart3);
                var ctx4 = document.getElementById('myChart4').getContext('2d');
                becprecos.newChart4(ctx4, data.chart4);
                var ctx5 = document.getElementById('myChart5').getContext('2d');
                becprecos.newChart5(ctx5, data.chart5);

                /* info geral */
                for(var key in data.infoGeral) {
                    $('#' + key).html(data.infoGeral[key]);
                }

                $('#loader').hide();
                $('#resultDiv').show();
            })
    }

    $(document).ready(function() {
        // autocomplete prefeitura
        $.get('/becprecos/auto-prefeituras', function(data) {
            var listaUC = data;

            // autocomplete prefeituras
            $('#inputUC').autocomplete({
                data: listaUC
            });
        });

        // autocomplete produtos
        $.get('/becprecos/auto-produtos', function(data) {
            var listaProdutos = data;

            // autocomplete prefeituras
            $('#inputProduto').autocomplete({
                data: listaProdutos
            });
        });

        let datepicker_options = {
            format  : "dd/mm/yyyy",
            i18n    : {
                cancel          : "Cancelar",
                clear           : "Limpar",
                months          :
                    [
                        'Janeiro',
                        'Fevereiro',
                        'Março',
                        'Abril',
                        'Maio',
                        'Junho',
                        'Julho',
                        'Agosto',
                        'Setembro',
                        'Outubro',
                        'Novembro',
                        'Dezembro'
                    ],
                monthsShort     :
                    [
                        'Jan',
                        'Fev',
                        'Mar',
                        'Abr',
                        'Mai',
                        'Jun',
                        'Jul',
                        'Ago',
                        'Set',
                        'Out',
                        'Nov',
                        'Dez'
                    ],
                weekdays        :
                    [
                        'Domingo',
                        'Segunda',
                        'Terça',
                        'Quarta',
                        'Quinta',
                        'Sexta',
                        'Sábado'
                    ],

                weekdaysShort   :
                    [
                        'Dom',
                        'Seg',
                        'Ter',
                        'Qua',
                        'Qui',
                        'Sex',
                        'Sab'
                    ],
                weekdaysAbbrev  : [ 'D', 'S', 'T', 'Q', 'Q', 'S', 'S' ]
            }
        };


        $('.collapsible').collapsible();
        $('.datepicker').datepicker(datepicker_options);
        $('select').formSelect();
        $('.tabs').tabs();
    });

</script>

<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhtS5U20lVTSdHMti3O0iol7Vqzd2QMaI&callback=initMap"></script>

</body>
</html>