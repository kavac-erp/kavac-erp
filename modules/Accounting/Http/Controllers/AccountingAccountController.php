<?php

namespace Modules\Accounting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingEntryAccount;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Accounting\Exports\AccountingAccountExport;
use Modules\Accounting\Imports\AccountingAccountImport;

/**
 * @class AccountingAccountController
 * @brief Controlador de Cuentas patrimoniales
 *
 * Clase que gestiona las Cuentas patrimoniales
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class AccountingAccountController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:accounting.account.list', ['only' => 'index']);
        $this->middleware(
            'permission:accounting.account.create',
            ['only' => ['store', 'registerImportedAccounts', 'export']]
        );
        $this->middleware(
            'permission:accounting.account.edit',
            ['only' => ['update']]
        );
        $this->middleware(
            'permission:accounting.account.delete',
            ['only' => 'destroy']
        );
    }

    /**
     * Muestra un listado de cuentas patrimoniales
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => $this->getAccounts()], 200);
    }

    /**
     * Crea una nueva cuenta patrimonial
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     *
     * @return JsonResponse información de la operacion y listado de los registros actualizados
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'group' => ['required', 'digits:1'],
            'subgroup' => ['required', 'digits:1'],
            'item' => ['required', 'digits:1'],
            'generic' => ['required', 'digits:2'],
            'specific' => ['required', 'digits:2'],
            'subspecific' => ['required', 'digits:2'],
            'institutional' => ['required', 'digits:3'],
            'denomination' => ['required'],
            'type' => ['nullable'],
            'active' => ['required'],
            'original' => ['nullable'],
        ]);

        DB::transaction(function () use ($request) {

            /* Objeto que contiene los datos de la cuenta ya registrada si existe */
            $accountingAccount = AccountingAccount::where('group', $request->group)
                ->where('subgroup', $request->subgroup)
                ->where('item', $request->item)
                ->where('generic', $request->generic)
                ->where('specific', $request->specific)
                ->where('subspecific', $request->subspecific)
                ->where('institutional', $request->institutional)
                ->where('active', true)->first();

            /*
             * Si la cuenta a registrar ya existe en la base de datos y la nueva cuenta se indica como activa,
             * se desactiva la cuenta anterior
             */
            if ($accountingAccount && $request->active !== null) {
                /* define si la cuenta esta activa */
                $accountingAccount->active = false;
                $accountingAccount->save();

                if ($accountingAccount->subgroup == '0') {
                    $accountingAccountChilds = AccountingAccount::where('group', $request->group)
                        ->where('active', true)->update(['active' => false]);
                    if ($accountingAccount->item == '0') {
                        $accountingAccountChilds = AccountingAccount::where('group', $request->group)
                            ->where('subgroup', $request->subgroup)
                            ->where('active', true)->update(['active' => false]);
                        if ($accountingAccount->generic == '00') {
                            $accountingAccountChilds = AccountingAccount::where('group', $request->group)
                                ->where('subgroup', $request->subgroup)
                                ->where('item', $request->item)
                                ->where('active', true)->update(['active' => false]);
                            if ($accountingAccount->specific == '00') {
                                $accountingAccountChilds = AccountingAccount::where('group', $request->group)
                                    ->where('subgroup', $request->subgroup)
                                    ->where('item', $request->item)
                                    ->where('generic', $request->generic)
                                    ->where('active', true)->update(['active' => false]);
                                if ($accountingAccount->subspecific == '00') {
                                    $accountingAccountChilds = AccountingAccount::where('group', $request->group)
                                        ->where('subgroup', $request->subgroup)
                                        ->where('item', $request->item)
                                        ->where('generic', $request->generic)
                                        ->where('specific', $request->specific)
                                        ->where('active', true)->update(['active' => false]);
                                    if ($accountingAccount->institutional == '000') {
                                        $accountingAccountChilds = AccountingAccount::where('group', $request->group)
                                            ->where('subgroup', $request->subgroup)
                                            ->where('item', $request->item)
                                            ->where('generic', $request->generic)
                                            ->where('specific', $request->specific)
                                            ->where('subspecific', $request->subspecific)
                                            ->where('active', true)->update(['active' => false]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /* Datos de la cuenta padre si aplica */
            $parent = AccountingAccount::getParent(
                $request->group,
                $request->subgroup,
                $request->item,
                $request->generic,
                $request->specific,
                $request->subspecific,
                $request->institutional
            );

            /*   determina si la cuenta es madre */
            if ($parent) {
                Accountingaccount::create([
                    'group' => $request->group,
                    'subgroup' => $request->subgroup,
                    'item' => $request->item,
                    'generic' => $request->generic,
                    'specific' => $request->specific,
                    'subspecific' => $request->subspecific,
                    'institutional' => $request->institutional,
                    'denomination' => $request->denomination,
                    'active' => $request->active,
                    'resource' => isset($parent->resource)
                    ? $parent->resource
                    : (isset($request->type) ? ($request->type == 'R') : null),
                    'egress' => isset($parent->egress)
                    ? $parent->egress
                    : (isset($request->type) ? ($request->type == 'E') : null),
                    'inactivity_date' => (!$request->active) ? date('Y-m-d') : null,
                    'parent_id' => ($parent) ? $parent->id : null,
                    'original' => $request->original ?? false,
                ]);
            } else {
                //caso tipo de cuenta 1.1.1.01.02.01.001 -> no tiene padre
                // es decir la 1.1.1.01.02.01.000 no existe.
                if ($request->institutional != '000') {
                    //reviso si la cuenta 1.1.1.01.02.01.000 tiene creado el padre
                    // es decir 1.1.1.01.02.00.000
                    $parent = AccountingAccount::getParent(
                        $request->group,
                        $request->subgroup,
                        $request->item,
                        $request->generic,
                        $request->specific,
                        $request->subspecific,
                        '000'
                    );
                    if ($parent) {
                        //si tiene padre entnces existe 1.1.1.01.02.00.000
                        //por lo que solo crearia 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                        $level6 = $this->createAccounts(
                            $request->group,
                            $request->subgroup,
                            $request->item,
                            $request->generic,
                            $request->specific,
                            $request->subspecific,
                            '000',
                            $request->denomination,
                            $request->active,
                            $request->resource,
                            $request->type,
                            $parent,
                            $request->original
                        );
                        //ahora creo 1.1.1.01.02.01.001
                        $this->createAccounts(
                            $request->group,
                            $request->subgroup,
                            $request->item,
                            $request->generic,
                            $request->specific,
                            $request->subspecific,
                            $request->institutional,
                            $request->denomination,
                            $request->active,
                            $request->resource,
                            $request->type,
                            $level6,
                            $request->original
                        );
                    } else {
                        //no existe 1.1.1.01.02.00.000
                        // por lo que revisare que existe 1.1.1.01.00.00.000
                        $parent = AccountingAccount::getParent(
                            $request->group,
                            $request->subgroup,
                            $request->item,
                            $request->generic,
                            $request->specific,
                            '00',
                            '000'
                        );
                        if ($parent) {
                            //si existe 1.1.1.01.00.00.000 padre
                            //por lo que solo crearia 1.1.1.01.02.00.000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                            //ahora creo 1.1.1.01.02.01.001
                            //1.1.1.01.02.00.000
                            $level5 = $this->createAccounts(
                                $request->group,
                                $request->subgroup,
                                $request->item,
                                $request->generic,
                                $request->specific,
                                '00',
                                '000',
                                $request->denomination,
                                $request->active,
                                $request->resource,
                                $request->type,
                                $parent,
                                $request->original
                            );
                            //1.1.1.01.02.01.000
                            $level6 = $this->createAccounts(
                                $request->group,
                                $request->subgroup,
                                $request->item,
                                $request->generic,
                                $request->specific,
                                $request->subspecific,
                                '000',
                                $request->denomination,
                                $request->active,
                                $request->resource,
                                $request->type,
                                $level5,
                                $request->original
                            );
                            //ahora creo 1.1.1.01.02.01.001
                            $this->createAccounts(
                                $request->group,
                                $request->subgroup,
                                $request->item,
                                $request->generic,
                                $request->specific,
                                $request->subspecific,
                                $request->institutional,
                                $request->denomination,
                                $request->active,
                                $request->resource,
                                $request->type,
                                $level6,
                                $request->original
                            );
                        } else {
                            //no existe 1.1.1.01.00.00.000
                            //por lo que solo crearia 1.1.1.01.00.00.000->
                            // 1.1.1.01.02.00.000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                            //chequeo si existe el padre de //1.1.1.01.00.00.000 es decir 1.1.1.00.00.00.000
                            $parent = AccountingAccount::getParent(
                                $request->group,
                                $request->subgroup,
                                $request->item,
                                $request->generic,
                                '00',
                                '00',
                                '000'
                            );
                            if ($parent) {
                                //si existe 1.1.1.00.00.00.000 padre
                                //por lo que solo crearia 1.1.1.01.00.00.000->
                                // 1.1.1.01.02.00.000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                                //1.1.1.01.00.00.000
                                $level5 = $this->createAccounts(
                                    $request->group,
                                    $request->subgroup,
                                    $request->item,
                                    $request->generic,
                                    '00',
                                    '00',
                                    '000',
                                    $request->denomination,
                                    $request->active,
                                    $request->resource,
                                    $request->type,
                                    $parent,
                                    $request->original
                                );
                                //1.1.1.01.02.00.000
                                $level6 = $this->createAccounts(
                                    $request->group,
                                    $request->subgroup,
                                    $request->item,
                                    $request->generic,
                                    $request->specific,
                                    '00',
                                    '000',
                                    $request->denomination,
                                    $request->active,
                                    $request->resource,
                                    $request->type,
                                    $level5,
                                    $request->original
                                );
                                //1.1.1.01.02.01.000
                                $level7 = $this->createAccounts(
                                    $request->group,
                                    $request->subgroup,
                                    $request->item,
                                    $request->generic,
                                    $request->specific,
                                    $request->subspecific,
                                    '000',
                                    $request->denomination,
                                    $request->active,
                                    $request->resource,
                                    $request->type,
                                    $level6,
                                    $request->original
                                );
                                //ahora creo 1.1.1.01.02.01.001
                                $this->createAccounts(
                                    $request->group,
                                    $request->subgroup,
                                    $request->item,
                                    $request->generic,
                                    $request->specific,
                                    $request->subspecific,
                                    $request->institutional,
                                    $request->denomination,
                                    $request->active,
                                    $request->resource,
                                    $request->type,
                                    $level7,
                                    $request->original
                                );
                            } else {
                                //no existe 1.1.1.00.00.00.000
                                //por lo que solo crearia 1.1.1.00.00.00.000->
                                // 1.1.1.01.00.00.000->1.1.1.01.02.00.000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                                //chequeo si existe el padre de //1.1.1.00.00.00.000 es decir 1.1.0.00.00.00.000
                                $parent = AccountingAccount::getParent(
                                    $request->group,
                                    $request->subgroup,
                                    $request->item,
                                    '00',
                                    '00',
                                    '00',
                                    '000'
                                );
                                if ($parent) {
                                    //si existe 1.1.0.00.00.00.000
                                    //por lo que solo crearia 1.1.1.00.00.00.
                                    // 000->1.1.1.01.00.00.000->1.1.1.01.02.00
                                    // .000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                                    //1.1.1.00.00.00.000
                                    $level4 = $this->createAccounts(
                                        $request->group,
                                        $request->subgroup,
                                        $request->item,
                                        '00',
                                        '00',
                                        '00',
                                        '000',
                                        $request->denomination,
                                        $request->active,
                                        $request->resource,
                                        $request->type,
                                        $parent,
                                        $request->original
                                    );
                                    //1.1.1.01.00.00.000
                                    $level5 = $this->createAccounts(
                                        $request->group,
                                        $request->subgroup,
                                        $request->item,
                                        $request->generic,
                                        '00',
                                        '00',
                                        '000',
                                        $request->denomination,
                                        $request->active,
                                        $request->resource,
                                        $request->type,
                                        $level4,
                                        $request->original
                                    );
                                    //1.1.1.01.02.00.000
                                    $level6 = $this->createAccounts(
                                        $request->group,
                                        $request->subgroup,
                                        $request->item,
                                        $request->generic,
                                        $request->specific,
                                        '00',
                                        '000',
                                        $request->denomination,
                                        $request->active,
                                        $request->resource,
                                        $request->type,
                                        $level5,
                                        $request->original
                                    );
                                    //1.1.1.01.02.01.000
                                    $level7 = $this->createAccounts(
                                        $request->group,
                                        $request->subgroup,
                                        $request->item,
                                        $request->generic,
                                        $request->specific,
                                        $request->subspecific,
                                        '000',
                                        $request->denomination,
                                        $request->active,
                                        $request->resource,
                                        $request->type,
                                        $level6,
                                        $request->original
                                    );
                                    //ahora creo 1.1.1.01.02.01.001
                                    $this->createAccounts(
                                        $request->group,
                                        $request->subgroup,
                                        $request->item,
                                        $request->generic,
                                        $request->specific,
                                        $request->subspecific,
                                        $request->institutional,
                                        $request->denomination,
                                        $request->active,
                                        $request->resource,
                                        $request->type,
                                        $level7,
                                        $request->original
                                    );
                                } else {
                                    //esto esta al dia vas pora aca
                                    //no existe 1.1.0.00.00.00.000 padre
                                    //por lo que solo crearia 1.1.0.00.00.00.
                                    // 000->1.1.1.00.00.00.000->1.1.1.01.00.00
                                    // .000->1.1.1.01.02.00.000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                                    //chequeo si existe el padre de //1.1.0.00.00.00.000 es decir 1.0.0.00.00.00.000
                                    $parent = AccountingAccount::getParent(
                                        $request->group,
                                        $request->subgroup,
                                        '0',
                                        '00',
                                        '00',
                                        '00',
                                        '000'
                                    );
                                    if ($parent) {
                                        //si existe 1.0.0.00.00.00.000
                                        //por lo que solo crearia 1.1.0.00.00.00
                                        // .000->1.1.1.00.00.00.000->1.1.1.01.00
                                        // .00.000->1.1.1.01.02.00.000-> 1.1.1.01.02.01.000-> 1.1.1.01.02.01.001
                                        //1.1.0.00.00.00.000
                                        $level3 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            '0',
                                            '00',
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $parent,
                                            $request->original
                                        );
                                        //1.1.1.00.00.00.000
                                        $level4 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            '00',
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level3,
                                            $request->original
                                        );
                                        //1.1.1.01.00.00.000
                                        $level5 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level4,
                                            $request->original
                                        );
                                        //1.1.1.01.02.00.000
                                        $level6 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            $request->specific,
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level5,
                                            $request->original
                                        );
                                        //1.1.1.01.02.01.000
                                        $level7 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            $request->specific,
                                            $request->subspecific,
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level6,
                                            $request->original
                                        );
                                        //ahora creo 1.1.1.01.02.01.001
                                        $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            $request->specific,
                                            $request->subspecific,
                                            $request->institutional,
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level7,
                                            $request->original
                                        );
                                    } else {
                                        //esto esta al dia vas pora aca
                                        //no existe 1.0.0.00.00.00.000 padre
                                        //si existe 1.0.0.00.00.00.000
                                        //por lo que solo crearia 1.1.0.00.00.00
                                        // .000->1.1.1.00.00.00.000->1.1.1.01.00
                                        // .00.000->1.1.1.01.02.00.000-> 1.1.1.
                                        // 01.02.01.000-> 1.1.1.01.02.01.001
                                        //1.0.0.00.00.00.000
                                        $level2 = $this->createAccounts(
                                            $request->group,
                                            '0',
                                            '0',
                                            '00',
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $parent,
                                            $request->original
                                        );
                                        //1.1.0.00.00.00.000
                                        $level3 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            '0',
                                            '00',
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level2,
                                            $request->original
                                        );
                                        //1.1.1.00.00.00.000
                                        $level4 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            '00',
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level3,
                                            $request->original
                                        );
                                        //1.1.1.01.00.00.000
                                        $level5 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            '00',
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level4,
                                            $request->original
                                        );
                                        //1.1.1.01.02.00.000
                                        $level6 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            $request->specific,
                                            '00',
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level5,
                                            $request->original
                                        );
                                        //1.1.1.01.02.01.000
                                        $level7 = $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            $request->specific,
                                            $request->subspecific,
                                            '000',
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level6,
                                            $request->original
                                        );
                                        //ahora creo 1.1.1.01.02.01.001
                                        $this->createAccounts(
                                            $request->group,
                                            $request->subgroup,
                                            $request->item,
                                            $request->generic,
                                            $request->specific,
                                            $request->subspecific,
                                            $request->institutional,
                                            $request->denomination,
                                            $request->active,
                                            $request->resource,
                                            $request->type,
                                            $level7,
                                            $request->original
                                        );
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // caso qie se cree 1.0.0.00.00.00.000
                    if (
                        $request->subgroup == '0' &&
                        $request->item == '0' &&
                        $request->generic == '00' &&
                        $request->specific == '00' &&
                        $request->subspecific == '00'
                    ) {
                        $this->createAccounts(
                            $request->group,
                            $request->subgroup,
                            $request->item,
                            $request->generic,
                            $request->specific,
                            $request->subspecific,
                            $request->institutional,
                            $request->denomination,
                            $request->active,
                            $request->resource,
                            $request->type,
                            $parent,
                            $request->original
                        );
                    } else {
                        $this->fatherMaker($request);
                    }
                }
            }
        });
        return response()->json(['records' => $this->getAccounts(), 'message' => 'Success']);
    }

    /**
     * Actualiza los datos de la cuenta patrimonial
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request Datos de la petición realizada
     * @param  integer $id      Identificador de la cuenta patrimonial a modificar
     *
     * @return JsonResponse información de la operacion y listado de los registros actualizados
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'group' => ['required', 'digits:1'],
            'subgroup' => ['required', 'digits:1'],
            'item' => ['required', 'digits:1'],
            'generic' => ['required', 'digits:2'],
            'specific' => ['required', 'digits:2'],
            'subspecific' => ['required', 'digits:2'],
            'institutional' => ['required', 'digits:3'],
            'denomination' => ['required'],
            'active' => ['required'],
            'type' => ['nullable'],
            'original' => ['nullable'],
        ]);

        DB::transaction(function () use ($request, $id) {
            /* Datos de la cuenta padre si aplica */
            $parent = AccountingAccount::getParent(
                $request->group,
                $request->subgroup,
                $request->item,
                $request->generic,
                $request->specific,
                $request->subspecific,
                $request->institutional
            );
            /* Datos de la cuenta patrimonial a modificar */
            $account = AccountingAccount::find($id);
            $data = $request->all();
            $data['original'] = $request->original ?? false;
            $data['resource'] = isset($parent->resource)
            ? $parent->resource
            : (isset($request->type) ? ($request->type == 'resource') : null);
            $data['egress'] = isset($parent->egress)
            ? $parent->egress
            : (isset($request->type) ? ($request->type == 'egress') : null);
            $data['inactivity_date'] = (!$request->active) ? date('Y-m-d') : null;
            $data['parent_id'] = ($parent) ? $parent->id : null;
            $account->update($data);
        });

        return response()->json(['records' => $this->getAccounts(), 'message' => 'Success']);
    }

    /**
     * Elimina una cuenta patrimonial
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id Identificador de la cuenta patrimonial a eliminar
     *
     * @return JsonResponse información de la operacion y listado de los registros actualizados
     */
    public function destroy($id)
    {
        /* datos de la cuenta presupuestaria a eliminar */
        $AccountingAccount = AccountingAccount::with('accountable')->find($id);

        if ($AccountingAccount) {
            if (
                count($AccountingAccount->accountable) > 0
                || !is_null(AccountingEntryAccount::where('accounting_account_id', $id)->first())
            ) {
                return response()->json(
                    [
                        'error' => true,
                        'message' => 'No es posible eliminar cuentas que esten' .
                        ' siendo utilizadas en conversiones ó asientos contables.',
                    ],
                    200
                );
            }
            $AccountingAccount->delete();
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Obtiene información de las cuentas hijas
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param integer $parent_id Identificador de la cuenta padre de la cual se va a generar el nuevo código
     *
     * @return JsonResponse
     */
    public function getChildrenAccount($parent_id)
    {
        /* información de la cuenta presupuestaria de nivel superior */
        $parent = AccountingAccount::find($parent_id);

        /* valor del campo subgroup */
        $subgroup = $parent->subgroup;

        /* valor del campo item */
        $item = $parent->item;

        /* valor del campo generic */
        $generic = $parent->generic;

        /* valor del campo specific */
        $specific = $parent->specific;

        /* valor del campo subspecific */
        $subspecific = $parent->subspecific;

        /* valor del campo institutional */
        $institutional = $parent->institutional;

        if ($parent->subgroup === "0") {
            /* almacena registro */
            $currentSubgroup = AccountingAccount::where(['group' => $parent->group])
                ->orderBy('subgroup', 'desc')->first();

            $subgroup = (strlen(intval($currentSubgroup->subgroup)) < 2 && intval($currentSubgroup->subgroup) < 9)
            ? (intval($currentSubgroup->subgroup) + 1) : $currentSubgroup->subgroup;
        } elseif ($parent->item === "0") {
            /* almacena registro */
            $currentItem = AccountingAccount::where(
                [
                    'group' => $parent->group,
                    'subgroup' => $parent->subgroup,
                ]
            )->orderBy('item', 'desc')->first();

            $item = (strlen(intval($currentItem->item)) < 2 && intval($currentItem->item) < 9)
            ? (intval($currentItem->item) + 1) : $currentItem->item;
        } elseif ($parent->generic === "00") {
            /* almacena registro */
            $currentGeneric = AccountingAccount::where(
                [
                    'group' => $parent->group,
                    'subgroup' => $parent->subgroup,
                    'item' => $parent->item,
                ]
            )->orderBy('generic', 'desc')->first();

            $generic = (strlen(intval($currentGeneric->generic)) < 2 || intval($currentGeneric->generic) < 99)
            ? (intval($currentGeneric->generic) + 1) : $currentGeneric->generic;
            $generic = (strlen($generic) === 1) ? "0$generic" : $generic;
        } elseif ($parent->specific === "00") {
            /* almacena registro */
            $currentSpecific = AccountingAccount::where(
                [
                    'group' => $parent->group,
                    'subgroup' => $parent->subgroup,
                    'item' => $parent->item,
                    'generic' => $parent->generic,
                ]
            )->orderBy('specific', 'desc')->first();

            $specific = (strlen(intval($currentSpecific->specific)) < 2 || intval($currentSpecific->specific) < 99)
            ? (intval($currentSpecific->specific) + 1) : $currentSpecific->specific;
            $specific = (strlen($specific) === 1) ? "0$specific" : $specific;
        } elseif ($parent->subspecific === "00") {
            /* almacena registro */
            $currentSubSpecific = AccountingAccount::where(
                [
                    'group' => $parent->group,
                    'subgroup' => $parent->subgroup,
                    'item' => $parent->item,
                    'generic' => $parent->generic,
                    'specific' => $parent->specific,
                ]
            )->orderBy('subspecific', 'desc')->first();

            $subspecific = (strlen(intval($currentSubSpecific->subspecific)) < 2
                || intval($currentSubSpecific->subspecific) < 99)
            ? (intval($currentSubSpecific->subspecific) + 1) : $currentSubSpecific->subspecific;
            $subspecific = (strlen($subspecific) === 1) ? "0$subspecific" : $subspecific;
        } elseif ($parent->institutional === "000") {
            /* almacena registro */
            $currentInstitutional = AccountingAccount::where(
                [
                    'group' => $parent->group,
                    'subgroup' => $parent->subgroup,
                    'item' => $parent->item,
                    'generic' => $parent->generic,
                    'specific' => $parent->specific,
                    'subspecific' => $parent->subspecific,
                ]
            )->orderBy('institutional', 'desc')->first();

            $institutional = (strlen(intval($currentInstitutional->institutional)) < 2
                || intval($currentInstitutional->institutional) < 999)
            ? (intval($currentInstitutional->institutional) + 1) : $currentInstitutional->institutional;

            if (strlen($institutional) === 1) {
                $institutional = "00$institutional";
            } elseif (strlen($institutional) === 2) {
                $institutional = "0$institutional";
            }
        }

        $currentAccount = AccountingAccount::where(
            [
                'group' => $parent->group,
                'subgroup' => ($subgroup > 9) ? 9 : $subgroup,
                'item' => ($item > 9) ? 9 : $item,
                'generic' => ($generic > 99) ? 99 : $generic,
                'specific' => ($specific > 99) ? 99 : $specific,
                'subspecific' => ($subspecific > 99) ? 99 : $subspecific,
                'institutional' => ($institutional > 999) ? 999 : $institutional,
                'active' => true,
            ]
        )->first();

        /* información de la cuenta */
        $account = [
            'code' => (string) $parent->group . '.' . (string) $subgroup . '.' .
            (string) $item . '.' . (string) $generic . '.' .
            (string) $specific . '.' . (string) $subspecific . '.' .
            (string) $institutional,
            'denomination' => $parent->denomination,
            'active' => $parent->active,
            'exist' => ($currentAccount) ? 'is-invalid' : '',
            'type' => (isset($parent->resource) && isset($parent->egress))
            ? (($parent->resource == true) ? 'resource' : 'egress')
            : '',
        ];

        return response()->json(['account' => $account, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los registros de las cuentas patrimoniales
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return AccountingAccount|\Illuminate\Database\Eloquent\Collection|array con la información de las cuentas formateada
     */
    public function getAccounts()
    {
        /* arreglo que almacenara la lista de cuentas patrimoniales */
        $records = AccountingAccount::with('parent')->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('institutional', 'ASC')
            ->get()->map(function ($record) {
                return [
                'id' => $record->id,
                'code' => $record->getCodeAttribute(),
                'denomination' => $record->denomination,
                'active' => $record->active,
                'original' => $record->original,
                'type' => (isset($record->resource) && isset($record->egress))
                ? (($record->resource == true) ? 'resource' : 'egress')
                : '',
                'text' => "{$record->getCodeAttribute()} - {$record->denomination}",
                'parent' => $record->parent,
                ];
            });

        return $records;
    }

    /**
     * Crea una cuenta con los parametros recibidos
     *
     * @author Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @return AccountingAccount con la información de las cuentas formateada
     */
    public function createAccounts(
        $group,
        $subgroup,
        $item,
        $generic,
        $specific,
        $subspecific,
        $institutional,
        $denomination,
        $active,
        $resource,
        $type,
        $parent,
        $original
    ) {
        $records = AccountingAccount::firstOrCreate([
            'group' => $group,
            'subgroup' => $subgroup,
            'item' => $item,
            'generic' => $generic,
            'specific' => $specific,
            'subspecific' => $subspecific,
            'institutional' => $institutional,
        ], [
            'denomination' => $denomination,
            'active' => $active,
            'resource' => isset($parent->resource)
            ? $parent->resource
            : (isset($type) ? ($type == 'R') : null),
            'egress' => isset($parent->egress)
            ? $parent->egress
            : (isset($type) ? ($type == 'E') : null),
            'inactivity_date' => (!$active) ? date('Y-m-d') : null,
            'parent_id' => ($parent) ? $parent->id : null,
            'original' => $original ?? ($parent->original ?? false),
        ]);

        return $records;
    }

    /**
     * Crea las cuentas madres de una cuenta con los parametros recibidos
     *
     * @author Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @return void
     */
    public function fatherMaker($data)
    {
        $i = 1;
        while ($i <= 7) :
            $y = $i + 1;
            $parent = AccountingAccount::getParent(
                $data->group,
                ($y == 2) ? '0' : $data->subgroup,
                ($y <= 3) ? '0' : $data->item,
                ($y <= 4) ? '00' : $data->generic,
                ($y <= 5) ? '00' : $data->specific,
                ($y <= 6) ? '00' : $data->subspecific,
                ($y <= 7) ? '000' : $data->institutional
            );
            $this->createAccounts(
                $data->group,
                ($y == 2) ? '0' : $data->subgroup,
                ($y <= 3) ? '0' : $data->item,
                ($y <= 4) ? '00' : $data->generic,
                ($y <= 5) ? '00' : $data->specific,
                ($y <= 6) ? '00' : $data->subspecific,
                ($y <= 7) ? '000' : $data->institutional,
                $data->denomination,
                $data->active,
                $data->resource,
                $data->type,
                $parent,
                ($y <= 7) ? true : false
            );
            $i++;
        endwhile;
        return;
    }

    /**
     * Lee las filas de un archivo de hoja de calculo
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return     JsonResponse           Objeto que permite descargar el archivo con la información a ser importada
     */
    public function import(Request $request)
    {
        Excel::Import(new AccountingAccountImport(), request()->file('file'));

        return response()->json(['result' => true, 'records' => $this->getAccounts()], 200);
    }

    /**
     * Realiza la acción necesaria para exportar los datos
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    object    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function export()
    {
        return Excel::download(new AccountingAccountExport(), 'accounting_account.xlsx');
    }

    /**
     * Verifica los posibles errores que se pueden presentar en las filas de archivo y
     * agrega un mensaje del error para el usuario
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return array
     */
    public function validatedErrors($record, $currentRow)
    {
        /* almecena los errores en el array */
        $errors = [];

        /* Se valida el formato y que el valor sea entero en el rango de min 0 y max 9 */
        if (!ctype_digit($record['subgrupo'])) {
            array_push($errors, 'La columna subgrupo en la fila ' . $currentRow .
                ' debe ser entero y no debe contener caracteres ni simbolos.');
        }

        if ((int) $record['subgrupo'] > 9 || (int) $record['subgrupo'] < 0) {
            array_push($errors, 'La columna subgrupo en la fila ' . $currentRow .
                ' no cumple con el formato valido, Número entero entre 0 y 9.');
        }

        /* Se valida el formato y que el valor sea entero en el rango de min 0 y max 9 */
        if (!ctype_digit($record['rubro'])) {
            array_push($errors, 'La columna rubro en la fila ' . $currentRow .
                ' debe ser entero y no debe contener caracteres ni simbolos.');
        }
        if ((int) $record['rubro'] > 9 || (int) $record['rubro'] < 0) {
            array_push($errors, 'La columna rubro en la fila ' . $currentRow .
                ' no cumple con el formato valido, Número entero entre 0 y 9.');
        }

        /* Se valida el formato y que el valor sea entero en el rango de min 0 y max 99 */
        if (!ctype_digit($record['n_cuenta_orden'])) {
            array_push($errors, 'La columna n_cuenta_orden en la fila ' . $currentRow .
                ' debe ser entero y no debe contener caracteres ni simbolos.');
        }
        if ((int) $record['n_cuenta_orden'] > 99 || (int) $record['n_cuenta_orden'] < 0) {
            array_push($errors, 'La columna n_cuenta_orden en la fila ' . $currentRow .
                ' no cumple con el formato valido, Número entero entre 0 y 99.');
        }

        /* Se valida el formato y que el valor sea entero en el rango de min 0 y max 99 */
        if (!ctype_digit($record['n_subcuenta_primer_orden'])) {
            array_push($errors, 'La columna n_subcuenta_primer_orden en la fila ' . $currentRow .
                ' debe ser entero y no debe contener caracteres ni simbolos.');
        }
        if ((int) $record['n_subcuenta_primer_orden'] > 99 || (int) $record['n_subcuenta_primer_orden'] < 0) {
            array_push($errors, 'La columna n_subcuenta_primer_orden en la fila ' . $currentRow .
                ' no cumple con el formato valido, Número entero entre 0 y 99.');
        }

        /* Se valida el formato y que el valor sea entero en el rango de min 0 y max 99 */
        if (!ctype_digit($record['n_subcuenta_segundo_orden'])) {
            array_push($errors, 'La columna n_subcuenta_segundo_orden en la fila ' . $currentRow .
                ' debe ser entero y no debe contener caracteres ni simbolos.');
        }
        if ((int) $record['n_subcuenta_segundo_orden'] > 99 || (int) $record['n_subcuenta_segundo_orden'] < 0) {
            array_push($errors, 'La columna n_subcuenta_segundo_orden en la fila ' . $currentRow .
                ' no cumple con el formato valido, Número entero entre 0 y 99.');
        }

        /* Se valida que el valor en la columna de activa */
        if (strtolower($record['activa']) != 'si' && strtolower($record['activa']) != 'no') {
            array_push($errors, 'La columna activa en la fila ' . $currentRow .
                ' no cumple con el formato valido, SI ó NO.');
        }

        return $errors;
    }
}
