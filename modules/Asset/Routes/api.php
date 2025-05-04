<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Asset\Http\Controllers\ExportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')
    /**->middleware(['auth:api'])*/
    ->group(function () {

        Route::get('/', function () {
            return response()->json(['message' => 'Api Running']);
        });

        Route::prefix('asset')->group(function () {
            Route::resource('export', ExportController::class, ['only' => ['index', 'show']]);
        });
    });
