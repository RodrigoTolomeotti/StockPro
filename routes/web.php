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

$router->get('', function () use ($router) {
    return view('interfaces.login');
});
$router->get('login', function () use ($router) {
    return view('interfaces.login');
});
$router->get('inicio', function () use ($router) {
    return view('interfaces.inicio');
});
$router->get('contatos', function () use ($router) {
    return view('interfaces.contatos');
});
$router->get('campanhas', function () use ($router) {
    return view('interfaces.campanhas');
});
$router->get('grupos-contato', function () use ($router) {
    return view('interfaces.gruposContato');
});
$router->get('configuracoes', function () use ($router) {
    return view('interfaces.configuracoes');
});
$router->get('retornos', function() use ($router) {
    return view('interfaces.retornos');
});
$router->get('envios', function() use ($router) {
    return view('interfaces.envios');
});

// $router->group(['prefix' => 'testar'], function() use ($router) {
//
//     $router->get('enviarEmail', 'UsuarioController@enviarEmail');
//
// });
