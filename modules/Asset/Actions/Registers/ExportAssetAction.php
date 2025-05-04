<?php

declare(strict_types=1);

namespace Modules\Asset\Actions\Registers;

use App\Exports\MultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Asset\Exports\AssetExport;
use Modules\Asset\Exports\Sheets\MuebleValidationSheetExport;
use Modules\Asset\Exports\Sheets\InmuebleValidationSheetExport;
use Modules\Asset\Exports\Sheets\VehiculoValidationSheetExport;
use Modules\Asset\Exports\Sheets\SemovienteValidationSheetExport;

/**
 * @class ExportAssetAction
 * @brief Acciones en el proceso de exportación de datos de bienes
 *
 * Gestiona las acciones en el proceso de exportación de datos de bienes
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class ExportAssetAction
{
    /**
     * Método constructor de la clase
     *
     * @param MultiSheetExport $worksheet Objeto para la generación de hojas del archivo a exportar
     *
     * @return void
     */
    public function __construct(
        private MultiSheetExport $worksheet,
    ) {
        //
    }

    /**
     * Método que establece las validaciones y datos a exportar
     *
     * @param array $data Listado con información de los bienes a exportar
     * @param string $file Nombre del archivo a exportar
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|void
     */
    public function invoke(array $data, string $file = 'data')
    {
        if (!empty($data['type'])) {
            $validation = match ($data['type']) {
                'mueble' => new MuebleValidationSheetExport(),
                'inmueble' => new InmuebleValidationSheetExport(),
                'vehiculo' => new VehiculoValidationSheetExport(),
                'semoviente' => new SemovienteValidationSheetExport(),
            };

            $this->worksheet->setSheets([
                'data' => new AssetExport($data['type']),
                'validation' => $validation,
            ]);

            return Excel::download($this->worksheet, $file . '.xlsx');
        }
    }
}
