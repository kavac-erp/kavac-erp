<?php

use App\Http\Controllers\Admin\ModuleController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register module routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth', 'verified']], function () {
    /*
     | -----------------------------------------------------------------------
     | Ruta para obtener archivos de módulos
     | -----------------------------------------------------------------------
     |
     | Permite mostrar archivos almacenados en los módulos de la aplicación
     */
    Route::get('assets/{module}/{type}/{file}', function ($module, $type, $file) {
        $module = ucfirst($module);

        $path = base_path("modules/$module/Resources/assets/$type/$file");

        if (!File::exists($path)) {
            abort(404);
        }
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    /*
     | -----------------------------------------------------------------------
     | Rutas para gestionar los módulos del sistema a través de la aplicación
     | -----------------------------------------------------------------------
     |
     | Permite gestionar los módulos del sistema a través de la configuración
     | de cada uno de los módulos
     */
    Route::group(['prefix' => 'modules'], function () {
        Route::get('check/installed/{module}', [ModuleController::class, 'checkInstalled']);
        Route::get('disable/{module}', [ModuleController::class, 'setDisabled']);
        Route::get('enable/{module}', [ModuleController::class, 'setEnabled']);
    });
});
