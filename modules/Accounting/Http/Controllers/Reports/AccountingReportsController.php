<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingEntry;

/**
 * @class AccountingReportPdfCheckupBalanceController
 * @brief Controlador para el manejo de las vistas y consulta segun el tipo de reporte a generar
 *
 * Clase que gestiona el manejo de las vistas y consulta segun el tipo de reporte a generar
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 * @copyright <a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *                LICENCIA DE SOFTWARE CENDITEL</a>
 */
class AccountingReportsController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:accounting.report.accountingbooks', ['only' => ['accountingBooks']]);
        $this->middleware('permission:accounting.report.financestatements', ['only' => ['financeStatements']]);
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function accountingBooks()
    {
        /**
         * [$records lista de cuentas patrimoniales]
         * @var array
         */
        $records          = [];

        $yearOld          = $this->calcualteYearOld();

        $records_auxiliar = [];

        array_push($records, [
                'id'   => '0',
                'text' =>  "Seleccione...",
            ]);
        array_push($records_auxiliar, [
                'id'   => '0',
                'text' =>  "Seleccione...",
            ]);
        /**
         * se realiza la busqueda de manera ordenada en base al codigo
         */
        foreach (
            AccountingAccount::orderBy('group', 'ASC')
                                    ->orderBy('subgroup', 'ASC')
                                    ->orderBy('item', 'ASC')
                                    ->orderBy('generic', 'ASC')
                                    ->orderBy('specific', 'ASC')
                                    ->orderBy('subspecific', 'ASC')
                                    ->where('active', true)
                                    ->get() as $account
        ) {
            // datos de las cuentas patrimoniales
            array_push($records, [
                'id'   => $account->id,
                'text' =>   "{$account->getCodeAttribute()} - {$account->denomination}",
            ]);
            if ($account->group > 0 && $account->subgroup > 0) {
                array_push($records_auxiliar, [
                    'id'   => $account->id,
                    'text' =>   "{$account->getCodeAttribute()} - {$account->denomination}",
                ]);
            }
        }
        /**
         * se convierte array a JSON
         */
        $records_auxiliar = json_encode($records_auxiliar);
        $records          = json_encode($records);
        // $currencies       = json_encode(template_choices('App\Models\Currency', ['symbol', '-', 'name'], [], true));

        return view('accounting::reports.accounting_books', compact(
            'yearOld',
            'records',
            'records_auxiliar',
        ));
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function financeStatements()
    {
        $yearOld = $this->calcualteYearOld();

        /**
         * [$type_report_1 tipo de reporte que abrira]
         * @var string
         */
        $type_report_1 = 'BalanceSheet';

        /**
         * [$type_report_2 tipo de reporte que abrira]
         * @var string
         */
        $type_report_2 = 'StateOfResults';

        // $currencies = json_encode(template_choices('App\Models\Currency', ['symbol', '-', 'name'], [], true));

        return view('accounting::reports.finance_statements', compact(
            'yearOld',
            'type_report_1',
            'type_report_2',
        ));
    }

    public function calcualteYearOld()
    {
        /**
         * [$entries almacena el registro de asiento contable mas antiguo]
         * @var AccountingEntry
         */
        $entries = AccountingEntry::where('approved', true)->orderBy('from_date', 'ASC')->first();
        if ($entries === null) {
            return date('Y');
        }
        /**
         * [$yearOld determinara el año mas antiguo para el filtrado]
         * @var string
         */
        $yearOld = explode('-', $entries['from_date'])[0];

        return $yearOld;
    }
}
