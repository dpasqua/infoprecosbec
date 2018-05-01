<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- jquery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <!-- bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>

  <!-- google maps -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type = "text/javascript">
     google.charts.load('current', {packages: ['corechart','line']});  
  </script>

  <!-- project js-->
  <script type="text/javascript" src="/becprecos.js"></script>
  
  <style>
    .table-wrapper-2 {
        display: block;
        max-height: 400px;
        overflow-y: auto;
        -ms-overflow-style: -ms-autohiding-scrollbar;
    }

    /* Center the loader */
    #loader {
      position: absolute;
      display: none;
      left: 50%;
      top: 50%;
      z-index: 1;
      width: 150px;
      height: 150px;
      margin: -75px 0 0 -75px;
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite;
      animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
      0% { -webkit-transform: rotate(0deg); }
      100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Add animation to "page content" */
    .animate-bottom {
      position: relative;
      -webkit-animation-name: animatebottom;
      -webkit-animation-duration: 1s;
      animation-name: animatebottom;
      animation-duration: 1s
    }

    @-webkit-keyframes animatebottom {
      from { bottom:-100px; opacity:0 } 
      to { bottom:0px; opacity:1 }
    }

    @keyframes animatebottom { 
      from{ bottom:-100px; opacity:0 } 
      to{ bottom:0; opacity:1 }
    }

    #resultDiv {
      display: none;
      text-align: center;
    }

  </style>

</head>
<body>
  
<div class="container">

<div class="row justify-content-center">
  <h1>BEC Preços</h1>
</div>

<div class="container-fluid">
    <form id="formBuscar" name="formBuscar">
        <div class="form-group">
            <label for="inputProduto">Produto</label>
            <input type="input" name="produto" class="form-control" id="inputProduto" placeholder="Digite aqui o produto desejado" />
        </div>

        <div class="form-group">
            <label for="inputUC">UC/UGE</label>
            <input type="input" name="uc" class="form-control" id="inputUC" placeholder="Digite o nome ou código da UC/UGE que será usado como referência" />
        </div>
        
        <div class="form-row">

            <div class="form-group col-md-4">
                <label for="inputDataInicial">Data Inicial</label>
                <input type="input" name="data_inicial" class="form-control" id="inputDataInicial" placeholder="Data mais recente"> 
            </div>

            <div class="form-group col-md-4">
                <label for="inputDataFinal">Data Final</label>
                <input type="input" name="data_final" class="form-control" id="inputDataFinal" placeholder="Data mais antiga"> 
            </div>

            <div class="form-group col-md-2">
                <label for="selectRaio">Raio da Busca</label>
                <select name="raio" class="form-control" id="selectRaio">
                    <option value="null">Livre</option>
                    <option value="25000">25km</option>
                    <option value="50000">50km</option>
                    <option value="100000">100km</option>
                </select>
            </div>
            
            <div class="form-group col-md-2" style="margin-top: 35px;">
                <button type="button" onclick="submitBusca()" class="btn btn-primary btn-sm">🔍 Buscar Referências</button>
            </div>

        </div>
    </form>
</div>

<hr />

<div id="loader"></div>

<div style="display:none;" id="resultDiv" >

<div class="row mt-4">

    <div class="col-lg-6">
      <div id="googleMap" style="width:97%;height:400px;"></div>  
    </div>

    <div class="col-lg-6 table-wrapper-2 table-responsive">

<table id="myTable" class="table table-hover"">
  <thead class="thead-dark table-sm" style="font-size:14px;">
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

</div>

<hr />
<div class="row justify-content-center mt-4">
    <h3>Infográfico</h3>
</div>

<div class="row mt-2">
    <div class="col-lg-4 align-self-center">
        <p><h4 class="text-center">O valor de compra unitário mais baixo deste produto foi registrado em <b id="unitario_min_mes"></b> pelo valor de <span class="text-danger" id="unitario_min_vl"></span></h4></p>
    </div>
    <div class="col-lg-8">
        <div id="graph_comparativo_preco_medio_qtde_oc" style="width: 700px; height: 300px; margin: 0 auto"></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div id="graph_unidade_compradora_regiao" style="width: 700px; height: 400px; margin: 0 auto"></div>
    </div>

    <div class="col-lg-3 align-self-center">
        <p><h4 class="text-center">A localiade que mais comprou esse produto foi a região intermediária de <span class="text-success" id="localidade_max_regiao1"></span>, seguido pelas regiões de <span class="text-warning" id="localidade_max_regiao2"></span> e <span class="text-danger" id="localidade_max_regiao3"></span></h4></p>
    </div>

</div>

<div class="row">
    <div class="col-lg-4 align-self-center">
        <p><h4 class="text-center">Os órgãos do município de <span class="text-success" id="investimento_municipio"></span> investiram juntos um montante de <span class="text-danger" id="investimento_valor"></span> no produto pesquisado.</h4>
        </p>
        <p><h4 class="text-center">Seu maior comprador é o Órgão <b id="orgao_comprador_max"></b></h4></p>
    </div>
    <div class="col-lg-8">
        <div id="graph_comparativo_preco_medio" style="width: 700px; height: 400px; margin: 0 auto"></div>
    </div>
</div>

<hr />

<div class="row justify-content-center mt-4">
    <h3>Fornecedores</h3>
</div>

<div class="row">
<p>No período pesquisado foram executados <b id="oc_num"></b> OC (Ordens de Compra) para este produto no portal BEC. Participaram <b id="fornecedores_participantes">53</b> diferentes fornecedores sendo <b id="vencedores_diferentes">18</b> diferentes vencedores. </p>

<ul>
    <li id="fornecedores_epp"></li>
    <li id="fornecedores_outros"></li>
</ul>
</div>

</div>

</div>

<script language = "JavaScript">

// global
var becprecos = new BecPrecos(document);

$(document).ready(function() {
    // date picker
    var datepicker_options = { language: "pt-BR", format: "dd/mm/yyyy"};
    $('#inputDataInicial').datepicker(datepicker_options);
    $('#inputDataFinal').datepicker(datepicker_options);

    // autocomplete prefeituras
    $('#inputUC').autocomplete({
        source: "/becprecos/auto-prefeituras",
        minLength: 2
    });

    // autocomplete produtos
    $('#inputProduto').autocomplete({
        source: "/becprecos/auto-produtos",
        minLength: 2
    }); 
});

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

      /* charts */
      becprecos.chart1(data.chart1);
      becprecos.chart2(data.chart2);
      becprecos.chart3(data.chart3);

      /* info geral */
      for(var key in data.infoGeral) {
        $('#' + key).html(data.infoGeral[key]); 
      }

      $('#loader').hide();
      $('#resultDiv').show();
  })  
}

</script>

<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>

<script async defer 
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhtS5U20lVTSdHMti3O0iol7Vqzd2QMaI&callback=initMap"></script>

</body>
</html>
