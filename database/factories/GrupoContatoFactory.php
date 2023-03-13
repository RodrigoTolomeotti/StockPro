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

$factory->define(App\GrupoContato::class, function (Faker\Generator $faker) {
    $username = $faker->userName;
    return [
        'usuario_id' => App\Usuario::all()->random(1)->first()->id,
        'nome' => $faker->jobTitle
    ];
});
