<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Produto::class, function (Faker\Generator $faker) {
    $user = App\Usuario::all()->random(1)->first();
    return [
        'nome' => $faker->name,
        'preco_unitario' => $faker->numberBetween(50,1000),
        'tipo_produto_id' => $faker->numberBetween(1,2),
        'descricao' => $faker->text,
        'imagem' => null,
        'usuario_id' => $user->id,
    ];
});
