<?php

namespace Modules\Payroll\Jobs;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Imports\SalaryAdjustmentImport;

/**
 * @class PayrollSalaryAdjustmentImportJob
 * @brief Trabajo que se encarga de importar los ajustes salariales
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAdjustmentImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Ruta del archivo a importar.
     *
     * @var string $file
     */
    protected $file;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->file = $data['filePath'];
    }

    /**
     * Ejecuta el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        Excel::import(new SalaryAdjustmentImport(), $this->file, null, \Maatwebsite\Excel\Excel::XLSX);
        Storage::delete($this->file);
    }
}
