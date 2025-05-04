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

final class ExportAssetAction
{
    public function __construct(
        private MultiSheetExport $worksheet,
    ) {
    }

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
