<?php

use App\Http\Controllers\Api\V1\Admin\ExamplePostController;
use App\Http\Controllers\Api\V1\Files\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
| Admin routes  → jwt.auth + localize middleware
| Public routes → localize only
*/

Route::prefix('v1')->group(function () {

    // File upload (authenticated, any role)
    Route::middleware(['jwt.auth', 'localize'])->group(function () {
        Route::post('files/upload', [FileController::class, 'upload']);
    });

    // Admin: full CRUD, JWT protected
    Route::middleware(['jwt.auth', 'localize'])->prefix('admin')->group(function () {
        Route::apiResource('example-posts', ExamplePostController::class);
    });

    // Site: public-facing read endpoints
    Route::middleware(['localize'])->prefix('site')->group(function () {
        //
    });
});
