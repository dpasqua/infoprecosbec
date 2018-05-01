<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return view('index', []);
});

$router->get('becprecos',  ['uses' => 'BecprecosController@index']);
$router->get('becprecos/auto-prefeituras',  ['uses' => 'BecprecosController@autoCompletePrefeituras']);
$router->get('becprecos/auto-produtos',  ['uses' => 'BecprecosController@autoCompleteProdutos']);
$router->post('becprecos/buscar',  ['uses' => 'BecprecosController@buscarReferencias']);