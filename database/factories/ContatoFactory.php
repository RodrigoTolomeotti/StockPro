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

$factory->define(App\Contato::class, function (Faker\Generator $faker) {
    $username = $faker->userName;
    $user = App\Usuario::all()->random(1)->first();
    return [
        'usuario_id' => $user->id,
        'nome' => $faker->name,
        'empresa' => $faker->company,
        'email' => $faker->email,
        'telefone' => $faker->e164PhoneNumber,
        'cargo_id' => App\Cargo::all()->random(1)->first()->id,
        'departamento_id' => App\Departamento::all()->random(1)->first()->id,
        'profissao_id' => App\Profissao::all()->random(1)->first()->id,
        'grupo_contato_id' => $user->grupos_contato()->get()->random(1)->first()->id,
        'facebook_link' => "https://www.facebook.com/$username",
        'linkedin_link' => "https://www.linkedin.com/in/$username",
        'instagram_link' => "https://www.instagram.com/$username",
        'twitter_link' => "https://twitter.com/$username",
    ];
});
