<?php

namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\Faker\Generator as Faker;
$factory->define(App\User::class, function (Faker $faker) {
    return ['name' => $faker->name, 'email' => $faker->unique()->safeEmail, 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'remember_token' => str_random(10)];
});
