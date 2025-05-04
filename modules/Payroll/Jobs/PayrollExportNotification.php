<?php

namespace Modules\Payroll\Jobs;

use App\Exports\MultiSheetExport;
use App\Models\User;
use App\Notifications\System;
use App\Notifications\SystemNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Exports\EmploymentStaffExportFromButton;
use Modules\Payroll\Exports\FinancialStaffExportFromButton;
use Modules\Payroll\Exports\PayrollStaffAccountExport;
use Modules\Payroll\Exports\ProfessionalStaffExportFromButton;
use Modules\Payroll\Exports\Sheets\PayrollEmploymentValidationExport;
use Modules\Payroll\Exports\Sheets\PayrollFinancialValidationExport;
use Modules\Payroll\Exports\Sheets\PayrollProfessionalValidationExport;
use Modules\Payroll\Exports\Sheets\PayrollSocioeconomicValidationExport;
use Modules\Payroll\Exports\Sheets\PayrollStaffAccountValidationExport;
use Modules\Payroll\Exports\Sheets\PayrollStaffValidationExport;
use Modules\Payroll\Exports\SocioStaffExportFromButton;
use Modules\Payroll\Exports\StaffExportFromButton;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollFinancial;
use Modules\Payroll\Models\PayrollProfessional;
use Modules\Payroll\Models\PayrollSocioeconomic;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollStaffAccount;

/**
 * @class PayrollExportNotification
 * @brief Trabajo que se encarga de enviar notificaciones de exportación de archivos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollExportNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Variable que contiene el tiempo de espera para la ejecución del trabajo,
     * si no se quiere limite de tiempo, se define en 0
     *
     * @var integer $timeout
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct(
        protected ?int $userId = null,
        protected string $sheetName = ''
    ) {
        if ('local' !== @env('APP_ENV')) {
            $this->onQueue('bulk');
        }
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        $worksheet = new MultiSheetExport();
        $shets = match ($this->sheetName) {
            'Datos Personales' => $this->getStaffShets(),
            'Datos Profesionales' => $this->getProfessionalShets(),
            'Datos Socioeconomicos' => $this->getSocioeconomicShets(),
            'Datos Laborales' => $this->getEmploymentShets(),
            'Datos Financieros' => $this->getFinancialShets(),
            'Datos Contables' => $this->getAccountingShets(),
            default => []
        };

        $worksheet->setSheets($shets);

        $excelFiles = [
            [
                'file' => Excel::raw($worksheet, ExcelExcel::XLSX),
                'fileName' => Str::snake($this->sheetName ?? 'Datos', '-') . '.xlsx',
            ]
        ];

        $user = User::without(['roles', 'permissions'])->where('id', $this->userId)->first();

        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Ha finalizado la exportación de los ' . Str::lower($this->sheetName) . ', '
                    . 'el archivo ha sido enviado a su correo electrónico',
                )
            );

            $user->notify(
                new System(
                    Str::title(Str::lower($this->sheetName)),
                    'Talento Humano',
                    'Se ha realizado la exportación de los ' . Str::lower($this->sheetName),
                    true,
                    $excelFiles
                )
            );
        }
    }

    /**
     * Obtiene la hoja de datos del expediente del personal
     *
     * @return array
     */
    protected function getEmploymentShets(): array
    {
        return [
            'Datos Laborales' => new EmploymentStaffExportFromButton(new PayrollEmployment()),
            'validation' => new PayrollEmploymentValidationExport()
        ];
    }

    protected function getProfessionalShets(): array
    {
        return [
            'Datos Profesionales' => new ProfessionalStaffExportFromButton(new PayrollProfessional()),
            'validation' => new PayrollProfessionalValidationExport()
        ];
    }

    /**
     * Obtiene la hoja de datos de la información socioeconómica del personal
     *
     * @return array
     */
    protected function getSocioeconomicShets(): array
    {
        $data = [];
        PayrollSocioeconomic::all()->map(function ($payrollSocioeconomic) use (&$data) {

            array_push($data, [
                'payroll_staff_id_number' => $payrollSocioeconomic->payrollStaff->id_number,
                'marital_status' => $payrollSocioeconomic->maritalStatus?->name,
                'student' => null,
                'disability' => null

            ]);
            $data = array_merge($data, $payrollSocioeconomic['payrollChildrens']
                ->map(function ($model) use ($payrollSocioeconomic) {
                    return [
                        'payroll_staff_id_number' => $payrollSocioeconomic->payrollStaff->id_number,
                        'marital_status' => '',
                        'first_name' => $model->first_name,
                        'last_name' => $model->last_name,
                        'payroll_relationship' => $model->payrollRelationship?->name,
                        'id_number' => $model->id_number,
                        'birthdate' => $model->birthdate,
                        'address' => $model->address,
                        'gender' => $model->payrollGender?->name,
                        'disability' => $model->has_disability ? $model->payrollDisability?->name : [],
                        'student' => $model->is_student ? [
                            'study_center' => $model->study_center,
                            'payroll_schooling_level' => $model->payrollSchoolingLevel?->name,
                            'scholarships' => $model->has_scholarships
                                ? ['scholarship_type' => $model->payrollScholarshipType?->name]
                                : [],
                        ] : []

                    ];
                })->toArray());
        });

        return [
            'Datos Socioeconomicos' => new SocioStaffExportFromButton(collect($data)),
            'validation' => new PayrollSocioeconomicValidationExport(),
        ];
    }

    /**
     * Obtiene la hoja de datos de la información laboral del personal
     *
     * @return array
     */
    protected function getStaffShets(): array
    {
        return [
            'Datos Personales' => new StaffExportFromButton(new PayrollStaff()),
            'validation' => new PayrollStaffValidationExport()
        ];
    }

    /**
     * Obtiene la hoja de datos de la información financiera del personal
     *
     * @return array
     */
    protected function getFinancialShets(): array
    {
        return [
            'Datos Financieros' => new FinancialStaffExportFromButton(new PayrollFinancial()),
            'validation' => new PayrollFinancialValidationExport()
        ];
    }

    /**
     * Obtiene la hoja de datos de la información contable del personal
     *
     * @return array
     */
    protected function getAccountingShets(): array
    {
        return [
            'Datos Contables' => new PayrollStaffAccountExport(new PayrollStaffAccount()),
            'validation' => new PayrollStaffAccountValidationExport()
        ];
    }
}
