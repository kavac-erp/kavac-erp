<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\PayrollReportController;

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
    'prefix' => 'payroll'
], function () {

    /* Ruta para gestionar la importación de carga masiva de vacaciones */
    Route::post('vacations-request/import', 'PayrollVacationRequestController@import')->name('payroll.vacations-request.import');

    /* Ruta para gestionar la exportación de la planilla de vacaciones */
    Route::get('vacations-request/export', 'PayrollVacationRequestController@export')->name('payroll.vacations-request.export');

    /* Ruta para gestionar la exportación de la planilla ARI */
    Route::get('ari-register/export', 'PayrollAriRegisterController@export')->name('payroll.ari-register.export');

    /* Ruta para gestionar la importación de la planilla ARI */
    Route::post('ari-register/import', 'PayrollAriRegisterController@import')->name('payroll.ari-register.import');



    /* Ruta para gestionar la eliminación de la planilla ARI */
    Route::delete('delete-ari-register/{id}', 'PayrollAriRegisterController@destroy')->name('payroll.ari-register.destroy');

    /* Ruta para gestionar la edición de la planilla ARI */
    Route::get('edit-ari-register/{id}', 'PayrollAriRegisterController@edit')->name('payroll.ari-register.edit');

    /* Ruta para obtener todos los registros de la planilla ARI */
    Route::get('get-ari-registers', 'PayrollAriRegisterController@getAriRegisters');

    /* Ruta para gestionar el guardado de la planilla ARI */
    Route::post('ari-register-save', 'PayrollAriRegisterController@store')->name('payroll.ari-register.store');

    /* Ruta para mostrar el formulario que permite el registro de la planilla ARI */
    Route::get('ari-register-create', 'PayrollAriRegisterController@create')->name('payroll.ari-register.create');

    /* Ruta para mostrar la vista de registro de informacion de la planilla ARI */
    Route::get('ari-register', 'PayrollAriRegisterController@index')->name('payroll.ari-register.index');

    /* Ruta para gestionar el envio de los recibos de pago */
    Route::post('send-payroll-payment-type-receipt/{id}', 'SendPayrollPaymentTypeReceiptsController@sendReceipts');

    /* Ruta para obtener los tipos de pagos */
    Route::get('get-payroll-payment-types', 'PayrollTextFileController@getPayrollPaymentTypes');

    /* Ruta para obtener las cuentas bancarias */
    Route::get('get-bank-accounts', 'PayrollTextFileController@getBankAccounts');

    /* Ruta para obtener la lista de nominas */
    Route::get('get-payroll-list', 'PayrollTextFileController@getPayrollList');

    /* Ruta para mostrar la vista Archivo txt de nómina */
    Route::get('payroll-text-file', 'PayrollTextFileController@create')->name('payroll.text.file.create');

    /* Ruta para validar los datos del formulario del archivo txt de nómina */
    Route::post('validate-txt-data', 'PayrollTextFileController@validateTxtData');

    /* Ruta para mostrar todos los archivos txt de nómina */
    Route::get('get-text-file-records', 'PayrollTextFileController@getTextFileRecords');

    /* Ruta para eliminar un archivo txt de nómina */
    Route::delete('delete-text-file-record/{id}', 'PayrollTextFileController@deleteTextFileRecord');

    /* Ruta para mostrar la vista de edición de un archivo txt de nómina */
    Route::get('edit-text-file-record/{id}', 'PayrollTextFileController@edit');

    /* Ruta para editar un archivo txt de nómina */
    Route::get('get-edit-text-file-record/{id}', 'PayrollTextFileController@editTextFileRecord');

    /* Ruta para generar un archivo txt de nómina */
    Route::get('generate-txt', 'PayrollTextFileController@downloadFile');

    /* Ruta que muestra la vista del archivo txt de nómina */
    Route::get('file', 'PayrollTextFileController@index')->name('payroll.text-file.index');

    Route::get('get-budget-accounting-report/{id}', 'PayrollTextFileController@getBudgetAccountingReport');

    /* Ruta para manejar manipular la informacion de la planilla ARC */
    Route::get('/arc/registers', 'PayrollArcController@getArcRegisters');
    Route::get('/arc/export', 'PayrollArcController@export');
    Route::post('/arc/send', 'PayrollArcController@send');
    Route::resource(
        'arc',
        'PayrollArcController',
        ['as' => 'payroll', 'only' => ['index']]
    );
    /* Ruta para manejar manipular la informacion de los responsables de ARC */
    Route::resource(
        'arc-responsible',
        'PayrollArcResponsibleController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    )->parameters([
        'arc-responsible' => 'payroll-arc-responsible',
    ]);
    ;

    /* Ruta para manejar manipular la informacion de los niveles de escolaridad */
    Route::resource(
        'schooling-levels',
        'PayrollSchoolingLevelController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );
    /* Rutas para gestionar el registro masivo del personal */
    Route::post('registers/import/staffs/all', 'PayrollStaffController@import');
    Route::post('registers/import/professional/all', 'PayrollProfessionalController@import');
    Route::post('registers/import/socioeconomics/all', 'PayrollSocioeconomicController@import');
    Route::post('registers/import/employments/all', 'PayrollEmploymentController@import');
    Route::post('registers/import/financial/all', 'PayrollFinancialController@import');
    Route::post('registers/import/staff-accounts/all', 'PayrollStaffAccountController@import');
    /* Rutas para gestionar el Exportar registro masivo del personal */
    Route::get('registers/export/staffs/all', 'PayrollStaffController@export');
    Route::get('registers/export/professional/all', 'PayrollProfessionalController@export');
    Route::get('registers/export/socioeconomics/all', 'PayrollSocioeconomicController@export');
    Route::get('registers/export/employments/all', 'PayrollEmploymentController@export');
    Route::get('registers/export/financial/all', 'PayrollFinancialController@export');
    Route::get('registers/export/staff-accounts/all', 'PayrollStaffAccountController@export');
    /* Ruta para obtener la lista de los niveles de escolaridad */
    Route::get(
        'get-schooling-levels',
        [Modules\Payroll\Http\Controllers\PayrollSchoolingLevelController::class, 'getPayrollSchoolingLevels']
    )->name('payroll.get-payroll-schooling-levels');


    /* Rutas para gestionar la nómina de trabajadores de la institución */
    Route::resource('registers', 'PayrollController', ['as' => 'payroll', 'except' => ['edit', 'show']]);

    /* Ruta que permite editar la información de un registro de nómina */
    Route::get('registers/edit/{register}', 'PayrollController@edit')->name('payroll.registers.edit');

    /* Ruta que permite visualizar el registro de la disponibilidad presupuestaría de un registro de nómina */
    Route::get('registers/availability/{id}', 'PayrollController@availability')->name('payroll.registers.availability.edit');

    /* Ruta que permite visualizar el registro de la disponibilidad presupuestaría de un registro de nómina */
    Route::get('registers/availability/show/{id}', 'PayrollController@availabilityShow')->name('payroll.registers.availability.show');

    /* Ruta que permite registar la disponibilidad presupuestaría de un registro de nómina */
    Route::post('registers/availability', 'PayrollController@availabilityStore')->name('payroll.registers.availability.store');

    /* Ruta que permite visualizar la información de un registro de nómina */
    Route::get('registers/show/{register}', 'PayrollController@show')->name('payroll.registers.show');

    /* Ruta que obtiene la información de un registro de nómina */
    Route::get('registers/vue-info/{register}', 'PayrollController@vueInfo')->name('payroll.registers.vue-info');

    /* Ruta que obtiene un listado de los registros de nómina */
    Route::get('registers/vue-list', 'PayrollController@vueList')->name('payroll.registers.vue-list');

    /* Ruta que actualiza el estado de un registro de nómina para evitar su edición */
    Route::patch('registers/close/{register}', 'PayrollController@close')->name('payroll.registers.close');

    /* Ruta para aprobar un registro de nómina */
    Route::put('registers/approved/{id}', 'PayrollController@approved')->name('payroll.registers.approved');

    /* Ruta que permite generar el reporte csv de los registros de nómina asociados a un pago */
    Route::get('registers/export/{id}', 'PayrollController@export');

    /* Ruta para visualizar la sección de configuración del módulo */
    Route::get('settings', 'PayrollSettingController@index')->name('payroll.settings.index');

    /* Ruta para guardar los cambios en la sección de configuración del módulo */
    Route::post('settings', 'PayrollSettingController@store')->name('payroll.settings.store');

    /* Rutas para gestionar el tipo de personal */
    Route::resource(
        'staff-types',
        'PayrollStaffTypeController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Ruta que obtiene un arreglo con los tipos de personal registrados */
    Route::get(
        'get-staff-types',
        'PayrollStaffTypeController@getPayrollStaffTypes'
    )->name('payroll.get-payroll-staff-types');

    /* Rutas para gestionar el tipo de cargo */
    Route::resource(
        'position-types',
        'PayrollPositionTypeController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Ruta que obtiene un arreglo con los tipos de cargo registrados */
    Route::get(
        'get-position-types',
        'PayrollPositionTypeController@getPayrollPositionTypes'
    )->name('payroll.get-payroll-position-types');

    /* Rutas para gestionar los cargos */
    Route::resource(
        'positions',
        'PayrollPositionController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Rutas para gestionar las Coordinaciones */
    Route::resource(
        'coordinations',
        'PayrollCoordinationController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Rutas para gestionar las Responsabilidades */
    Route::resource(
        'responsibilities',
        'PayrollResponsibilityController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Ruta que obtiene un arreglo con las coordinaciones registradas */
    Route::get(
        'get-coordinations',
        'PayrollCoordinationController@getPayrollCoordinations'
    )->name('payroll.get-payroll-coordinations');

    /* Obtiene el listado de Unidades y Dependencias */
    Route::get(
        'get-departments',
        'PayrollCoordinationController@getDepartments'
    )->name('payroll.getDepartments.list');

    /* Ruta que obtiene un arreglo con los cargos registrados */
    Route::get(
        'get-positions',
        'PayrollPositionController@getPayrollPositions'
    )->name('payroll.get-payroll-positions');

    /* intermedia entre PayrollEmployment y PayrollPosition. */
    Route::get(
        'get-employments-positions',
        'PayrollEmploymentController@getEmploymentsPositions'
    )->name('payroll.get-employments-positions');

    /* asociados a un cargo en la tabla intermedia entre cagos y empleados. */
    Route::get(
        'get-payroll-employments-positions-count',
        'PayrollPositionController@getPayrollEmploymentsPositionsCount'
    )->name('payroll.get-employments-positions-count');

    /* Rutas para gestionar la clasificación del personal */
    Route::resource(
        'staff-classifications',
        'PayrollStaffClassificationController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Ruta que obtiene un arreglo con las clasificaciones del personal registrados */
    Route::get(
        'get-staff-classifications',
        'PayrollStaffClassificationController@getPayrollStaffClassifications'
    )->name('payroll.get-payroll-staff-classifications');

    /* Rutas para gestionar el registro del personal */
    Route::resource('staffs', 'PayrollStaffController', ['as' => 'payroll']);

    /* Ruta que obtiene un listado del peronal activo */
    Route::get('staffs/show/vue-list', 'PayrollStaffController@vueList')->name('payroll.staffs.vue-list');

    /* Ruta que obtiene un arreglo con los registros del personal registrados */
    Route::get(
        'get-staffs/{type?}',
        'PayrollStaffController@getPayrollStaffs'
    )->name('payroll.get-payroll-staffs');

    /* Ruta que obtiene un arreglo con los registros del personal registrados */
    Route::get(
        'get-socioeconomic/{type?}',
        'PayrollStaffController@getPayrollSocioeconomic'
    )->name('payroll.get-payroll-socioeconomic');

    /* Ruta que obtiene un arreglo con los registros del personal registrados */
    Route::get(
        'get-professional/{type?}',
        'PayrollStaffController@getPayrollProfessional'
    )->name('payroll.get-payroll-professional');

    /* Rutas para gestionar los grados de instrucción */
    Route::resource(
        'instruction-degrees',
        'PayrollInstructionDegreeController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    /* Ruta que obtiene un arreglo con los grados de instrucción registrados */
    Route::get(
        'get-instruction-degrees',
        'PayrollInstructionDegreeController@getPayrollInstructionDegrees'
    )->name('payroll.get-payroll-instruction-degrees');

    /* Rutas para gestionar los tipos de estudios */
    Route::resource(
        'study-types',
        'PayrollStudyTypeController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );

    /* Ruta que obtiene un arreglo con los tipos de estudios registrados */
    Route::get(
        'get-study-types',
        'PayrollStudyTypeController@getPayrollStudyTypes'
    )->name('payroll.get-payroll-study-types');

    /* Rutas para gestionar las nacionalidades */
    Route::resource(
        'nationalities',
        'PayrollNationalityController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    /* Ruta que obtiene un arreglo con las nacionalidades registradas */
    Route::get(
        'get-nationalities',
        'PayrollNationalityController@getPayrollNationalities'
    )->name('payroll.get-payroll-nationalities');

    /* Rutas para gestionar los conceptos */
    Route::resource(
        'concepts',
        'PayrollConceptController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );
    /* Rutas para gestionar los conceptos Server Side Table */
    Route::get(
        'concepts-server',
        'PayrollConceptController@serverSideConcept'
    )->name('payroll.concepts-server');

    /* Ruta que obtiene un arreglo con los conceptos registrados */
    Route::get(
        'get-concepts',
        'PayrollConceptController@getPayrollConcepts'
    )->name('payroll.get-payroll-concepts');

    /* Ruta que obtiene un arreglo con las opciones a asignar un concepto */
    Route::get(
        'get-concept-assign-to',
        'PayrollConceptController@getPayrollConceptAssignTo'
    )->name('payroll.get-payroll-concept-assign-to');

    /* Ruta que obtiene un arreglo con las opciones a asignar un concepto, de acuerdo al parámetro seleccionado */
    Route::get(
        'get-concept-assign-options/{code}',
        'PayrollConceptController@getPayrollConceptAssignOptions'
    )->name('payroll.get-payroll-concept-assign-options');

    /* Ruta que obtiene un arreglo con las asignaciones de un concepto. */
    Route::get(
        'get-personal-concept-assign/{id}',
        'PayrollConceptController@getPayrollPersonalConceptAssign'
    )->name('payroll.get-payroll-personal-concept-assign');

    /* Ruta que obtiene el concepto asociado a un parametro global. */
    Route::get(
        'get-concept-parameter/{idParameter}',
        'PayrollConceptController@getPayrollConceptParameter'
    )->name('payroll.get-payroll-concept-parameter');

    /* Ruta que obtiene las cuentas contables asociadas a las cuentas presupuestarias para ralizar los conceptos */
    Route::get(
        'get-concept-accounting-accounts/{accountId}',
        'PayrollConceptController@getPayrollConceptAccountingAccounts'
    )->name('payroll.get-payroll-concept-accounting-account');

    /* Ruta que obtiene las cuentas contables asociadas a las cuentas presupuestarias para ralizar los conceptos */
    Route::get(
        'get-concept-accountable/{accountId}',
        'PayrollConceptController@getPayrollConceptAccountable'
    )->name('payroll.get-payroll-concept-accountable');

    /* Rutas para gestionar los tipos de concepto */
    Route::resource(
        'concept-types',
        'PayrollConceptTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los tipos de concepto registrados */
    Route::get(
        'get-concept-types',
        'PayrollConceptTypeController@getPayrollConceptTypes'
    )->name('payroll.get-payroll-concept-types');

    /* Rutas para gestionar los tipos de excepciones de jornada laboral */
    Route::resource(
        'exception-types',
        'PayrollExceptionTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los tipos de excepciones registrados */
    Route::get(
        'get-exception-types',
        'PayrollExceptionTypeController@getPayrollExceptionTypes'
    )->name('payroll.get-payroll-exception-types');

    /* Rutas para gestionar los tipos de pago */
    Route::resource(
        'payment-types',
        'PayrollPaymentTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );
    /* Rutas para calcular el monto del tipo de pago */
    Route::post('calculate-payment', 'PayrollPaymentTypeController@calculatePayrollPayment');

    /* Ruta que obtiene un arreglo con los tipos de pago registrados */
    Route::get(
        'get-payment-types',
        'PayrollPaymentTypeController@getPayrollPaymentTypes'
    )->name('payroll.get-payroll-payment-types');

    /* Ruta que obtiene un arreglo con los períodos de pago registrados, de acuerdo al tipo de pago seleccionado */
    Route::get(
        'get-payment-periods/{payment_type}',
        'PayrollPaymentTypeController@getPayrollPaymentPeriods'
    )->name('payroll.get-payroll-payment-periods');
    Route::post(
        'get-payment-periods-by-status',
        'PayrollPaymentTypeController@getPaymentPeriodsByStatus'
    )->name('payroll.get-payroll-payment-periods-by-status');

    /* Ruta que indica si un periodo ya fue asignado a un registro de nomina */
    Route::get(
        'get-payroll-assigned-period/{payment_period_id}/{payment_type_id}',
        'PayrollPaymentTypeController@getpayrollAssignedPeriod'
    )->name('payroll.get-payroll-assigned-period');

    /* Ruta que indica si el usuario tiene permiso de editar las fechas de los periodos */
    Route::get(
        'get-user-permission',
        'PayrollPaymentTypeController@getUserPermission'
    )->name('payroll.get-user-permission');

    /* Ruta que obtiene la información de un registro de política vacacional según el trabajador */
    Route::get(
        'get-vacation-policy/{payroll_staff_id}',
        'PayrollVacationPolicyController@getVacationPolicy'
    );
    Route::get(
        'get-vacation-policy/verify_assignment/{vacation_policy_id}',
        'PayrollVacationPolicyController@verifyAssignment'
    );

    /* Rutas para gestionar las políticas vacacionales registradas */
    Route::resource(
        'vacation-policies',
        'PayrollVacationPolicyController',
        ['as' => 'payroll', 'except' => ['create', 'edit']]
    );

    /* Ruta que obtiene la información de un registro de política de prestaciones según el trabajador */
    Route::get(
        'get-benefits-policy',
        'PayrollBenefitsPolicyController@getBenefitsPolicy'
    );

    /* Rutas para gestionar las políticas de prestaciones registradas */
    Route::resource(
        'benefits-policies',
        'PayrollBenefitsPolicyController',
        ['as' => 'payroll', 'except' => ['create', 'edit']]
    );

    /* Rutas para gestionar los niveles de idioma */
    Route::resource(
        'language-levels',
        'PayrollLanguageLevelController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    /* Ruta que obtiene un arreglo con los niveles de idioma registrados */
    Route::get(
        'get-language-levels',
        'PayrollLanguageLevelController@getPayrollLanguageLevels'
    )->name('payroll.get-payroll-language-levels');

    /* Rutas para gestionar los idiomas */
    Route::resource('languages', 'PayrollLanguageController', ['as' => 'payroll', 'except' => ['show']]);

    /* Ruta que obtiene un arreglo con los idiomas registrados */
    Route::get('get-languages', 'PayrollLanguageController@getPayrollLanguages')->name('payroll.get-payroll-languages');

    /* Rutas para gestionar los datos socioeconómicos del personal */
    Route::resource('socioeconomics', 'PayrollSocioeconomicController', ['as' => 'payroll']);

    /* Ruta que obtiene un listado de los datos socioeconómicos del personal */
    Route::get(
        'socioeconomics/show/vue-list',
        'PayrollSocioeconomicController@vueList'
    )->name('payroll.socioeconomics.vue-list');

    /* Rutas para gestionar los datos profesionales del personal */
    Route::resource('professionals', 'PayrollProfessionalController', ['as' => 'payroll']);

    /* Ruta que obtiene un listado de los datos profesionales del personal */
    Route::get(
        'professionals/show/vue-list',
        'PayrollProfessionalController@vueList'
    )->name('payroll.professionals.vue-list');

    /* Ruta que obtiene un arreglo con las profesiones registrados */
    Route::get(
        'get-json-professions',
        'PayrollProfessionalController@getJsonProfessions'
    )->name('payroll.get-json-professions');

    /* Rutas para gestionar los datos financieros del personal */
    Route::resource('financials', 'PayrollFinancialController', ['as' => 'payroll']);

    /* Ruta que obtiene un listado de los datos financiero del personal */
    Route::get(
        'financials/show/vue-list',
        'PayrollFinancialController@vueList'
    )->name('payroll.financials.vue-list');

    /* Rutas para gestionar los datos laborales del personal */
    Route::resource('employments', 'PayrollEmploymentController', ['as' => 'payroll']);

    /* Ruta que obtiene un listado de los datos laborales del personal */
    Route::get(
        'employments/show/vue-list',
        'PayrollEmploymentController@vueList'
    )->name('payroll.employments.vue-list');

    /* Rutas para gestionar los tipos de inactividad */
    Route::resource(
        'inactivity-types',
        'PayrollInactivityTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los tipos de inactividad registrados */
    Route::get(
        'get-inactivity-types',
        'PayrollInactivityTypeController@getPayrollInactivityTypes'
    )->name('payroll.get-payroll-inactivity-types');

    /* Rutas para gestionar los tipos de contrato */
    Route::resource(
        'contract-types',
        'PayrollContractTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los tipos de contrato registrados */
    Route::get(
        'get-contract-types',
        'PayrollContractTypeController@getPayrollContractTypes'
    )->name('payroll.get-payroll-contract-types');

    /* Rutas para gestionar los tipos de sector */
    Route::resource(
        'sector-types',
        'PayrollSectorTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los tipos de sector registrados */
    Route::get(
        'get-sector-types',
        'PayrollSectorTypeController@getPayrollSectorTypes'
    )->name('payroll.get-payroll-sector-types');

    /* Rutas para gestionar los grados de licencia de conducir */
    Route::resource(
        'license-degrees',
        'PayrollLicenseDegreeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los grados de licencia de conducir registrados */
    Route::get(
        'get-license-degrees',
        'PayrollLicenseDegreeController@getPayrollLicenseDegrees'
    )->name('payroll.get-payroll-license-degrees');

    /* Rutas para gestionar los tipos de sangre */
    Route::resource(
        'blood-types',
        'PayrollBloodTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con los tipos de sangre registrados */
    Route::get(
        'get-blood-types',
        'PayrollBloodTypeController@getPayrollBloodTypes'
    )->name('payroll.get-payroll-blood-types');

    /* Rutas para gestionar los tipos de liquidación */
    Route::resource(
        'settlement-types',
        'PayrollSettlementTypeController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Rutas para gestionar los parentescos */
    Route::resource(
        'relationships',
        'PayrollRelationshipController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );
    /* Ruta que obtiene un arreglo con los parentescos registrados */
    Route::get(
        'get-relationships',
        'PayrollRelationshipController@getPayrollRelationship'
    )->name('payroll.get-relationships');

    /* Rutas para gestionar las discapacidades */
    Route::resource(
        'disabilities',
        'PayrollDisabilityController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta que obtiene un arreglo con las discapacidades registradas */
    Route::get(
        'get-disabilities',
        [Modules\Payroll\Http\Controllers\PayrollDisabilityController::class, 'getPayrollDisabilities']
    )->name('payroll.get-payroll-disabilities');

    /* Rutas para gestionar los parámetros de nómina */
    Route::resource('parameters', 'PayrollParameterController', ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]);

    /* Ruta que obtiene un arreglo con los parámetros de nómina registrados */
    Route::get('get-parameters', 'PayrollParameterController@getPayrollParameters');

    /* Ruta que obtiene un arreglo con parámetros de nómina registrados asociados a un concepto */
    Route::post('get-parameters', 'PayrollParameterController@getPayrollParameters');

    /* Ruta que obtiene un arreglo con parámetros globales reiniciables de nómina registrados */
    Route::get('get-parameters-resettable', 'PayrollParameterController@getPayrollParametersResettable');

    /* Ruta que obtiene un arreglo con los parámetros asociados al expediente del trabajador registrados */
    Route::get('get-parameter-options/{code}', 'PayrollParameterController@getPayrollParameterOptions');

    /* Ruta que obtiene un arreglo con los tipos de parámetros de nómina registrados */
    Route::get('get-parameter-types', 'PayrollParameterController@getPayrollParameterTypes');

    /* Ruta que actualiza los parámetros para los reporte de nómina registrados */
    Route::post('update-report-parameters', 'PayrollParameterController@updateReportParameters')->name('payroll.parameters.update-report-parameters');

    /* Ruta que optiene los parámetros para los reporte de nómina registrados */
    Route::get('get-report-parameters', 'PayrollParameterController@getReportParameters');

    /* Ruta que optiene los parámetros que son de tipo de parámetros de tiempo registrados */
    Route::get('get-time-parameters', 'PayrollParameterController@getTimeParameters');


    /* Rutas para gestionar los escalafones de nómina */
    Route::resource('salary-scales', 'PayrollSalaryScaleController', ['except' => ['show', 'create', 'edit']]);

    /* Ruta que obtiene un arreglo con los escalafones de nómina registrados */
    Route::post('get-salary-scales', 'PayrollSalaryScaleController@getSalaryScales');

    /* Ruta que obtiene la información de un escalafón de nómina registrado */
    Route::get('salary-scales/info/{id}', 'PayrollSalaryScaleController@info');

    /* Rutas para gestionar los tabuladores de nómina */
    Route::resource('salary-tabulators', 'PayrollSalaryTabulatorController', ['except' => ['show', 'create', 'edit']]);

    /* Ruta que obtiene la información de un registro de nómina */
    Route::get(
        'salary-tabulators/show/{tabulator}',
        'PayrollSalaryTabulatorController@show'
    )->name('payroll.salary-tabulators.show');

    /* Ruta que permite exportar la información de los tabuladores salariales registrados */
    Route::get('salary-tabulators/export/{tabulator}', 'PayrollSalaryTabulatorController@export')
        ->name('payroll.salary-tabulators.export');

    /* Ruta que permite importar la información de los tabuladores salariales registrados */
    Route::post('salary-tabulators/import', 'PayrollSalaryTabulatorController@import')
        ->name('payroll.salary-tabulators.import');

    /* Ruta que obtiene un arreglo con los tabuladores salariales registrados */
    Route::get('get-salary-tabulators', 'PayrollSalaryTabulatorController@getSalaryTabulators');

    /* Ruta que obtiene un arreglo con las agrupaciones de los tabuladores salariales registrados */
    Route::get('get-salary-tabulators-groups', 'PayrollParameterController@getSalaryTabulatorsGroups');

    /* Ruta que obtiene un arreglo con los registros asociados al expediente del trabajador registrados */
    Route::get('get-associated-records', 'PayrollParameterController@getAssociatedRecords');

    /* Ruta que obtiene un arreglo con los registros asociados a las vacaciones registrados */
    Route::get('get-vacation-associated-records', 'PayrollParameterController@getVacationAssociatedRecords');

    /* Ruta que obtiene un arreglo con los registros asociados a las prestaciones sociales registrados */
    Route::get('get-benefit-associated-records', 'PayrollParameterController@getBenefitAssociatedRecords');

    /* Rutas para gestionar los ajustes en las tablas salariales */
    Route::resource(
        'salary-adjustments',
        'PayrollSalaryAdjustmentController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    /* Ruta para gestionar la importación de carga de ajuste en tablas salariales */
    Route::get('salary-adjustments/export', 'PayrollSalaryAdjustmentController@export')->name('payroll.salary-adjustments.export');

    /* Ruta para gestionar la exportación de la planilla de ajuste en tablas salariales */
    Route::post('salary-adjustments/import', 'PayrollSalaryAdjustmentController@import')->name('payroll.salary-adjustments.import');

    /* Ruta que obtiene un listado de los ajustes en las tablas salariales */
    Route::get(
        'salary-adjustments/vue-list',
        'PayrollSalaryAdjustmentController@vueList'
    )->name('payroll.salary-adjustments.vue-list');

    /* Ruta que obtiene la información de los ajustes en las tablas salariales */
    Route::get(
        'salary-adjustments/vue-info/{id}',
        'PayrollSalaryAdjustmentController@vueInfo'
    )->name('payroll.salary-adjustments.vue-info');

    /* Rutas para gestionar las solicitudes de vacaciones */
    Route::resource(
        'vacation-requests',
        'PayrollVacationRequestController',
        ['as' => 'payroll', 'except' => ['edit', 'show']]
    );

    /* Rutas para gestionar las solicitudes de suspension de vacaciones */
    Route::resource(
        'suspension-vacation-requests',
        'PayrollSuspensionVacationRequestController',
        ['as' => 'payroll', 'except' => ['edit', 'show']]
    );

    /* Ruta que obtiene un listado de las solicitudes de vacaciones */
    Route::get(
        'vacation-requests/vue-list',
        'PayrollVacationRequestController@vueList'
    )->name('payroll.vacation-requests.vue-list');

    /* Ruta que obtiene un listado de las solicitudes de suspension de vacaciones */
    Route::get(
        'suspension-vacation-requests/vue-list',
        'PayrollSuspensionVacationRequestController@vueList'
    )->name('payroll.suspension-vacation-requests.vue-list');

    /* Ruta que obtiene un listado de las solicitudes de vacaciones pendientes */
    Route::get(
        'vacation-requests/vue-pending-list',
        'PayrollVacationRequestController@vuePendingList'
    )->name('payroll.vacation-requests.vue-pending-list');

    /* Ruta que permite actualizar una solicitud de vacaciones*/
    Route::patch(
        'vacation-requests/review/{request}',
        'PayrollVacationRequestController@review'
    )->name('payroll.request.review');

    /* Ruta que permite editar la información de un registro de solicitud de vacaciones */
    Route::get(
        'vacation-requests/edit/{vacation_request}',
        'PayrollVacationRequestController@edit'
    )->name('payroll.vacation-requests.edit');

    /* Ruta que obtiene la información de un registro de solicitud de vacaciones */
    Route::get(
        'vacation-requests/show/{vacation_request}',
        'PayrollVacationRequestController@show'
    )->name('payroll.vacation-requests.show');

    /* Ruta que obtiene un listado de las solicitudes de vacaciones de un trabajador */
    Route::get(
        'get-vacation-requests/{staff_id}',
        'PayrollVacationRequestController@getVacationRequests'
    );

     /* Ruta que obtiene un listado de las suspension de solicitud de vacaiones de un trabajador */
    Route::get(
        'get-suspension-vacation-requests/{staff_id}',
        'PayrollSuspensionVacationRequestController@getSuspensionVacationRequests'
    );

    /* Rutas para aprobar y rechazar solicitud de vacaciones */
    Route::put('vacation-requests/approved/{request}/{check_permission?}', 'PayrollVacationRequestController@approved')->name('payroll.vacation-requests.approved');
    Route::put('vacation-requests/rejected/{request}/{check_permission?}', 'PayrollVacationRequestController@rejected')->name('payroll.vacation-requests.rejected');

    /* Rutas para aprobar y rechazar solicitud de suspension de vacaciones */
    Route::put(
        'suspension-vacation-requests/approved/{suspension_vacation_request}/{check_permission?}',
        'PayrollSuspensionVacationRequestController@approved'
    )->name('payroll.suspension.vacation-requests.approved');
    Route::put(
        'suspension-vacation-requests/rejected/{suspension_vacation_request}/{check_permission?}',
        'PayrollSuspensionVacationRequestController@rejected'
    )->name('payroll.suspension-vacation-requests.rejected');

    /* Rutas para gestionar las solicitudes de prestaciones */
    Route::resource(
        'benefits-requests',
        'PayrollBenefitsRequestController',
        ['as' => 'payroll', 'except' => ['edit', 'show']]
    );

    /* Ruta que obtiene un listado de las solicitudes de prestaciones */
    Route::get(
        'benefits-requests/vue-list',
        'PayrollBenefitsRequestController@vueList'
    )->name('payroll.benefits-requests.vue-list');

    /* Ruta que permite actualizar una solicitud de prestaciones */
    Route::patch(
        'benefits-requests/review/{request}',
        'PayrollBenefitsRequestController@review'
    )->name('payroll.benefits-request.review');

    /* Ruta que permite editar la información de un registro de solicitud de prestaciones */
    Route::get(
        'benefits-requests/edit/{benefits_request}',
        'PayrollBenefitsRequestController@edit'
    )->name('payroll.benefits-requests.edit');

    /* Ruta que obtiene la información de un registro de solicitud de prestaciones */
    Route::get(
        'benefits-requests/show/{benefits_request}',
        'PayrollBenefitsRequestController@show'
    )->name('payroll.benefits-requests.show');

    /* Ruta que obtiene un listado de las solicitudes de prestaciones de un trabajador */
    Route::get(
        'get-benefits-requests/{staff_id}',
        'PayrollBenefitsRequestController@getBenefitsRequests'
    );

    /* Rutas para gestionar las políticas de permisos */
    Route::resource(
        'permission-policies',
        'PayrollPermissionPolicyController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );
    Route::get('/get-permission-policies', 'PayrollPermissionPolicyController@getPermissionPolicies');

    /* Rutas para solicitar permisos*****/

    /* Rutas para gestionar las solicitudes de permiso */
    Route::resource(
        'permission-requests',
        'PayrollPermissionRequestController',
        ['as' => 'payroll', 'except' => ['edit', 'show']]
    );
    /* Ruta que muestra información sobre la solicitud de permiso */
    Route::get('permission-requests/vue-info/{permission_request}', 'PayrollPermissionRequestController@vueInfo')
        ->name('payroll.permission-requests.vue-info');

    /* Ruta que obtiene un listado de las solicitudes de permiso */
    Route::get(
        'permission-requests/vue-list',
        'PayrollPermissionRequestController@vueList'
    )->name('payroll.permission-requests.vue-list');

    /* Ruta que permite editar la información de un registro de solicitud de permiso */
    Route::get(
        'permission-requests/edit/{permission_request}',
        'PayrollPermissionRequestController@edit'
    )->name('payroll.permission-requests.edit');

    /* Ruta que obtiene la información de un registro de solicitud de permiso */
    Route::get(
        'permission-requests/show/{permission_request}',
        'PayrollPermissionRequestController@show'
    )->name('payroll.permission-requests.show');

    Route::delete('/permission-requests/delete/{permission_request}', 'PayrollPermissionRequestController@destroy')
        ->name('payroll.permission-requests.delete');

    // Ruta que obtiene un listado de las solicitudes de permisos pendientes */

    Route::put(
        'permission-requests/request-approved/{permission_request}',
        'PayrollPermissionRequestController@approved'
    )->name('payroll.permission-requests.approved');
    Route::put(
        'permission-requests/request-rejected/{permission_request}',
        'PayrollPermissionRequestController@rejected'
    )->name('payroll.permission-requests.rejected');

    /* Ruta para la informacion de los días feriados */
    Route::resource(
        'holidays',
        'PayrollHolidayController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta para obtener la lista de los días feriados */
    Route::get(
        'get-holidays',
        'PayrollHolidayController@getHolidays'
    )->name('payroll.get-holidays');

    /* Rutas para gestionar los grupos de supervisados de la institución */
    Route::resource(
        'supervised-groups',
        'PayrollSupervisedGroupController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    /* Ruta para obtener la lista de los grupos de supervisados */
    Route::get(
        'get-supervised-groups',
        'PayrollSupervisedGroupController@getPayrollSupervisedGroups'
    );

    /* Ruta para obtener la lista de los trabajadores de forma agrupada */
    Route::get(
        'get-grouped-staff/{ids?}',
        'PayrollSupervisedGroupController@getGroupedStaff'
    );

    /* Rutas para gestionar los esquemas de guardias de la institución */
    Route::get(
        'guard-schemes/vue-list',
        'PayrollGuardSchemeController@vueList'
    );
    Route::put(
        'guard-schemes/approve/{id}',
        'PayrollGuardSchemeController@approve'
    );
    Route::put(
        'guard-schemes/reject/{id}',
        'PayrollGuardSchemeController@reject'
    );
    Route::get(
        'guard-schemes/show/{id}',
        'PayrollGuardSchemeController@show'
    );

    Route::resource(
        'guard-schemes',
        'PayrollGuardSchemeController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    Route::post(
        'guard-schemes/periods',
        'PayrollGuardSchemeController@addPeriod'
    );
    Route::patch(
        'guard-schemes/periods/{id}',
        'PayrollGuardSchemeController@editPeriod'
    );

    Route::get(
        'guard-schemes/get-payroll-confirmed-periods',
        'PayrollGuardSchemeController@getPayrollConfirmedGuardPeriods'
    );

    Route::put(
        'guard-schemes/periods/confirm/{id}',
        'PayrollGuardSchemeController@confirmPeriod'
    );

    Route::put(
        'guard-schemes/periods/request-review/{id}',
        'PayrollGuardSchemeController@requestReviewPeriod'
    );

    /* Rutas para gestionar el tipo de beca */
    Route::resource(
        'scholarship-types',
        'PayrollScholarshipTypeController',
        ['as' => 'payroll', 'except' => ['create', 'edit', 'show']]
    );
    /* Ruta que obtiene un arreglo con los parentescos registrados */
    Route::get(
        'get-scholarship-types',
        'PayrollScholarshipTypeController@getPayrollScholarshipType'
    )->name('payroll.get-scholarship-types');
    /* Rutas para gestionar los parámetros de la hoja de tiempo */
    Route::resource(
        'time-sheet-parameters',
        'PayrollTimeSheetParameterController',
        ['as' => 'payroll', 'except' => ['show', 'create', 'edit']]
    );

    Route::get(
        'get-time-sheet-parameters',
        'PayrollTimeSheetParameterController@getPayrollTimeSheetParameters'
    );

    /* Rutas para gestionar los datos de las cuentas de personal */
    Route::resource(
        'staff-accounts',
        'PayrollStaffAccountController',
        ['as' => 'payroll']
    );

    Route::get('staff-accounts/show/vue-list', 'PayrollStaffAccountController@vueList')
        ->name('paryoll.staff-accounts.vue-list');

    /* Rutas para gestionar la hoja de tiempo */
    Route::resource(
        'time-sheet',
        'PayrollTimeSheetController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    Route::get(
        'time-sheet/vue-list',
        'PayrollTimeSheetController@vueList'
    );

    Route::get(
        'time-sheet/vue-info/{id}',
        'PayrollTimeSheetController@vueInfo'
    );

    Route::put('time-sheet/approve/{id}', 'PayrollTimeSheetController@approve');
    Route::put('time-sheet/reject/{id}', 'PayrollTimeSheetController@reject');
    Route::put('time-sheet/confirm/{id}', 'PayrollTimeSheetController@confirm');

    Route::post('time-sheet/export', 'PayrollTimeSheetController@export');
    Route::post('time-sheet/import', 'PayrollTimeSheetController@import');

    /* Rutas para gestionar la hoja de tiempo pendiente */
    Route::resource(
        'time-sheet-pending',
        'PayrollTimeSheetPendingController',
        ['as' => 'payroll', 'except' => ['show']]
    );

    Route::get(
        'time-sheet-pending/vue-list',
        'PayrollTimeSheetPendingController@vueList'
    );

    Route::get(
        'time-sheet-pending/vue-info/{id}',
        'PayrollTimeSheetPendingController@vueInfo'
    );

    Route::put('time-sheet-pending/approve/{id}', 'PayrollTimeSheetPendingController@approve');
    Route::put('time-sheet-pending/reject/{id}', 'PayrollTimeSheetPendingController@reject');
    Route::put('time-sheet-pending/confirm/{id}', 'PayrollTimeSheetPendingController@confirm');

    Route::post('time-sheet-pending/export', 'PayrollTimeSheetPendingController@export');
    Route::post('time-sheet-pending/import', 'PayrollTimeSheetPendingController@import');

    /*
     | --------------------------------------------------------------------------------------
     | Grupo de rutas para gestionar la generación de reportes en el módulo de talento humano
     | --------------------------------------------------------------------------------------
     */
    Route::group([
        'middleware' => ['web', 'auth', 'verified'],
        'prefix' => 'reports'
    ], function () {
        Route::get('show/{filename}', 'PayrollReportController@show')
            ->name('payroll.reports.show');

        Route::get('showPdfSign/{filename}', 'PayrollReportController@showPdfSign')
            ->name('payroll.reports.showPdfSign');

        /* Ruta que permite generar el reporte de los registros de nómina asociados a un pago */
        Route::post('registers/create', 'PayrollReportController@   create')
            ->name('payroll.reports.registers.create');

        /* ruta que permite generar el reporte de las solicitudes de vacaciones */
        Route::get('vacation-requests', 'PayrollReportController@vacationRequests')
            ->name('payroll.reports.vacation-requests');
        Route::post('vacation-requests/create', 'PayrollReportController@create')
            ->name('payroll.reports.vacation-requests.create');

        /* Ruta que permite generar el reporte de los registros de los empleados */
        Route::get('employment-status', 'PayrollReportController@employmentStatus')
            ->name('payroll.reports.employment-status');
        Route::post('employment-status/create', 'PayrollReportController@create')
            ->name('payroll.reports.employment-status.create');

        /* Ruta que permite generar el reporte de los empleados */
        Route::get('staffs', 'PayrollReportController@staffs')
            ->name('payroll.reports.staffs');
        Route::post('staffs/create', 'PayrollReportController@reportPdf')
            ->name('payroll.reports.staffs.create');

        Route::get('vacation-bonus-calculations', 'PayrollReportController@vacationBonusCalculations')
            ->name('payroll.reports.vacation-bonus-calculations');
        Route::post('vacation-bonus-calculations/create', 'PayrollReportController@create')
            ->name('payroll.reports.vacation-bonus-calculations.create');

        /* Rutas que permiten generar reportes de los registros de prestaciones */
        Route::get('benefits/benefit-advances', 'PayrollReportController@benefitAdvances')
            ->name('payroll.reports.benefits.benefit-advances');
        Route::post('benefits/benefit-advances/create', 'PayrollReportController@create')
            ->name('payroll.reports.benefits.benefit-advances.create');

        Route::get('staff-vacation-enjoyment', 'PayrollReportController@staffVacationEnjoyment')
            ->name('payroll.reports.staff-vacation-enjoyment');
        Route::post('staff-vacation-enjoyment/create', 'PayrollReportController@create')
            ->name('payroll.reports.staff-vacation-enjoyment.create');

        Route::post('vue-list', 'PayrollReportController@vueList')
            ->name('payroll.reports.vue-list');

        Route::get('vue-list-report', 'PayrollReportController@vueListReport')
            ->name('payroll.reports.vue-list-report');

        /* ruta que permite generar el reporte de conceptos*/

        Route::get('concepts', 'PayrollReportController@concepts')
            ->name('payroll.reports.concepts');
        Route::post('concepts/create', 'PayrollReportController@create')
            ->name('payroll.reports.concepts.create');

        /* ruta que permite generar el reporte de relación de conceptos*/

        Route::get('relationship-concepts', 'PayrollReportController@relationshipConcepts')
            ->name('payroll.reports.relationship-concepts');
        Route::post('relationship-concepts/create', 'PayrollReportController@create')
            ->name('payroll.reports.relationship-concepts.create');

        /* Rutas para gestionar el reporte de trabajadores por nómina */
        Route::get('workers-by-payroll', 'PayrollReportController@workersByPayroll')
            ->name('payroll.reports.workers-by-payroll');

        Route::post('workers-by-payroll/filter', 'PayrollReportController@filterWorkersByPayroll')
        ->name('payroll.reports.workers-by-payroll.filter');

        /* Rutas para gestionar el reporte de hoja de tiempo */
        Route::get('time-sheets', 'PayrollReportController@timeSheets')
            ->name('payroll.reports.time-sheets');
        Route::post('time-sheets/create', 'PayrollReportController@timeSheetsPdf')
            ->name('payroll.reports.time-sheets.create');

        /* Rutas para gestionar el reporte de carga familiar */
        Route::get('family-burden', 'PayrollReportController@familyBurden')
            ->name('payroll.reports.family-burden');
        Route::post('family-burden/create', 'PayrollReportController@create')
            ->name('payroll.reports.familiy-burden.create');
        /* Rutas para gestionar el reporte de recibos de pago */
        Route::get('payment-receipts', 'PayrollReportController@paymentReceipt')
            ->name('payroll.reports.payment-receipts');
        Route::post(
            'payment-receipts/create',
            [PayrollReportController::class, 'paymentReceiptCreate']
        )->name('payroll.reports.payment-receipts.create');
    });

    /* Ruta que permite generar los reportes de conceptos como archivos .xlsx */
    Route::get('report-concepts/export', [PayrollReportController::class, 'exportReportConcepts']);
    /* Ruta que permite generar los reportes de trabajadores como archivos .xlsx */
    Route::post('report-staffs/export', [PayrollReportController::class, 'exportReportStaffs']);
});
