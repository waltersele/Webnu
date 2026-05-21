<?php

use Illuminate\Support\Facades\Route;

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
        });
    });
