<?php

namespace Modules\Payroll\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Modules\Payroll\Models\PayrollVacationRequest;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

/**
 * @class PayrollVacationRequestExport
 * @brief Clase que exporta el listado de solicitudes de vacaciones
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationRequestExport implements
    FromQuery,
    ShouldQueue,
    WithHeadings,
    ShouldAutoSize,
    WithColumnFormatting,
    WithMapping
{
    use Exportable;

    /**
     * Método constructor de la clase
     *
     * @param string $institution Nombre de la institución
     *
     * @return void
     */
    public function __construct(protected string $institution)
    {
        //
    }

    /**
     * Genera el listado de solicitudes de vacaciones
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $columns = [
            'payroll_staff_id',
            'created_at',
            'vacation_period_year',
            'start_date',
            'end_date',
            'days_requested'
        ];

        return PayrollVacationRequest::query()->with('payrollStaff')->select($columns);
    }

    /**
     * Determina la cantidad de filas por archivo
     *
     * @return integer
     */
    public function chunkById()
    {
        return 25;
    }

    /**
     * Encabezados de la hoja
     *
     * @return array
     */

    public function headings(): array
    {
        return [
            'Cédula del Trabajador',
            'Fecha de la Solicitud',
            'Organización',
            'Años del Periodo Vacacional',
            'Fecha de Inicio de Vacaciones',
            'Fecha de Culminación de Vacaciones',
            'Días Solicitados'
        ];
    }

    /**
     * Formatea las columnas de la hoja
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G' => NumberFormat::FORMAT_NUMBER
        ];
    }

    /**
     * Mapea los datos de la hoja
     *
     * @param array|object $row Datos de la fila
     *
     * @return array
     */
    public function map($row): array
    {
        $vacationYears = '';

        collect(
            json_decode(
                $row->vacation_period_year,
                true
            )
        )->pluck('id')->each(function (&$year) use (&$vacationYears) {
            $vacationYears .= $year . ",";
        });

        $vacationYears = rtrim($vacationYears, ",");

        return [
            $row->payrollStaff->id_number,
            Date::dateTimeToExcel(Carbon::parse($row->created_at)),
            $this->institution,
            $vacationYears,
            Date::dateTimeToExcel(Carbon::parse($row->start_date)),
            Date::dateTimeToExcel(Carbon::parse($row->end_date)),
            $row->days_requested,
        ];
    }
}
