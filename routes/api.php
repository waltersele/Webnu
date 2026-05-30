<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['throttle:30,1', 'pre_alta.ingest'])
    ->namespace('Api')
    ->group(function () {
        Route::post('demos/create', 'PreAltaIngestController@store');
    });

Route::prefix('pre-alta')
    ->middleware(['throttle:30,1', 'pre_alta.ingest'])
    ->namespace('Api')
    ->group(function () {
        Route::post('ingest', 'PreAltaIngestController@store');
    });

Route::prefix('signage')
    ->middleware(['digital.signage'])
    ->namespace('Api')
    ->group(function () {
        Route::post('login', 'SignageAuthController@login');

        Route::middleware('auth:api')->group(function () {
            Route::get('me', 'SignageAuthController@me');
            Route::get('account', 'SignageAuthController@account');
            Route::get('menus', 'SignageMenuController@index');
            Route::get('menus/{slug}/version', 'SignageMenuController@version');
            Route::get('menus/{slug}', 'SignageMenuController@show');
            Route::get('tv-templates', 'SignageTvTemplateController@index');
        });
    });
