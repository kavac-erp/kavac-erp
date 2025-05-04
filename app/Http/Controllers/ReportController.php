<?php

/** Controladores base de la aplicación */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elibyy\TCPDF\TCPDF as PDF;
use App\Repositories\ReportRepository;
use App\Models\Institution;
use App\Models\Document;

/**
 * @class ReportController
 * @brief Gestiona información de reportes de la aplicación
 *
 * Controlador para gestionar reportes de la aplicación
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ReportController extends Controller
{
    /**
     * Crea un nuevo reporte
     *
     * @method    create
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request             $request             Objeto con información de la petición
     * @param     ReportRepository    $reportRepository    Objeto con los método necesarios para gestionar los reportes
     */
    public function create(Request $request, ReportRepository $reportRepository)
    {
        //
    }

    /**
     * Realiza el proceso de firma digital de un reporte
     *
     * @method    sign
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request             $request             Objeto con información de la petición
     * @param     ReportRepository    $reportRepository    Objeto con los método necesarios para gestionar los reportes
     */
    public function sign(Request $request, ReportRepository $reportRepository)
    {
        //
    }

    /**
     * Verifica la autenticidad de una firma en un reporte
     *
     * @method    verify
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Document    $document    Objeto con información del documento a verificar
     */
    public function verify(Document $document)
    {
        //
    }
}
