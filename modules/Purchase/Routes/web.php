<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Modules\Purchase\Http\Controllers\PurchaseGeneralConditionController;
use Modules\Purchase\Http\Controllers\Reports\DirectHire\PurchaseOrderController;

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
    'prefix' => 'purchase',
], function () {
    /*
     | -----------------------------------------------------------------------
     | Ruta para el panel de control del módulo de compras
     | -----------------------------------------------------------------------
     |
     | Muestra información del módulo de compras
     */
    Route::get('/', 'PurchaseController@index')->name('purchase.index');

    /* Ruta que descargar archivos. */
    Route::get('document/download/{file_name}', function ($fileName) {
        $path = storage_path('documents/' . $fileName);
        if (!File::exists($path)) {
            abort(404);
        }
        $file = File::get($path);
        /* Tipo de archivo */
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    });

    /*
     | -----------------------------------------------------------------------
     | Rutas para la configuración general del módulo de compras
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de configuración del módulo de compras
     */
    Route::group(['middleware' => 'permission:purchase.setting.create'], function () {

        Route::get('get-fiscal-year', 'PurchaseController@getFiscalYear');

        Route::get('get-institutions', 'PurchaseController@getInstitutions');

        /* Ruta de acceso a parámetros de configuración del módulo */
        Route::get('settings', 'PurchaseSettingController@index')->name('purchase.settings.index');
        Route::post('settings', 'PurchaseSettingController@store')->name('purchase.settings.store');
        /* Rutas para la gestión de objetos de proveedores */
        Route::resource(
            'supplier-objects',
            'PurchaseSupplierObjectController',
            ['as' => 'purchase']
        );
        /* Rutas para la gestión de ramas de proveedores */
        Route::resource(
            'supplier-branches',
            'PurchaseSupplierBranchController',
            ['as' => 'purchase']
        );
        /* Rutas para la gestión de especialidades de proveedores */
        Route::resource(
            'supplier-specialties',
            'PurchaseSupplierSpecialtyController',
            ['as' => 'purchase']
        );
        /* Rutas para la gestión de tipos de proveedores */
        Route::resource('supplier-types', 'PurchaseSupplierTypeController', ['as' => 'purchase']);

        /* Rutas para la gestión de migración de datos ramas y especialidad a la tabla pivote */
        /*Route::get('supplier-data-migrate-pivote', 'PurchaseSupplierController@DataMigratePivote');*/

        /* Rutas para la gestión de procesos de compras */
        Route::resource('processes', 'PurchaseProcessController', ['as' => 'purchase']);
        Route::get('get-processes', 'PurchaseProcessController@getProcesses');
        Route::post('get-process-documents', 'PurchaseProcessController@getProcessDocuments');

        /* Rutas para la gestión de servicios */
        Route::resource('services', 'PurchaseServiceController', ['as' => 'purchase']);

        /* Rutas para la gestión de productos e insumos */
        Route::resource('products', 'PurchaseProductController', ['as' => 'purchase', 'except' => 'show']);
        Route::get('products/export', 'PurchaseProductController@export');
        Route::get('get-products', 'PurchaseProductController@getProducts');
        Route::post('products/import', 'PurchaseProductController@import');
    });

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de tipos de compras
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los tipos de compras
     */
    Route::resource('purchase_types', 'PurchaseTypeController', [
        'as'     => 'purchase',
    ]);
    Route::get('get-purchase-type', 'PurchaseTypeController@getPurchaseType');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de tipos de contratación
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los tipos de contratación
     */
    Route::resource('type_hiring', 'PurchaseTypeHiringController', [
        'as'     => 'purchase',
    ]);

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de tipos de operaciones
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los tipos de operaciones
     */
    Route::resource('type_operations', 'PurchaseTypeOperationController', [
        'as'     => 'purchase',
    ]);
    Route::get('get-type-operations', 'PurchaseTypeOperationController@getRecords');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de proveedores
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los proveedores
     */
    Route::get(
        'suppliers/vue-list',
        'PurchaseSupplierController@vueList'
    )->name('purchase.suppliers.vuelist');
    Route::resource('suppliers', 'PurchaseSupplierController', ['as' => 'purchase',]);
    Route::get('suppliers-list', 'PurchaseSupplierController@showall');
    Route::get('suppliers/{id}', 'PurchaseSupplierController@show');


    Route::get(
        'get-purchase-supplier-object/{id}',
        'PurchaseSupplierObjectController@getPurchaseSupplierObject'
    );

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de requerimientos
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los requerimientos de compras
     */
    Route::post('requirements', 'PurchaseRequirementController@store');

    Route::get('requirements/pdf/{id}', 'Reports\PurchaseRequirementController@pdf');

    Route::get(
        'requirements/vue-list',
        'PurchaseRequirementController@vueList'
    )->name('purchase.requirements.vuelist');

    Route::resource('requirements', 'PurchaseRequirementController', [
        'as'     => 'purchase',
    ]);

    Route::post('base_budget', 'PurchaseBaseBudgetController@store');
    Route::post('send_notify', 'PurchaseBaseBudgetController@sendNotify');
    Route::get(
        'base_budget/vue-list',
        'PurchaseBaseBudgetController@vueList'
    )->name('purchase.base-budget.vuelist');
    Route::resource('base_budget', 'PurchaseBaseBudgetController', [
        'as'     => 'purchase',
    ]);
    Route::get('base-budget/pdf/{id}', 'Reports\PurchaseBaseBudgetController@generatePdf');
    Route::get('requirement-items', 'PurchaseRequirementController@getRequirementItems');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de planes de compras
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los plan de compras
     */
    Route::resource('purchase_plans', 'PurchasePlanController', [
        'as'     => 'purchase',
    ]);
    Route::post('purchase_plans/start_diagnosis', 'PurchasePlanController@uploadFile');

    Route::get('purchase_plans/download/{code}', 'PurchasePlanController@getDownload');
    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de ordenes de compras
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de ordenes de compras
     */
    Route::resource('purchase_order', 'PurchaseOrderController', [
        'as'     => 'purchase',
    ]);
    Route::post('purchase_order/{id}', 'PurchaseOrderController@updatePurchaseOrder');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de ordenes de compras
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de ordenes de compras
     */
    Route::get('general-conditions', [PurchaseGeneralConditionController::class, 'index']);
    Route::post('general-conditions', [PurchaseGeneralConditionController::class, 'store']);

    Route::get(
        'direct_hire/vue-list',
        'PurchaseDirectHireController@vueList'
    );

    Route::get(
        'direct_hire/show-direct-hire-currency/{code}',
        'PurchaseDirectHireController@showDirectHireCurrency'
    );

    /* Rutas para los pdf de orden de compra */
    Route::get('direct_hire/start_certificate/pdf/{id}', 'Reports\DirectHire\PurchaseStartCertificateController@pdf');

    Route::get('direct_hire/purchase_order/pdf/{id}', [PurchaseOrderController::class, 'pdf'])->name('purchase.direct_hire.purchase_order.pdf');

    Route::resource('direct_hire', 'PurchaseDirectHireController', [
        'as'     => 'purchase',
    ]);
    Route::post('direct_hire/{id}', 'PurchaseDirectHireController@updatePurchaseOrder');

    Route::post('change-direct-hire-status', 'PurchaseDirectHireController@changeStatus');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de cotizaciones
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de ordenes de compras
     */
    Route::resource('quotation', 'PurchaseQuotationController', [
        'as'     => 'purchase',
    ]);
    Route::get('quotations/vue-list', 'PurchaseQuotationController@vueList');

    Route::post('quotation/{id}', 'PurchaseQuotationController@updatePurchaseQuotation');

    Route::get('get-convertion/{currency_id}/{base_budget_currency_id}', 'PurchaseOrderController@getConvertion');

    /* Ruta para generar  el reporte de cotización */
    Route::get('quotation/pdf/{id}', 'Reports\PurchaseQuotationController@pdf');

    Route::post('change-quotation-status', 'PurchaseQuotationController@changeQuotationStatus');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de Disponibilidad presupuestaria
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los tipos de operaciones
     */
    Route::resource('budgetary_availability', 'PurchaseBudgetaryAvailabilityController', [
        'as'     => 'purchase',
    ]);
    Route::post('budgetary_availability/approve', 'PurchaseBudgetaryAvailabilityController@approveBudgetaryAvailability')
    ->name('purchase.budgetary_availability.approve');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la consulta de producto del modulo Warehouse con la informacion de
     | -----------------------------------------------------------------------
     |
     | Gestiona los datos de los tipos de operaciones
     */
    Route::get('get-warehouse-products', 'PurchaseRequirementController@getWarehouseProducts');

    /*
     | -----------------------------------------------------------------------
     | Rutas para la gestión de los parámetros de configuración de Compras.
     | -----------------------------------------------------------------------
     */
    /* Ruta que gestiona los datos de la Configuración de parámetros de Compras */
    Route::post(
        'update-parameters',
        'PurchaseParameterController@updateParameters'
    )->name('purchase.parameters.update-parameters');

    /* Ruta que devuelve los parámetros de configuración de Compras: Número de decimales a mostrar y Redondeo */
    Route::get(
        'get-parameters',
        'PurchaseParameterController@index'
    )->name('purchase.parameters.index');
});
