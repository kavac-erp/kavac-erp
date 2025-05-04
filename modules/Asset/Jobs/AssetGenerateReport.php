<?php

namespace Modules\Asset\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Modules\Asset\Pdf\AssetReport as ReportRepository;
use Modules\Asset\Models\AssetReport;
use Modules\Asset\Models\Asset;
use App\Models\Institution;
use App\Models\Parameter;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Modules\Asset\Mail\AssetSendMail;

/**
 * @class AssetGenerateReport
 * @brief Gestiona las tareas en la generación de reportes de bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetGenerateReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Objeto que contiene la información asociada a la solicitud
     *
     * @var object $asset
     */
    protected $data;

    /**
     * Plantilla o texto a incluir en el cuerpo del reporte
     *
     * @var string $body
     */
    protected $body;

    /**
     * Objeto que contiene el código interno asociada a la solicitud
     *
     * @var object $code
     */
    protected $code;

    /**
     * Título del reporte
     *
     * @var string $title
     */
    protected $title;

    /**
     * Subtítulo o descripción del reporte
     *
     * @var string $subtitle
     */
    protected $subtitle;

    /**
     * Operación a realizar al finalizar el trabajo
     *
     * @var string $operation
     */
    protected $operation;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia del trabajo
     *
     * @return void
     */
    public function __construct(
        AssetReport $data,
        string $body,
        string $code = null,
        string $title = null,
        protected ?int $userId = null,
        protected ?string $reportCode = null
    ) {
        $this->data = $data;
        $this->body = $body;
        $this->title = ($this->data->type_report == 'general'
            ? 'Reporte de Bienes'
            : ($this->data->type_asset == 'furniture_active'
                ? 'Reporte de Bienes Muebles'
                : ($this->data->type_asset == 'property_active'
                    ? 'Reporte de Bienes Inmuebles'
                    : ($this->data->type_asset == 'vehicle_active'
                        ? 'Reporte de Bienes Vehiculares'
                        : ($this->data->type_asset == 'livestock_active'
                            ? 'Reporte de Bienes Semovientes'
                            : ''
                        )
                    )
                )
            )
        );
        $this->code = $code;
    }

    /**
     * Ejecuta el trabajo
     *
     * @return void
     */
    public function handle()
    {
        if ($this->data->type_report == 'general') {
            $assets = Asset::where('institution_id', $this->data->institution_id)
                ->with(
                    'institution',
                    'assetCondition',
                    'assetStatus',
                    'assetSpecificCategory',
                    'assetAsignationAsset.assetAsignation.payrollStaff',
                    'department'
                );

            /* filtro por periodo de tiempo */
            if ($this->data->start_date || $this->data->end_date) {
                if ($this->data->start_date != '' && !is_null($this->data->start_date)) {
                    if ($this->data->end_date != '' && !is_null($this->data->end_date)) {
                        $assets = $assets->whereBetween("created_at", [$this->data->start_date, $this->data->end_date]);
                    } else {
                        $assets = $assets->whereBetween("created_at", [$this->data->start_date, now()]);
                    }
                }
                if ($this->data->asset_status_id > 0) {
                    $assets = $assets->where('asset_status_id', $this->data->asset_status_id);
                }
            } elseif ($this->data->year || $this->data->mes) { /* filtro por mes y año */
                if ($this->data->mes != '' && !is_null($this->data->mes)) {
                    if ($this->data->year != '' && !is_null($this->data->year)) {
                        $assets = $assets->whereMonth('created_at', $this->data->mes)
                            ->whereYear('created_at', $this->data->year);
                    } else {
                        $assets = $assets->whereMonth('created_at', $this->data->mes);
                    }
                }

                if ($this->data->year != '' && !is_null($this->data->year) && $this->data->mes == '') {
                    $assets = $assets->whereYear('created_at', $this->data->year);
                }

                if ($this->data->asset_status_id > 0) {
                    $assets = $assets->where('asset_status_id', $this->data->asset_status_id);
                }
            } else {
                if ($this->data->asset_status_id > 0) {
                    $assets = $assets->where('asset_status_id', $this->data->asset_status_id);
                }
            }
            $assets = $assets->get();
        } elseif ($this->data->type_report == 'clasification') {
            $assets = Asset::where('institution_id', $this->data->institution_id)
                ->with(
                    'institution',
                    'assetCondition',
                    'assetStatus',
                    'assetSpecificCategory',
                    'assetAsignationAsset.assetAsignation.payrollStaff',
                    'department'
                );
            if ($this->data->type_search != '') {
                $assets = $assets->dateclasification(
                    $this->data->start_date,
                    $this->data->end_date,
                    $this->data->mes,
                    $this->data->year_id
                );
            } elseif ($this->data->asset_type_id != '') {
                $assets = $assets->where('institution_id', $this->data->institution_id)->CodeClasification(
                    $this->data->asset_type_id,
                    $this->data->asset_category_id,
                    $this->data->asset_subcategory_id,
                    $this->data->asset_specific_category_id
                );

                if ($this->data->asset_status_id > 0) {
                    $assets = $assets->where('asset_status_id', $this->data->asset_status_id);
                }
            } else {
                if ($this->data->asset_status_id > 0) {
                    $assets = $assets->where('asset_status_id', $this->data->asset_status_id);
                }
            }
            /* filtro por periodo de tiempo */
            if ($this->data->start_date || $this->data->end_date) {
                if ($this->data->start_date != '' && !is_null($this->data->start_date)) {
                    if ($this->data->end_date != '' && !is_null($this->data->end_date)) {
                        $assets = $assets->whereBetween("created_at", [$this->data->start_date, $this->data->end_date]);
                    } else {
                        $assets = $assets->whereBetween("created_at", [$this->data->start_date, now()]);
                    }
                }
                if ($this->data->asset_status_id > 0) {
                    $assets = $assets->where('asset_status_id', $this->data->asset_status_id);
                }
            } elseif ($this->data->year || $this->data->mes) { /* filtro por mes y año */
                if ($this->data->mes != '' && !is_null($this->data->mes)) {
                    if ($this->data->year != '' && !is_null($this->data->year)) {
                        $assets = $assets->whereMonth('created_at', $this->data->mes)
                            ->whereYear('created_at', $this->data->year);
                    } else {
                        $assets = $assets->whereMonth('created_at', $this->data->mes);
                    }
                }

                if ($this->data->year != '' && !is_null($this->data->year) && $this->data->mes == '') {
                    $assets = $assets->whereYear('created_at', $this->data->year);
                }
            }

            $assets = $assets->get();

            $assetsA = [];
            foreach ($assets as $asset) {
                if ($this->data->type_asset != '') {
                    if ($this->data->type_asset == 'furniture_active') {
                        if (
                            str_contains(strtolower($asset['assetType']['name']), 'mueble') &&
                            !str_contains(strtolower($asset['assetCategory']['name']), 'transporte') &&
                            !str_contains(strtolower($asset['assetCategory']['name']), 'semoviente') &&
                            !str_contains(strtolower($asset['assetType']['name']), 'inmueble')
                        ) {
                            if ($this->code != '') {
                                $asset = $assets->where('asset_institutional_code', $this->code)->first();
                                if ($asset != null && array_key_exists('serial', $asset['asset_details'])) {
                                    array_push($assetsA, $asset);
                                    break;
                                }
                            } else {
                                array_push($assetsA, $asset);
                            }
                        }
                    } elseif ($this->data->type_asset == 'property_active') {
                        if (str_contains(strtolower($asset['assetType']['name']), 'inmueble')) {
                            if ($this->code != '') {
                                $asset = $assets->where('asset_institutional_code', $this->code)->first();
                                if ($asset != null && array_key_exists('construction_year', $asset['asset_details'])) {
                                    array_push($assetsA, $asset);
                                    break;
                                }
                            } else {
                                array_push($assetsA, $asset);
                            }
                        }
                    } elseif ($this->data->type_asset == 'vehicle_active') {
                        if (
                            str_contains(strtolower($asset['assetType']['name']), 'mueble') &&
                            str_contains(strtolower($asset['assetCategory']['name']), 'transporte') &&
                            !str_contains(strtolower($asset['assetCategory']['name']), 'semoviente') &&
                            !str_contains(strtolower($asset['assetType']['name']), 'inmueble')
                        ) {
                            if ($this->code != '') {
                                $asset = $assets->where('asset_institutional_code', $this->code)->first();
                                if ($asset != null && array_key_exists('license_plate', $asset['asset_details'])) {
                                    array_push($assetsA, $asset);
                                    break;
                                }
                            } else {
                                array_push($assetsA, $asset);
                            }
                        }
                    } elseif ($this->data->type_asset == 'livestock_active') {
                        if (
                            str_contains(strtolower($asset['assetType']['name']), 'mueble') &&
                            !str_contains(strtolower($asset['assetCategory']['name']), 'transporte') &&
                            str_contains(strtolower($asset['assetCategory']['name']), 'semoviente') &&
                            !str_contains(strtolower($asset['assetType']['name']), 'inmueble')
                        ) {
                            if ($this->code != '') {
                                $asset = $assets->where('asset_institutional_code', $this->code)->first();
                                if ($asset != null && array_key_exists('race', $asset['asset_details'])) {
                                    array_push($assetsA, $asset);
                                    break;
                                }
                            } else {
                                array_push($assetsA, $asset);
                            }
                        }
                    }
                }
            }
            $assets = $assetsA;
        }

        $multi_inst = Parameter::where('p_key', 'multi_institution')
            ->where('active', true)->first();
        $institution = Institution::where('default', true)
            ->where('active', true)->first();
        $pdf = new ReportRepository();

        $meses = [
            0 => 'Todos',
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiempre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        /* Definicion de las características generales de la página */

        $filename = $this->data->code
            ? 'asset-report-' . $this->data->code . '.pdf'
            : 'asset-report-' . Carbon::now() . '.pdf';

        $pdf->setConfig(
            [
                'institution' => $institution,
                'start_date' => $this->data->type_search != ''
                    ? (
                        $this->data->type_search == 'date'
                        ? $this->data->start_date
                        : $meses[$this->data->mes]
                    )
                    : '',
                'end_date' => $this->data->type_search != ''
                    ? (
                        $this->data->type_search == 'date'
                        ? $this->data->end_date
                        : $this->data->year
                    )
                    : '',
                'orientation' => 'L',
                'urlVerify' => url(''),
                'filename' => $filename
            ]
        );
        $month = [
            '1' => 'Enero',
            '2' => 'Febrero',
            '3' => 'Marzo',
            '4' => 'Abril',
            '5' => 'Mayo',
            '6' => 'Junio',
            '7' => 'Julio',
            '8' => 'Agosto',
            '9' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];
        $month_name = $this->data->mes ? $month[$this->data->mes] : '';

        $pdf->setHeader(
            $this->title,
            $subTitle = ($this->data->start_date
                ? 'DESDE: ' . $this->data->start_date . ' HASTA: ' . $this->data->end_date
                : ($this->data->mes
                    ? 'DESDE: ' . $month_name . ' HASTA: ' . $this->data->year
                    : ''
                )
            ),
            $subTitleAlign = 'C'
        );
        $pdf->setFooter();
        $pdf->setBody(
            $this->body,
            true,
            [
                'pdf' => $pdf,
                'assets' => $assets
            ]
        );

        $pdfPath = storage_path() . '/reports/asset-report-' . $this->reportCode . '.pdf';

        $user = User::find($this->userId);

        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Se ha generado el reporte de bienes, '
                    . 'el archivo ha sido enviado a su correo electrónico',
                )
            );

            $email = $user->email;
        }

        $mailable = new AssetSendMail($pdfPath, 'Se ha generado el reporte de bienes');

        Mail::to($email)->send($mailable);
    }

    /**
     * Elimina un reporte si falla el proceso
     *
     * @return void
     */
    public function failed()
    {
        $report = AssetReport::find($this->data->id);
        $report->delete();
    }
}
