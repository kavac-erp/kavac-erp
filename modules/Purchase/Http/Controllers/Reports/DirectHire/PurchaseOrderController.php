<?php

namespace Modules\Purchase\Http\Controllers\Reports\DirectHire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Purchase\Http\Controllers\Reports\Base\ReportRepository;
use Modules\Purchase\Models\PurchaseDirectHire;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Models\FiscalYear;
use Modules\Purchase\Models\Pivot;
use App\Models\Profile;
use App\Models\Institution;
use App\Models\CodeSetting;
use Auth;
use Illuminate\Support\Facades\Log;

/**
 * @class PurchaseStartCertificateController
 * @brief Controlador para la generación del reporte de acta de inicio de una contratacion directa
 *
 * Clase que gestiona de la generación del reporte de acta de inicio de una contratacion directa
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 * @copyright <a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *                LICENCIA DE SOFTWARE CENDITEL</a>
 */
class PurchaseOrderController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
    }

    /**
     * vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param Int $id id del asiento contable
    */
    public function pdf($id)
    {
        $user_profile = Profile::with('institution')
            ->where('user_id', auth()
            ->user()
            ->id)
            ->first();
        $is_admin = $user_profile == null || $user_profile['institution_id']
            == null ? true : false;
        if ($is_admin) {
            $purchaseDirectHire = PurchaseDirectHire::with([
                'contratingDepartment',
                'currency',
                'preparedBy' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'reviewedBy' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'verifiedBy' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'firstSignature' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'secondSignature' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'userDepartment',
                'quatations' =>  function ($query) {
                    $query->with('currency')->get();
                    $query->with(['relatable' => function ($query) {
                        $query->with(['purchaseRequirementItem' => function ($query) {
                            $query->with('purchaseRequirement')->get();
                            $query->with('historyTax')->get();
                        }])->get();
                    }])->get();
                },
                'purchaseSupplier'
            ])->find($id);

            // Enviando las cuentas presupuestarias de gastos al reporte PDF
            foreach ($purchaseDirectHire['quatations'] as $x) {
                $purchaseDirectHire['base_budget'] = Pivot::where(
                    'recordable_type',
                    PurchaseQuotation::class
                )
                ->where('recordable_id', $x->id)
                ->with('relatable')
                ->get();
            }
        } else {
            $purchaseDirectHire = PurchaseDirectHire::with([
                'contratingDepartment',
                'currency',
                'preparedBy' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'reviewedBy' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'verifiedBy' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'firstSignature' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'secondSignature' =>  function ($query) {
                    $query->with(['payrollStaff' => function ($query) {
                    }])->get();
                },
                'preparedBy',
                'reviewedBy',
                'verifiedBy',
                'firstSignature',
                'secondSignature',
                'userDepartment',
                'quatations' =>  function ($query) {
                    $query->with('currency')->get();
                    $query->with(['relatable' => function ($query) {
                        $query->with(['purchaseRequirementItem' => function ($query) {
                            $query->with('purchaseRequirement')->get();
                            $query->with('historyTax')->get();
                        }])->get();
                    }]);
                },
                'purchaseSupplier'
            ])
            ->where('institution_id', $user_profile['institution_id'])
            ->find($id);

            // Enviando las cuentas presupuestarias de gastos al reporte PDF
            foreach ($purchaseDirectHire['quatations'] as $x) {
                $purchaseDirectHire['base_budget'] = Pivot::where('recordable_type', PurchaseQuotation::class)
                ->where('recordable_id', $x->id)
                ->with('relatable')
                ->get();
            }
        }

        $timeFrame = [
            'delivery' => 'Entrega inmediata',
            'day'      => 'día(s)',
            'week'     => 'semana(s)',
            'month'    => 'mes(es)',
        ];
        //Se adapta el fot¿rmato de los datos del plazo de entrega
        $due_date = json_decode($purchaseDirectHire->due_date, true);
        $time_frame = array_keys($due_date);
        $purchaseDirectHire->due_date = $due_date[$time_frame[0]];
        $purchaseDirectHire->time_frame = $timeFrame[$time_frame[0]];

        /**
         * Base para generar el pdf]
         */
        $pdf = new ReportRepository();

        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        $institution = null;

        if ($is_admin) {
            $institution = Institution::find($purchaseDirectHire->institution_id);
        } else {
            $institution = Institution::find($user_profile['institution_id']);
        }
        $title = $institution->name . '. ' . $institution->acronym . '.' . ' Rif: ' . $institution->rif;

        $pdf->setConfig(
            [
                'institution' => $institution,
                'reportDate' => now()->format('d-m-Y'),
                'urlVerify' => url('/purchase/direct_hire/purchase_order/pdf/' . $id)
            ]
        );
        $code = explode('-', $purchaseDirectHire->code);
        $codeSettingOrder = CodeSetting::where("model", PurchaseDirectHire::class)->first();
        $codeSettingService = CodeSetting::where('table', 'purchase_service_orders')->first();

        if ($code[0] == $codeSettingService->format_prefix) {
            $orderTitle = 'ORDEN DE SERVICIO';
        } elseif ($code[0] == $codeSettingOrder->format_prefix) {
            $orderTitle = 'ORDEN DE COMPRA';
        } else {
            $orderTitle = 'ORDEN DE COMPRA / SERVICIO';
        }
        $pdf->setHeader($title, $orderTitle);
        $pdf->setFooter();
        $pdf->setBody('purchase::pdf.direct_hire.purchase_order', true, [
            'pdf'    => $pdf,
            'record' => $purchaseDirectHire
        ]);
    }

    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }
}
