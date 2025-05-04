<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\ReportRepository;
use App\Models\Document;

/**
 * @class ReportController
 * @brief Gestiona información de reportes de la aplicación
 *
 * Controlador para gestionar reportes de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ReportController extends Controller
{
    /**
     * Crea un nuevo reporte
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request             $request             Objeto con información de la petición
     * @param     ReportRepository    $reportRepository    Objeto con los método necesarios para gestionar los reportes
     *
     * @return void
     */
    public function create(Request $request, ReportRepository $reportRepository)
    {
        //
    }

    /**
     * Realiza el proceso de firma digital de un reporte
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request             $request             Objeto con información de la petición
     * @param     ReportRepository    $reportRepository    Objeto con los método necesarios para gestionar los reportes
     *
     * @return void
     */
    public function sign(Request $request, ReportRepository $reportRepository)
    {
        //
    }

    /**
     * Verifica la autenticidad de una firma en un reporte
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Document    $document    Objeto con información del documento a verificar
     *
     * @return void
     */
    public function verify(Document $document)
    {
        //
    }
}
