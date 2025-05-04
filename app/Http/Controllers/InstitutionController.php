<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\FiscalYear;
use App\Models\Institution;
use Illuminate\Http\JsonResponse;

/**
 * @class InstitutionController
 * @brief Gestiona información de Organizaciones
 *
 * Controlador para gestionar Organizaciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class InstitutionController extends Controller
{
    /**
     * Obtiene las Organizaciones registradas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id                      Identificador de la organización a buscar, este parámetro es opcional
     *
     * @return JsonResponse    JSON con los datos de las Organizaciones
     */
    public function getInstitutions($id = null)
    {
        return response()->json(
            array_merge(
                [
                    [
                        'id' => '',
                        'text' => 'Seleccione...'
                    ]
                ],
                Institution::query()
                    ->select('id', 'name as text', 'start_operations_date')
                    ->get()
                    ->map(fn ($element) => [
                        'id' => $element->id,
                        'text' => $element->text,
                        'start_operations_date' => $element->start_operations_date
                    ])
                    ->toArray()
            )
        );
    }

    /**
     * Obtiene el año actual para la ejecución de recursos
     *
     * @param  integer $institution_id          Identificador de la organización, si no se especifica toma
     *                                          el valor por defecto
     * @param  string  $year                    Año de la ejecución, si no se especifica toma el año actual
     *                                          del sistema
     * @return JsonResponse    JSON con información del año de execución
     */
    public function getExecutionYear($institution_id = null, $year = null)
    {
        $currentFiscalYear = FiscalYear::select('year')
                                        ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
        // Texto que identifica el año fiscal actual
        $year = $year ?? $currentFiscalYear->year ?? Carbon::now()->format("Y");
        // Año de ejercicio fiscal por defecto
        $exec_year = $year;
        // Establece los filtros a aplicar para la consulta del año fiscal en curso
        $filter = ['active' => true];
        $filter[(is_null($institution_id)) ? 'default' : 'id'] = (is_null($institution_id)) ? true : $institution_id;
        // Objeto con datos de los organismos a consultar
        $institution = Institution::with(['fiscalYears'])->where($filter)->first();

        if ($institution) {
            // Año de ejercicio fiscal activo
            $fiscalYear = $institution->fiscalYears()->where(['year' => $year, 'active' => true])->first();
            if (!$fiscalYear) {
                $fiscalYear = $institution->fiscalYears()->updateOrCreate(
                    ['year' => $year],
                    [
                        'active' => true,
                        'observations' => 'Ejercicio económico de ' . $institution->acronym
                    ]
                );
            }
            // Año fiscal actual
            $exec_year = $fiscalYear->year;
        }

        return response()->json(['result' => true, 'year' => $exec_year], 200);
    }
}
