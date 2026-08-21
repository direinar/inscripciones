<?php

use App\Providers\AppServiceProvider;

$providers = [
    AppServiceProvider::class,
];

if (class_exists(\Laravel\Boost\BoostServiceProvider::class)) {
    $providers[] = \Laravel\Boost\BoostServiceProvider::class;
}

return $providers;
