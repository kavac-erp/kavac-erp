<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @class FiscalYearController
 * @brief Gestiona información del año fiscal
 *
 * Controlador para gestionar información de los años fiscales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FiscalYearController extends Controller
{
    /**
     * Listado de años fiscales registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function index()
    {
        //
    }

    /**
     * Muestra el formulario para el registro de un nuevo año fiscal
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Registra un nuevo año fiscal
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de un año fiscal
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     FiscalYear    $fiscalYear    Objeto con información de la petición
     *
     * @return void
     */
    public function show(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Muestra el formulario con información del año fiscal a actualizar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     FiscalYear    $fiscalYear    Objeto con información del año fiscal a actualizar
     *
     * @return void
     */
    public function edit(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Actualiza los datos de un año fiscal
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request       $request       Objeto con información de la petición
     * @param     FiscalYear    $fiscalYear    Objeto con información del año fiscal a actualizar
     *
     * @return void
     */
    public function update(Request $request, FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Elimina un año fiscal
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     FiscalYear    $fiscalYear    Objeto con información del año fiscal a eliminar
     *
     * @return void
     */
    public function destroy(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Listado de los años de ejercicio fiscal que aún no han sido cerrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    JsonResponse       Objeto con el listado de años de ejercicio fiscal
     */
    public function getOpened()
    {
        $fiscalYears = $this->getList();
        return response()->json(['records' => $fiscalYears], 200);
    }

    /**
     * Listado de los años de ejercicio fiscal cerrados
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    JsonResponse       Objeto con el listado de años de ejercicio fiscal
     */
    public function getClosed()
    {
        $fiscalYears = $this->getList(false);
        return response()->json(['records' => $fiscalYears], 200);
    }

    /**
     * Listado de todos los años de ejercicio fiscal abiertos / cerrados
     *
     * @method    getAll
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    JsonResponse       Objeto con el listado de años de ejercicio fiscal
     */
    public function getAll()
    {
        $fiscalYears = $this->getList(null, true);
        return response()->json(['records' => $fiscalYears], 200);
    }

    /**
     * Consulta y lista el último año de ejercicio fiscal cerrado o en pre-cierre
     * (abierto con entradas), o el mayor de ambos
     *
     * @author    Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
     *
     * @return    JsonResponse  Objeto con el último año de ejercicio fiscal
     *                          que cumpla con la regla
     */
    public function getLast()
    {

        $profileUser = Auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        if (isset($institution)) {
            $queryActiveYear = FiscalYear::query()
                        ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                        ->whereJsonLength('entries', '>', 1)
                        ->orderBy('year', 'desc')
                        ->first();

            $queryClosedYear = FiscalYear::query()
                        ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
                        ->orderBy('year', 'desc')
                        ->first();
        } else {
            $queryActiveYear = FiscalYear::query()
                        ->where(['active' => true, 'closed' => false])
                        ->whereJsonLength('entries', '>', 1)
                        ->orderBy('year', 'desc')
                        ->first();

            $queryClosedYear = FiscalYear::query()
                        ->where(['active' => false, 'closed' => true])
                        ->orderBy('year', 'desc')
                        ->first();
        }

        $lastYear = null;

        if (isset($queryActiveYear)) {
            $lastYear = $queryActiveYear->year;
        }

        if (isset($queryClosedYear)) {
            $lastYear = $queryClosedYear->year;
        }

        if (isset($queryActiveYear) && isset($queryClosedYear)) {
            $lastYear = ($queryActiveYear->year >= $queryClosedYear->year)
                ? $queryActiveYear->year
                : $queryClosedYear->year;
        }

        return response()->json(["last_year" => $lastYear], 200);
    }

    /**
     * Listado de los años de ejercicio fiscal
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array       Listado de años de ejercicio fiscal
     */
    public function getList($active = true, $all = false)
    {
        $currentFiscalYear = FiscalYear::query()
            ->select('year')
            ->where(['active' => $active, 'closed' => !$active])
            ->orderBy('year', 'desc')
            ->first();
        $currentYear = date("Y");
        // Perfil del usuario autenticado
        $profileUser = auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            // Institución asociada al usuario autenticado
            $institution = Institution::with(['fiscalYears'])->find($profileUser->institution_id);
        } else {
            // Institución por defecto
            $institution = Institution::with(['fiscalYears'])->where('active', true)->where('default', true)->first();
        }
        // Años fiscales abiertos
        $fiscalYears = $institution->fiscalYears()
            ->select('year as id', 'year as text', 'created_at')
            ->when(!$all, fn ($query) => $query->where(['active' => $active, 'closed' => !$active]))
            ->get()
            ->toArray();
        if (
            $active &&
            !in_array(['id' => $currentYear, 'text' => $currentYear], $fiscalYears) &&
            !isset($currentFiscalYear)
        ) {
            array_push($fiscalYears, [
                'id' => $currentYear,
                'text' => $currentYear,
                'created_at' => $currentYear,
            ]);
        }
        return $fiscalYears;
    }
}
