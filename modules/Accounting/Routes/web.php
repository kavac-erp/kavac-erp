<?php

/**
 * -----------------------------------------------------------------------
 * Grupo de rutas para la gestión de cuentas patrimoniales, conversiones, asientos contables y reportes
 * -----------------------------------------------------------------------
 *
 * Permite gestionar las cuentas patrimoniales, asientos contables y generar reportes
 *
 */

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth', 'verified'], 'prefix' => 'accounting'], function () {

    /* Rutas temporal para la visualizacion del dashboard del modulo de contabilidad */
    Route::get('', 'AccountingDashboardController@index')->name('accounting.dashboard.test');

    /* Ruta del dashboard para consultar ultimas operaciones en el modulo */
    Route::post('lastOperations', 'AccountingDashboardController@getOperations')
    ->name('accounting.dashboard.last_operations');

    Route::post('get_report_histories', 'AccountingDashboardController@getReportHistories')
    ->name('accounting.dashboard.report_histories');

    /* Ruta que obtiene las cuentas contables */
    Route::get('get_accounts', 'AccountingAccountController@getAccounts')
    ->name('accounting.get_accounts');

    Route::get('get_original_accounts', 'AccountingEntryController@getGroupAccountingAccount')
    ->name('accounting.get_original_accounts');

    /* Rutas para la gestion de cuentas patrimoniales */
    Route::get('accounts', 'AccountingAccountController@index')
            ->name('accounting.accounts.index');

    Route::get('get-children-account/{parent_id}', 'AccountingAccountController@getChildrenAccount')
            ->name('accounting.accounts.getChildrenAccount');

    /* Ruta que permite importar la información de las cuentas patrimoniales */
    Route::post('import', 'AccountingAccountController@import')
        ->name('accounting.accounts.import');

    /* Ruta que permite exportar la información de las cuentas patrimoniales */
    Route::get('export/all', 'AccountingAccountController@export')
        ->name('accounting.accounts.export.all');

    Route::resource(
        'accounts',
        'AccountingAccountController',
        ['as' => 'accounting',
        'except' => ['index']]
    );

    /* Rutas para las operaciones de conversión de cuentas */
    Route::get('converter', 'AccountingAccountConverterController@index')
            ->name('accounting.converter.index');

    // retorna cuenta patrimonial asociada
    Route::get('converter/budgetToAccount/{id}', 'AccountingAccountConverterController@budgetToAccount')
            ->name('accounting.converter.budgetToAccount');

    // retorna cuenta presupuestaria asociada
    Route::get('converter/accountToBudget/{id}', 'AccountingAccountConverterController@accountToBudget')
            ->name('accounting.converter.accountToBudget');

    Route::post('converter/create', 'AccountingAccountConverterController@create')
            ->name('accounting.converter.create');

    Route::post('converter/get-Records', 'AccountingAccountConverterController@getRecords')
            ->name('accounting.converter.getRecords');

    Route::post(
        'converter/getAllRecordsAccounting_vuejs',
        'AccountingAccountConverterController@getAllRecordsAccountingVuejs'
    )->name('accounting.converter.getAllRecordsAccounting_vuejs');

    Route::post(
        'converter/getAllRecordsBudget_vuejs',
        'AccountingAccountConverterController@getAllRecordsBudgetVuejs'
    )->name('accounting.converter.getAllRecordsBudget_vuejs');



    Route::resource(
        'converter',
        'AccountingAccountConverterController',
        ['as' => 'converter',
        'except' => ['index']]
    );

    /* aprobar un asiento contable */
    Route::post('entries/approve/{id}', 'AccountingEntryController@approve')
            ->name('accounting.entries.approve');

    /* genera reverso de asiento contable */
    Route::post('entries/reverse', 'AccountingEntryController@reverse')
            ->name('accounting.entries.reverse');

    /* Convierte registros relacionados a cuentas patrimoniales en asientos contables */
    Route::post('entries/converterToEntry', 'AccountingEntryController@converterToEntry')
            ->name('accounting.entries.converterToEntry');

    /* Rutas para la gestión de asientos contables */
    Route::resource(
        'entries',
        'AccountingEntryController',
        ['as' => 'entries']
    );
    Route::get('entries', 'AccountingEntryController@index')
            ->name('accounting.entries.index');

    /* Ruta para crear asientos contables */
    Route::post('entries/create', 'AccountingEntryController@create')
            ->name('accounting.entries.create');

    /* Ruta para el filtrado o busqueda de asientos contables aprobados */
    Route::post('entries/Filter-Records/{perPage?}/{page?}', 'AccountingEntryController@filterRecords')
            ->name('accounting.entries.FilterRecords');

    /* Rutas para los pdf de asientos contables */
    Route::get('entries/pdf/{id}', 'Reports\AccountingEntryController@pdf');

    /* Rutas index para los formularios de los reportes */
    Route::get('report/accountingBooks', 'Reports\AccountingReportsController@accountingBooks')
        ->name('accounting.report.accountingBooks');

    Route::get('report/financeStatements', 'Reports\AccountingReportsController@financeStatements')
        ->name('accounting.report.financeStatements');

    /* Rutas para reporte de balance de comprobación */
    Route::get(
        'report/balanceCheckUp/{report}',
        'Reports\AccountingCheckupBalanceController@pdf'
    );

    /* Rutas para reporte de balance de comprobación */
    Route::get(
        'report/balanceCheckUp/sheet/{report}',
        'Reports\AccountingCheckupBalanceController@export'
    );

    Route::get(
        'report/balanceCheckUp/pdfVue/{initDate}/{endDate}/{currency}/{all?}',
        'Reports\AccountingCheckupBalanceController@pdfVue'
    );

    /* Rutas para reporte de balance de comprobación con firma electrónica */
    Route::get(
        'report/balanceCheckUpSign/{report}',
        'Reports\AccountingCheckupBalanceController@pdfSign'
    );

    Route::get(
        'report/balanceCheckUpSign/pdfVue/{initDate}/{endDate}/{currency}/{all?}',
        'Reports\AccountingCheckupBalanceController@pdfVueSign'
    );

    /* Rutas para reporte del Mayor Analítico */
    Route::post(
        'report/analyticalMajor/AccAccount',
        'Reports\AccountingAnalyticalMajorController@getAccAccount'
    );

    Route::get(
        'report/analyticalMajor/{report}',
        'Reports\AccountingAnalyticalMajorController@pdf'
    );

    Route::get(
        'report/analyticalMajor/sheet/{report}',
        'Reports\AccountingAnalyticalMajorController@export'
    );

    Route::get(
        'report/analyticalMajor/pdfVue/{initDate}/{endDate}/{initAcc}/{endAcc}/{currency}',
        'Reports\AccountingAnalyticalMajorController@pdfVue'
    );

    /* Rutas para reporte del Mayor Analítico con firma electrónica */
    Route::get(
        'report/analyticalMajorSign/{report}',
        'Reports\AccountingAnalyticalMajorController@pdfSign'
    );

    Route::get(
        'report/analyticalMajorSign/pdfVue/{initDate}/{endDate}/{initAcc}/{endAcc}/{currency}',
        'Reports\AccountingAnalyticalMajorController@pdfVueSign'
    );

    /* Rutas para reporte del libro diario */
    Route::get(
        'report/dailyBook/{report}',
        'Reports\AccountingDailyBookController@pdf'
    );

    Route::get(
        'report/dailyBook/sheet/{report}',
        'Reports\AccountingDailyBookController@export'
    );

    Route::get(
        'report/dailyBook/pdfVue/{initDate}/{endDate}/{currency}',
        'Reports\AccountingDailyBookController@pdfVue'
    );

    /* Rutas para reporte del libro diario firmado electrónicamente */
    Route::get(
        'report/dailyBookSign/{report}',
        'Reports\AccountingDailyBookController@pdfSign'
    );

    Route::get(
        'report/dailyBookSign/pdfVue/{initDate}/{endDate}/{currency}',
        'Reports\AccountingDailyBookController@pdfVueSign'
    );

    /* Rutas para reporte de libro auxiliar */
    Route::get(
        'report/auxiliaryBook/{report}',
        'Reports\AccountingAuxiliaryBookController@pdf'
    );

    Route::get(
        'report/auxiliaryBook/pdfVue/{date}/{currency}/{account_id?}',
        'Reports\AccountingAuxiliaryBookController@pdfVue'
    );

    /* Rutas para reporte de libro auxiliar con firma electrónica */
    Route::get(
        'report/auxiliaryBookSign/{report}',
        'Reports\AccountingAuxiliaryBookController@pdfSign'
    );

    Route::get(
        'report/auxiliaryBookSign/pdfVue/{date}/{currency}/{account_id?}',
        'Reports\AccountingAuxiliaryBookController@pdfVueSign'
    );

    /* Rutas para reporte de balance general */
    Route::get(
        'report/BalanceSheet/{report}',
        'Reports\AccountingBalanceSheetController@pdf'
    );

    /* Rutas para reporte de balance general */
    Route::get(
        'report/BalanceSheet/sheet/{report}',
        'Reports\AccountingBalanceSheetController@export'
    );

    Route::get(
        'report/BalanceSheet/pdfVue/{date}/{level}/{currency}/{zero?}',
        'Reports\AccountingBalanceSheetController@pdfVue'
    );

    /* Rutas para reporte de balance general con firma electrónica */
    Route::get(
        'report/BalanceSheetSign/{report}',
        'Reports\AccountingBalanceSheetController@pdfSign'
    );

    Route::get(
        'report/BalanceSheetSign/pdfVue/{date}/{level}/{currency}/{zero?}',
        'Reports\AccountingBalanceSheetController@pdfVueSign'
    );

    /* Rutas para reporte de estado de resultados */
    Route::get(
        'report/StateOfResults/{report}',
        'Reports\AccountingStateOfResultsController@pdf'
    );

    /* Rutas para reporte de hoja de calculo de estado de resultados */
    Route::get(
        'report/StateOfResults/sheet/{report}',
        'Reports\AccountingStateOfResultsController@export'
    );

    Route::get(
        'report/StateOfResults/pdfVue/{date}/{level}/{currency}/{zero?}',
        'Reports\AccountingStateOfResultsController@pdfVue'
    );

    /* Rutas para reporte de estado de resultados con firma electrónica */
    Route::get(
        'report/StateOfResultsSign/{report}',
        'Reports\AccountingStateOfResultsController@pdfSign'
    );

    Route::get(
        'report/StateOfResultsSign/pdfVue/{date}/{level}/{currency}/{zero?}',
        'Reports\AccountingStateOfResultsController@pdfVueSign'
    );

    /* Rutas para reporte de estado de movimiento del patrimonio */
    Route::get(
        'report/PatrimonialMovement/{report}',
        'Reports\AccountingPatrimonialMovementController@pdf'
    );

    Route::get(
        'report/PatrimonialMovement/pdfVue/{date}/{level}/{currency}/{zero?}',
        'Reports\AccountingPatrimonialMovementController@pdfVue'
    );

    /* Rutas para reporte de estado de movimiento del patrimonio con firma electrónica */
    Route::get(
        'report/PatrimonialMovementSign/{report}',
        'Reports\AccountingPatrimonialMovementController@pdfSign'
    );

    Route::get(
        'report/PatrimonialMovementSign/pdfVue/{date}/{level}/{currency}/{zero?}',
        'Reports\AccountingPatrimonialMovementController@pdfVueSign'
    );

    /* Rutas para las vistas de configuración de categorias del modulo de contabilidad */
    Route::get('settings', 'AccountingSettingController@index')
            ->name('accounting.settings.index');

    Route::post('settings/code', 'AccountingSettingController@codeStore')
            ->name('accounting.settings.code.store');

    Route::post(
        'settings/generateReferenceCode',
        'AccountingSettingController@generateReferenceCode'
    )->name('accounting.settings.code.generate');

    Route::post(
        'update-institution-parameters',
        'AccountingSettingController@updateInstitutionParameters'
    )->name('accounting.parameters.update-institution-parameters');


    Route::resource(
        'settings/categories',
        'AccountingSettingCategoryController',
        ['as' => 'accounting']
    );

    Route::get('get-categories/', 'AccountingSettingCategoryController@getCategories');

     /* Ruta para visualizar el interfaz para firmar documento PDF */
    Route::get('viewSignfile', function () {
        return view('accounting::viewSignfile', ['signfile' => 'false']);
    })->name('viewSignfile');
});
