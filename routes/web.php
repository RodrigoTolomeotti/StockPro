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
$router->get('configuracoes', function () use ($router) {
    return view('interfaces.configuracoes');
});
$router->get('clientes', function() use ($router) {
    return view('interfaces.clientes');
});
$router->get('produtos', function() use ($router) {
    return view('interfaces.produtos');
});
$router->get('tipos-produtos', function() use ($router) {
    return view('interfaces.tiposProdutos');
});
$router->get('fornecedores', function() use ($router) {
    return view('interfaces.fornecedores');
});
$router->get('estoque', function() use ($router) {
    return view('interfaces.estoque');
});
$router->get('pedido', function() use ($router) {
    return view('interfaces.pedido');
});