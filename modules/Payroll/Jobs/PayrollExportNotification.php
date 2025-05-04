<?php

/** [descripción del namespace] */

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
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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
     * @var int
     */
    public $timeout = 0;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @method __construct
     *
     * @return void
     */
    public function __construct(
        protected ?int $userId = null,
        protected string $sheetName = ''
    )
    {
        //
    }

    /**
     * Ejecuta el trabajo.
     *
     * @method handle
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

        $user = User::find($this->userId);

        if ($user) {
            $user->notify(
                new SystemNotification(
                    'Exito',
                    'Ha finalizado la exportación de los '. Str::lower($this->sheetName) . ', '
                    . 'el archivo ha sido enviado a su correo electrónico',
                )
            );

            $user->notify(
                new System(
                    Str::title(Str::lower($this->sheetName)),
                    'Talento Humano',
                    'Se ha realizado la exportación de los '. Str::lower($this->sheetName),
                    true,
                    $excelFiles
                )
            );
        }
    }

    protected function getEmploymentShets(): array
    {
        return [
            'Datos Laborales' => new EmploymentStaffExportFromButton(PayrollEmployment::class),
            'validation' => new PayrollEmploymentValidationExport()
        ];
    }

    protected function getProfessionalShets(): array
    {
        return [
            'Datos Profesionales' => new ProfessionalStaffExportFromButton(PayrollProfessional::class),
            'validation' => new PayrollProfessionalValidationExport()
        ];
    }

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

    protected function getStaffShets(): array
    {
        return [
            'Datos Personales' => new StaffExportFromButton(PayrollStaff::class),
            'validation' => new PayrollStaffValidationExport()
        ];
    }

    protected function getFinancialShets(): array
    {
        return [
            'Datos Financieros' => new FinancialStaffExportFromButton(PayrollFinancial::class),
            'validation' => new PayrollFinancialValidationExport()
        ];
    }

    protected function getAccountingShets(): array
    {
        return [
            'Datos Contables' => new PayrollStaffAccountExport(PayrollStaffAccount::class),
            'validation' => new PayrollStaffAccountValidationExport()
        ];
    }

}
