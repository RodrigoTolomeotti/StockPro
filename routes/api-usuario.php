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

$router->group(['prefix' => 'campanha', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'CampanhaController@getAll');
    $router->post('/', 'CampanhaController@create');
    $router->put('/{id}', 'CampanhaController@update');
    $router->patch('/{id}/ativar', 'CampanhaController@ativar');
    $router->patch('/{id}/inativar', 'CampanhaController@inativar');

});

$router->group(['prefix' => 'cargo', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'CargoController@getAll');

});

$router->group(['prefix' => 'origem', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'OrigemController@getAll');

});

$router->group(['prefix' => 'departamento', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'DepartamentoController@getAll');

});

$router->group(['prefix' => 'profissao', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'ProfissaoController@getAll');

});

$router->group(['prefix' => 'grupo-contato', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'GrupoContatoController@getAll');
    $router->post('/', 'GrupoContatoController@create');
    $router->put('/{id}', 'GrupoContatoController@update');
    $router->delete('/{id}', 'GrupoContatoController@delete');

});

$router->group(['prefix' => 'retorno', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'RetornoController@getAll');
    $router->get('/export', 'RetornoController@exportCSV');
    $router->post('/', 'RetornoController@create');
    $router->put('/{id}', 'RetornoController@update');

});

$router->group(['prefix' => 'template', 'middleware' => 'auth'], function () use ($router) {

    $router->post('upload-file', 'TemplateController@uploadFile');

});

$router->group(['middleware' => 'auth'], function () use ($router) {

    $router->post('teste-email', 'TemplateController@testarEnvio');

});

$router->group(['middleware' => 'auth'], function () use ($router) {

    $router->post('teste-whatsapp', 'TemplateController@testarEnvioWhatsapp');

});

$router->group(['prefix' => 'notificacao', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'NotificacaoController@getAll');
    $router->get('/titulo/{titulo}', 'NotificacaoController@findByTitulo');

});

$router->group(['prefix' => 'envio', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'EnvioController@getAll');

});

$router->group(['prefix' => 'relatorio', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/', 'RelatorioController@getAll');

});

// $router->group(['prefix' => 'usuarios', 'middleware' => 'auth'], function () use ($router) {
//
//     $router->get('/', 'UsuarioController@getAll');
//
// });

// $router->group(['prefix' => 'nivel-usuario', 'middleware' => 'auth'], function () use ($router) {
//
//     $router->get('/', 'NivelUsuarioController@getAll');
//
// });
