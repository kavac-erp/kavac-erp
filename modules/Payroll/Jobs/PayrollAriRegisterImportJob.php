<?php

namespace Modules\Payroll\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Payroll\Imports\AriRegisterImport;

/**
 * @class PayrollAriRegisterImportJob
 * @brief Trabajo que se encarga de importar el archivo de registros de ARI
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollAriRegisterImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use SerializesModels;

    /**
     * Ruta del archivo a importar
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
        Excel::import(new AriRegisterImport(), $this->file, null, \Maatwebsite\Excel\Excel::XLSX);
        Storage::delete($this->file);
    }
}
