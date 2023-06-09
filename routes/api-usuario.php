<?php

use Illuminate\Support\Facades\Crypt;
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

$router->get('/receive/{token}', 'EnvioController@gravarAbertura');

$router->group(['prefix' => 'usuario'], function () use ($router) {

    $router->post('login', 'UsuarioController@login');

    $router->group(['middleware' => 'auth'], function () use ($router) {

        $router->get('/', 'UsuarioController@getCurrentUser');
        $router->get('/telas', 'UsuarioController@getTelas');

        $router->put('/usuario', 'UsuarioController@updateUsuario');
        $router->put('/conta', 'UsuarioController@updateConta');
        $router->put('/smtp', 'UsuarioController@updateSMTP');
        $router->get('/smtp', 'UsuarioController@testarSMTP');
        $router->get('/imap', 'UsuarioController@testarIMAP');
        $router->put('/imap', 'UsuarioController@updateIMAP');
        // $router->put('/{id}', 'UsuarioController@update');

        $router->post('/imagem', 'UsuarioController@uploadImagem');
        // $router->post('/', 'UsuarioController@create');

    });

});

$router->group(['prefix' => 'contato', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'ContatoController@getAll');
    $router->get('/contato', 'ContatoController@getContatoCampanha');
    $router->get('/contatos/{campanha}', 'ContatoController@getContatosSemEnvioCampanha');
    $router->get('/bloqueios/{contato_id}', 'ContatoController@getBloqueios');
    $router->get('/export', 'ContatoController@exportCSV');
    $router->get('/exportSpotter', 'ContatoController@exportSpotter');
    $router->post('/', 'ContatoController@create');
    $router->put('/{id}', 'ContatoController@update');
    $router->delete('/{id}', 'ContatoController@delete');
    $router->delete('/bloqueios/{bloqueio_id}', 'ContatoController@deleteBloqueio');
    $router->post('/upload', 'ContatoController@importCSV');

});

$router->group(['prefix' => 'origem', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'OrigemController@getAll');

});

$router->group(['prefix' => 'cliente', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'ClienteController@getAll');
    $router->post('/', 'ClienteController@create');
    $router->put('/{id}', 'ClienteController@update');
    $router->delete('/{id}', 'ClienteController@delete');

});

$router->group(['prefix' => 'produto', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'ProdutoController@getAll');
    $router->post('/', 'ProdutoController@create');
    $router->put('/{id}', 'ProdutoController@update');
    $router->delete('/{id}', 'ProdutoController@delete');

    $router->post('/imagem', 'ProdutoController@uploadImagem');

});

$router->group(['prefix' => 'tipo-produto', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'TipoProdutoController@getAll');
    $router->post('/', 'TipoProdutoController@create');
    $router->put('/{id}', 'TipoProdutoController@update');
    $router->delete('/{id}', 'TipoProdutoController@delete');

    $router->post('/imagem', 'TipoProdutoController@uploadImagem');

});

$router->group(['prefix' => 'fornecedor', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'FornecedorController@getAll');
    $router->post('/', 'FornecedorController@create');
    $router->put('/{id}', 'FornecedorController@update');
    $router->delete('/{id}', 'FornecedorController@delete');
});

$router->group(['prefix' => 'estoque', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'EstoqueController@getAll');
    $router->post('/', 'EstoqueController@create');
    $router->put('/{id}', 'EstoqueController@update');
    $router->delete('/{id}', 'EstoqueController@delete');
});

$router->group(['prefix' => 'pedido', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'PedidoController@getAll');
    $router->post('/', 'PedidoController@create');
    $router->put('/{id}', 'PedidoController@update');
    $router->delete('/{id}', 'PedidoController@delete');
});

$router->group(['prefix' => 'item-pedido', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'itemPedidoController@getAll');
    $router->post('/', 'itemPedidoController@create');
    $router->put('/{id}', 'itemPedidoController@update');
    $router->delete('/{id}', 'itemPedidoController@delete');
});

$router->group(['prefix' => 'notificacao', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'NotificacaoController@getAll');
    $router->get('/titulo/{titulo}', 'NotificacaoController@findByTitulo');

});

$router->group(['prefix' => 'relatorio', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'RelatorioController@getAll');

});
