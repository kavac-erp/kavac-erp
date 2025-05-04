<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\Reports\FinanceReportsController;
use Modules\Finance\Http\Controllers\FinanceController;
use Modules\Finance\Http\Controllers\FinanceBankController;
use Modules\Finance\Http\Controllers\FinancePayOrderController;
use Modules\Finance\Http\Controllers\FinanceCheckBookController;
use Modules\Finance\Http\Controllers\FinanceMovementsController;
use Modules\Finance\Http\Controllers\FinanceAccountTypeController;
use Modules\Finance\Http\Controllers\FinanceBankAccountController;
use Modules\Finance\Http\Controllers\FinanceConciliationController;
use Modules\Finance\Http\Controllers\FinanceBankingAgencyController;
use Modules\Finance\Http\Controllers\FinancePaymentExecuteController;
use Modules\Finance\Http\Controllers\FinancePaymentMethodsController;
use Modules\Finance\Http\Controllers\FinanceSettingBankReconciliationFilesController;

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
    'prefix' => 'finance'
], function () {
    Route::get('/', [FinanceController::class, 'index'])->name('finance.index');

    Route::group(['middleware' => 'permission:finance.setting.create'], function () {
        /* Ruta de acceso a parámetros de configuración del módulo */
        Route::get('settings', [FinanceController::class, 'setting'])->name('finance.setting.index');
        Route::post('settings', [FinanceController::class, 'store'])->name('finance.setting.store');
        /* Rutas para la gestión de entidades bancarias */
        Route::resource('banks', FinanceBankController::class, ['as' => 'finance']);
        /* Rutas para la gestión de agencias bancarias */
        Route::resource('banking-agencies', FinanceBankingAgencyController::class, ['as' => 'finance']);
        /* Rutas para la gestión de tipos de cuentas bancarias */
        Route::resource('account-types', FinanceAccountTypeController::class, ['as' => 'finance']);
        /* Rutas para la gestión de cuentas bancarias */
        Route::resource('bank-accounts', FinanceBankAccountController::class, ['as' => 'finance']);
        /* Rutas para la gestión de chequeras */
        Route::resource('check-books', FinanceCheckBookController::class, ['as' => 'finance', 'except' => ['edit']]);

        Route::get('get-bank-account/{bank_id}', [FinanceCheckBookController::class, 'getBanksAccounts']);

        Route::get('check-books/edit/{id}', [FinanceCheckBookController::class, 'edit'])->name('finance.edit');
        /* Rutas para la gestión de los métodos de pago */
        Route::resource('payment_methods', FinancePaymentMethodsController::class, ['as' => 'finance']);
        /* Rutas para la gestión de la configuración de archivos de conciliación bancaria */
        Route::resource('setting-bank-reconciliation-files', FinanceSettingBankReconciliationFilesController::class, ['as' => 'finance']);
    });

    Route::post('deductions-to-pay', [FinanceController::class, 'getDeductionsToPay'])->name('finance.setting.get-deductions-to-pay');

    /* Ruta para la gestión de Finanzas > Banco > Ordenes de pago */
    Route::get(
        'pay-orders/list/get-receivers',
        [FinancePayOrderController::class, 'getPayOrderReceivers']
    );
    Route::get('pay-orders/pending/{receiver_id?}/{currency_id?}/{is_update?}', [FinancePayOrderController::class, 'getPendingPayOrders'])
        ->name('finance.pay-order.pending');
    Route::get('pay-orders/vue-list', [FinancePayOrderController::class, 'vueList'])->name('finance.pay-order.vuelist');
    Route::post('pay-orders/change-document-status', [FinancePayOrderController::class, 'changeDocumentStatus'])
        ->name('finance.pay-order.change-document-status');
    Route::get('pay-orders/pdf/{financePayOrder}', [FinancePayOrderController::class, 'pdf']);
    Route::post('pay-orders/cancel', [FinancePayOrderController::class, 'cancelPayOrder'])
        ->name('finance.pay-order.cancel');
    Route::resource('pay-orders', FinancePayOrderController::class, ['as' => 'finance']);
    Route::post(
        'pay-orders/documents/get-sources',
        [FinancePayOrderController::class, 'getSourceDocuments']
    );

    /* Ruta para la gestión de Finanzas > Banco > Emisiones de pago */
    Route::get(
        'payment-execute/list/get-receivers',
        [FinancePaymentExecuteController::class, 'getPayOrderReceivers']
    );
    Route::get('payment-execute/vue-list', [FinancePaymentExecuteController::class, 'vueList'])
        ->name('finance.payment-execute.vuelist');
    Route::get('payment-execute/pdf/{financePaymentExecute}', [FinancePaymentExecuteController::class, 'pdf']);
    Route::post('payment-execute/iva/pdf/{financePaymentExecute}', [FinancePaymentExecuteController::class, 'pdfIvaRegister']);
    Route::get('payment-execute/iva/pdf/{financePaymentExecute}', [FinancePaymentExecuteController::class, 'pdfIva']);
    Route::post('payment-execute/change-document-status', [FinancePaymentExecuteController::class, 'changeDocumentStatus'])
        ->name('finance.payment-execute.change-document-status');
    Route::post('payment-execute/cancel', [FinancePaymentExecuteController::class, 'cancelPaymentExecute'])
        ->name('finance.payment-execute.cancel');
    Route::resource('payment-execute', FinancePaymentExecuteController::class, ['as' => 'finance']);
    Route::get('payment-execute/bank/get-bank-accounting-account-id', [FinancePaymentExecuteController::class, 'getBankAccountingAccountId']);

    /* Ruta para la gestión de Finanzas > Banco > Movimientos */
    Route::get('movements/vue-list', [FinanceMovementsController::class, 'vueList']);
    Route::get('movements/vue-list-by-account/{institution_id}/{currency_id}/{account_id}/{startDate}/{endDate}', [FinanceMovementsController::class, 'vueListByAccount']);
    Route::get('movements/vue-info/{id}', [FinanceMovementsController::class, 'vueInfo']);
    Route::get('movements/edit/{id}', [FinanceMovementsController::class, 'edit']);
    Route::post('movements/change-document-status', [FinanceMovementsController::class, 'changeDocumentStatus']);
    Route::post('movements/cancel-movements', [FinanceMovementsController::class, 'cancelMovements']);
    Route::get('movements/budget-accounting-accounts/{budget_account_id}', [FinanceMovementsController::class, 'getBudgetAccountingAccount']);
    Route::resource('movements', FinanceMovementsController::class, ['as' => 'finance']);

    /* Ruta para la gestión de Finanzas > Banco > Conciliación */
    Route::get('conciliation/movements/vue-list-by-account/{institution_id}/{currency_id}/{account_id}/{startDate}/{endDate}', [FinanceConciliationController::class, 'vueListMovementsByAccount']);
    Route::get('conciliation/vueList', [FinanceConciliationController::class, 'vueList']);
    Route::get('conciliation/pdf/{id}', [FinanceConciliationController::class, 'pdf']);
    Route::post('conciliation/approve/{id}', [FinanceConciliationController::class, 'approve']);
    Route::resource('conciliation', FinanceConciliationController::class, ['as' => 'finance']);
    Route::get('get-institution', [FinanceConciliationController::class, 'getInstitution']);

    Route::get('get-banks/', [FinanceBankController::class, 'getBanks']);
    Route::get('get-bank-info/{bank_id}', [FinanceBankController::class, 'getBankInfo']);
    Route::get('get-agencies/{bank_id?}', [FinanceBankingAgencyController::class, 'getAgencies']);
    Route::get('get-account-types', [FinanceAccountTypeController::class, 'getAccountTypes']);
    Route::get('get-accounts/{bank_id}', [FinanceBankAccountController::class, 'getBankAccounts']);
    Route::get('get-bank-accounts', [FinanceBankAccountController::class, 'getFinanceBankAccount']);
    Route::get('get-payment-methods', [FinancePaymentMethodsController::class, 'getPaymentMethods']);
    Route::post('get-bank-account-conciliation', [FinanceConciliationController::class, 'getBankAccountConciliationInfo']);
    Route::get('voucher-design', function () {
        return view('finance::vouchers.design');
    })->name('finance.voucher.design');

    /* Rutas para generar reportes de finanzas */
    Route::get('payment-reports', [FinanceReportsController::class, 'create'])->name('finance.payment-reports.create');
    Route::get('payment-reports/get-document-status-list', [FinanceReportsController::class, 'getDocumentStatusList']);
    Route::post('payment-reports/payment-execute/pdf', [FinanceReportsController::class, 'pdfPaymentExecutes']);
    Route::post('payment-reports/pay-order/pdf', [FinanceReportsController::class, 'pdfPayOrders']);
    Route::post('payment-reports/banking-movements/pdf', [FinanceReportsController::class, 'pdfBankingMovements']);
});
