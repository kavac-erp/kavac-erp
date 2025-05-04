<?php

namespace App\Http\Controllers;

use App\Models\Receiver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;

/**
 * @class ReceiverController
 * @brief Gestiona información de receptores
 *
 * Controlador para gestionar a los receptores de procesos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ReceiverController extends Controller
{
    /**
     * Obtiene el listado de receptores de procesos
     *
     * @return JsonResponse
     */
    public function index()
    {
        $data = [['id' => '', 'text' => 'Seleccione...']];
        $groups = Receiver::select('group')->groupBy('group')->orderBy('group')->get();
        foreach ($groups as $g) {
            $childrens = Receiver::select('id', 'description AS text')
                                 ->where('group', $g->group)->toBase()->get()->toArray();
            array_push($data, ['text' => $g->group, 'children' => $childrens]);
        }

        return response()->json(['records' => $data], 200);
    }

    /**
     * Muestra el formulario para crear registros
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena un nuevo registro
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra el recurso solicitado
     *
     * @param  \App\Models\Receiver  $receiver
     *
     * @return void
     */
    public function show(Receiver $receiver)
    {
        //
    }

    /**
     * Muestra el formulario para la actualización de datos
     *
     * @param  \App\Models\Receiver  $receiver
     *
     * @return void
     */
    public function edit(Receiver $receiver)
    {
        //
    }

    /**
     * Actualiza información registrada
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Receiver  $receiver
     *
     * @return void
     */
    public function update(Request $request, Receiver $receiver)
    {
        //
    }

    /**
     * Elimina el registro solicitado
     *
     * @param  \App\Models\Receiver  $receiver
     *
     * @return void
     */
    public function destroy(Receiver $receiver)
    {
        //
    }

    /**
     * Muestra una lista de todos los beneficiarios registrados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return JsonResponse
     */
    public function getAllReceivers(Request $request)
    {
        $existPurchase = Module::has('Purchase') && Module::isEnabled('Purchase');
        $existPayroll = Module::has('Payroll') && Module::isEnabled('Payroll');
        $existSale = Module::has('Sale') && Module::isEnabled('Sale');
        $existBudget = Module::has('Budget') && Module::isEnabled('Budget');
        $existFinance = Module::has('Finance') && Module::isEnabled('Finance');
        $parents = [];
        $data = [];
        $query = $request->input('query');

        if (!$query) {
            return response()->json($data);
        }

        if ($existPurchase) {
            $suppliers = \Modules\Purchase\Models\PurchaseSupplier::where('name', 'like', '%' . $query . '%')->get();

            foreach ($suppliers as $supplier) {
                if (!array_key_exists('proveedores', $parents)) {
                    $parents['proveedores'] =
                    [
                        'label' => 'Proveedores',

                        'group' => [
                            0 => [
                                'id' => $supplier->id,
                                'text' => $supplier->name,
                                'class' => \Modules\Purchase\Models\PurchaseSupplier::class,
                                'group' => 'Proveedores',
                                'accounting_account_id' => $supplier->accounting_account_id
                            ]
                        ]
                    ];
                } else {
                    $parents['proveedores']['group'][] =
                    [
                        'id' => $supplier->id,
                        'text' => $supplier->name,
                        'class' => \Modules\Purchase\Models\PurchaseSupplier::class,
                        'group' => 'Proveedores',
                        'accounting_account_id' => $supplier->accounting_account_id
                    ];
                }
            }
        }

        if ($existPayroll) {
            $employeers = \Modules\Payroll\Models\PayrollEmployment::with('payrollStaff.payrollStaffAccount')
                                    ->whereHas('payrollStaff', function ($q) use ($query) {
                                        $q->where('last_name', 'like', '%' . $query . '%')
                                        ->orWhere('first_name', 'like', '%' . $query . '%')
                                        ->orWhere('id_number', 'like', '%' . $query . '%')
                                        ->orWhere('passport', 'like', '%' . $query . '%');
                                    })->get();

            foreach ($employeers as $employment) {
                $text = ($employment['payrollStaff']['id_number'] ?? $employment['payrollStaff']['passport'])
                        . ' - ' . $employment['payrollStaff']['fullname'];
                if (!array_key_exists('trabajadores', $parents)) {
                    $parents['trabajadores'] =
                    [
                        'label' => 'Trabajadores',

                        'group' => [
                            0 => [
                                'id' => $employment->id,
                                'text' => $text,
                                'class' => \Modules\Payroll\Models\PayrollEmployment::class,
                                'group' => 'Trabajadores',
                                'accounting_account_id' => $employment
                                    ->payrollStaff
                                    ->payrollStaffAccount
                                    ->accounting_account_id
                                    ?? null
                            ]
                        ]
                    ];
                } else {
                    $parents['trabajadores']['group'][] =
                    [
                        'id' => $employment->id,
                        'text' => $text,
                        'class' => \Modules\Payroll\Models\PayrollEmployment::class,
                        'group' => 'Trabajadores',
                        'accounting_account_id' => $employment
                            ->payrollStaff
                            ->payrollStaffAccount
                            ->accounting_account_id
                            ?? null
                    ];
                }
            }

            $receivers = Receiver::query()
                ->whereHas('sources', function ($query) {
                    $query->where('sourceable_type', \Modules\Payroll\Models\PayrollConcept::class);
                })
                ->select('id', 'description', 'group', 'receiverable_type', 'associateable_id', 'associateable_type')
                ->distinct('description', 'associateable_id')
                ->get();

            foreach ($receivers as $receiver) {
                $text = $receiver->description . (!empty($receiver->associateable?->code)
                    ? (' - ' . $receiver->associateable->code)
                    : '');
                if (!array_key_exists('otros', $parents)) {
                    $parents['otros'] =
                    [
                        'label' => 'Otros',

                        'group' => [
                            0 => [
                                'id' => $receiver->id,
                                'text' => $text,
                                'class' => null,
                                'group' => $receiver->group,
                                'description' => $receiver->description ?? '',
                                'accounting_account_id' => $receiver->associateable_id ?? null,
                                'accounting_account' => $receiver->associateable?->code ?? ''
                            ]
                        ]
                    ];
                } else {
                    $parents['otros']['group'][] =
                    [
                        'id' => $receiver->id,
                        'text' => $text,
                        'class' => null,
                        'group' => $receiver->group,
                        'description' => $receiver->description ?? '',
                        'accounting_account_id' => $receiver->associateable_id ?? null,
                        'accounting_account' => $receiver->associateable?->code ?? ''
                    ];
                }
            }
        }

        if ($existSale) {
            $clients = \Modules\Sale\Models\SaleClient::where('name', 'like', '%' . $query . '%')->get();

            foreach ($clients as $client) {
                if (!array_key_exists('clientes', $parents)) {
                    $parents['clientes'] =
                    [
                        'label' => 'Clientes',

                        'group' => [
                            0 => [
                                'id' => $client->id,
                                'text' => $client->name,
                                'class' => \Modules\Sale\Models\SaleClient::class,
                                'group' => 'Clientes',
                                'accounting_account_id' => null
                            ]
                        ]
                    ];
                } else {
                    $parents['clientes']['group'][] =
                    [
                        'id' => $client->id,
                        'text' => $client->name,
                        'class' => \Modules\Sale\Models\SaleClient::class,
                        'group' => 'Clientes',
                        'accounting_account_id' => null
                    ];
                }
            }
        }

        if ($existBudget || $existFinance) {
            $receivers = Receiver::where('receiverable_type', \Modules\Budget\Models\BudgetCompromise::class)
                ->orWhere('receiverable_type', \Modules\Finance\Models\FinancePaymentDeduction::class)
                ->get();

            foreach ($receivers as $receiver) {
                if (!array_key_exists('otros', $parents)) {
                    $parents['otros'] =
                    [
                        'label' => 'Otros',

                        'group' => [
                            0 => [
                                'id' => $receiver->id,
                                'text' => $receiver->description,
                                'class' => $receiver->receiverable_type,
                                'group' => $receiver->group,
                                'accounting_account_id' => $receiver->associateable_id ?? null
                            ]
                        ]
                    ];
                } else {
                    $parents['otros']['group'][] =
                    [
                        'id' => $receiver->id,
                        'text' => $receiver->description,
                        'class' => $receiver->receiverable_type,
                        'group' => $receiver->group,
                        'accounting_account_id' => $receiver->associateable_id ?? null
                    ];
                }
            }
        }

        foreach ($parents as $parent) {
            array_push($data, $parent);
        }

        return response()->json($data);
    }
}
