<?php

/** Controladores base de la aplicación */

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Institution;
use Illuminate\Http\Request;

/**
 * @class FiscalYearController
 * @brief Gestiona información del año fiscal
 *
 * Controlador para gestionar información de los años fiscales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FiscalYearController extends Controller
{
    /**
     * Listado de años fiscales registrados
     *
     * @method    index
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function index()
    {
        //
    }

    /**
     * Muestra el formulario para el registro de un nuevo año fiscal
     *
     * @method    create
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function create()
    {
        //
    }

    /**
     * Registra un nuevo año fiscal
     *
     * @method    store
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de un año fiscal
     *
     * @method    show
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     FiscalYear    $fiscalYear    Objeto con información de la petición
     */
    public function show(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Muestra el formulario con información del año fiscal a actualizar
     *
     * @method    edit
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     FiscalYear    $fiscalYear    Objeto con información del año fiscal a actualizar
     */
    public function edit(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Actualiza los datos de un año fiscal
     *
     * @method    update
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request       $request       Objeto con información de la petición
     * @param     FiscalYear    $fiscalYear    Objeto con información del año fiscal a actualizar
     */
    public function update(Request $request, FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Elimina un año fiscal
     *
     * @method    destroy
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     FiscalYear    $fiscalYear    Objeto con información del año fiscal a eliminar
     */
    public function destroy(FiscalYear $fiscalYear)
    {
        //
    }

    /**
     * Listado de los años de ejercicio fiscal que aún no han sido cerrados
     *
     * @method    getOpened
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
     * @method    getClosed
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
     * Consulta y lista el último año de ejercicio fiscal cerrado o en pre-cierre
     * (abierto con entradas), o el mayor de ambos
     *
     * @method    getLast
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
     * @method    getList
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    JsonResponse       Objeto con el listado de años de ejercicio fiscal
     */
    public function getList($active = true)
    {
        $currentFiscalYear = FiscalYear::select('year')
                                        ->where(['active' => $active, 'closed' => !$active])
                                        ->orderBy('year', 'desc')
                                        ->first();
        $currentYear = date("Y");
        /** @var profileUser Perfil del usuario autenticado */
        $profileUser = Auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            /** @var institution Institución asociada al usuario autenticado */
            $institution = Institution::with(['fiscalYears'])->find($profileUser->institution_id);
        } else {
            /** @var institution Institución por defecto */
            $institution = Institution::with(['fiscalYears'])->where('active', true)->where('default', true)->first();
        }
        /** @var FiscalYear|array Años fiscales abiertos */
        $fiscalYears = $institution->fiscalYears()
                                   ->select('year as id', 'year as text', 'created_at')
                                   ->where(['active' => $active, 'closed' => !$active])
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
