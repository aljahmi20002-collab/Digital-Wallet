<?php

use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

Route::prefix('install')->as('install.')->controller(InstallerController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/database-test', 'testDatabase')->name('database.test');
    Route::post('/', 'store')->name('store');
});
