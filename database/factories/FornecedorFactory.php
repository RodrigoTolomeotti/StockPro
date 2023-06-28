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

$factory->define(App\Fornecedor::class, function (Faker\Generator $faker) {
    $user = App\Usuario::all()->random(1)->first();
    return [
        'usuario_id' => $user->id,
        'nome' => $faker->company,
        'telefone' => $faker->randomNumber(),
        'email' => $faker->safeEmail,
        'endereco' => $faker->address
    ];
});
