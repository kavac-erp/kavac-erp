<?php

/** [descripción del namespace] */

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
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
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

    protected $file;

    /**
     * Crea una nueva instancia de trabajo.
     *
     * @method __construct
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
     * @method handle
     *
     * @return void
     */
    public function handle()
    {
        Excel::import(new AriRegisterImport(), $this->file, null, \Maatwebsite\Excel\Excel::XLSX);
        Storage::delete($this->file);
    }
}
