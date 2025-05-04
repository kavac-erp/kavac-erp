<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'middleware' => ['web', 'auth', 'verified'],
    'prefix' => 'citizenservice',
], function () {
    Route::get('/', 'CitizenServiceController@index');
    Route::get('settings', 'CitizenServiceSettingController@index')->name('citizenservice.settings.index');
    Route::post('settings', 'CitizenServiceSettingController@store')->name('citizenservice.settings.store');

    /* Ruta para solicitudes*/
    Route::resource('requests', 'CitizenServiceRequestController', ['as' => 'citizenservice', 'only' => ['update']]);

    Route::get('/requests/create', 'CitizenServiceRequestController@create')->name('citizenservice.request.create');
    Route::post('/requests', 'CitizenServiceRequestController@store')->name('citizenservice.request.store');
    Route::get('/requests', 'CitizenServiceRequestController@index')->name('citizenservice.request.index');
    Route::get('requests/edit/{request}', 'CitizenServiceRequestController@edit')->name('citizenservice.request.edit');
    Route::delete('/requests/delete/{request}', 'CitizenServiceRequestController@destroy')
        ->name('citizenservice.request.delete');
    Route::get('requests/vue-list', 'CitizenServiceRequestController@vueList');
    Route::get('requests/vue-info/{request}', 'CitizenServiceRequestController@vueInfo');

    Route::get('requests/vue-pending-list', 'CitizenServiceRequestController@vueListPending');

    Route::get('requests/vue-list-closing', 'CitizenServiceRequestController@vueListClosing');


    Route::put('requests/request-approved/{request}', 'CitizenServiceRequestController@approved');
    Route::put('requests/request-rejected/{request}', 'CitizenServiceRequestController@rejected');
    Route::put('/requests/request-add-indicator/{id}', 'CitizenServiceRequestController@addIndicator');

    /* Ruta para tipos de solicitudes */
    Route::resource(
        'request-types',
        'CitizenServiceRequestTypeController',
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );
    Route::get('/get-request-types', 'CitizenServiceRequestTypeController@getRequestTypes');

    /* Ruta para tipo de departamentos */
    Route::resource(
        'departments',
        'CitizenServiceDepartmentController',
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );
    Route::get('/get-departments', 'CitizenServiceDepartmentController@getDepartments');

    /* Rutas para subir archivos*/
    Route::resource(
        'request-close',
        'CitizenServiceRequestCloseController',
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    Route::post('requests/validate-document', 'CitizenServiceRequestCloseController@store');
    Route::get('/get-documents/show/{code}', 'CitizenServiceRequestCloseController@show');
    Route::get('/get-documents/{id}/{all?}', 'CitizenServiceRequestCloseController@getCitizenServiceRequestDocuments');

    /* Rutas para generar reporte */
    Route::get('reports', 'CitizenServiceReportController@index')->name('citizenservice.report.index');

    Route::post('reports', 'CitizenServiceReportController@store');


    Route::get('reports/request', 'CitizenServiceReportController@request')
        ->name('citizenservice.report.request');
    Route::post('reports/request/create', 'CitizenServiceReportController@create');
    Route::get('report/show/{code}', 'CitizenServiceReportController@show');
    Route::get('reports/search', 'CitizenServiceReportController@search');

    /* Rutas para generar registro de actividades */

    Route::resource('registers', 'CitizenServiceRegisterController', ['as' => 'citizenservice', 'only' => ['update']]);

    Route::get('/registers/create', 'CitizenServiceRegisterController@create')->name('citizenservice.register.create');

    Route::get('register', 'CitizenServiceRegisterController@index')->name('citizenservice.register.index');

    Route::post('registers', 'CitizenServiceRegisterController@store');

    Route::get(
        '/registers/edit/{register}',
        'CitizenServiceRegisterController@edit'
    )->name('citizenservice.register.edit');

    Route::delete(
        '/registers/delete/{register}',
        'CitizenServiceRegisterController@destroy'
    )->name('citizenservice.register.delete');

    Route::get('registers/vue-list', 'CitizenServiceRegisterController@vueList');

    Route::get('registers/vue-info/{register}', 'CitizenServiceRegisterController@vueInfo');

    /* Ruta para los tipos de impacto */
    Route::resource(
        'effect-types',
        'CitizenServiceEffectTypeController',
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    /* Ruta para obtener el listado de los tipos de impacto*/
    Route::get('get-effect-types', 'CitizenServiceEffectTypeController@getEffectType')->name('citizenservice.effect-types.get');

    /* Ruta para los indicadores */
    Route::resource(
        'indicators',
        'CitizenServiceIndicatorController',
        ['as' => 'citizenservice', 'except' => ['create','edit','show']]
    );

    Route::get('/get-indicators', 'CitizenServiceIndicatorController@getIndicators');

    Route::get('/get-request-codes', 'CitizenServiceRequestController@getRequestCodes');
    Route::delete('/registers/delete/{id}', 'CitizenServiceRequestController@destroy')->name('citizenservice.registers.delete');
});
