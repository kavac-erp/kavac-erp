<?php

namespace App\Repositories;

use Exception;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

/**
 * @class BackupRepository
 * @brief Gestiona las acciones a ejecutar en los respaldos del sistema
 *
 * Gestiona las acciones que se deben realizar en el respaldo de información
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BackupRepository
{
    /**
     * Constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Gestiona la creación de respaldos de la base de datos
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return  boolean Devuelve verdadero al crear el respaldo
     */
    public function create()
    {
        try {
            // Ejecuta el comando para iniciar el proceso de respaldo
            Artisan::call('backup:run');
            // Información del resultado de la ejecución del comando
            $output = Artisan::output();

            // Registra un log de información con el respaldo generado
            Log::info(
                "Backpack\BackupManager -- " .
                __('se ha generado un nuevo backup desde la interfaz administrativa') . ". \r\n" .
                $output
            );

            Session::flash('message', ['type' => 'other', 'text' => __('Nuevo backup generado')]);
        } catch (Exception $e) {
            // Registra un log de la excepción
            Log::error($e->getMessage());
            Session::flash('message', ['type' => 'other', 'text' => $e->getMessage()]);
        }

        return true;
    }

    /**
     * Ejecuta la acción necesaria para restaurar los datos a partir de un respaldo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      string           $filename    Nombre del archivo con el respaldo a ser restaurado
     * @param      object           $request     Objeto con información de la petición
     *
     * @return     boolean          Devuelve verdadero si se realizó la restauración de la base de datos,
     *                              de lo contrario devuelve falso
     */
    public function restore($filename, $request)
    {
        // Ruta en la que se realiza el respaldo de la base de datos
        $path = Storage::disk(config('backup.backup.destination.disks')[0])->getAdapter()->getPathPrefix();
        // Instancia a la clase
        $zip = new ZipArchive();
        // Objeto con información del archivo a descomprimir
        $res = $zip->open($path . config('app.name') . "/" . $filename);
        if ($res === true) {
            try {
                $request->session()->regenerate();
                // Nombre del archivo a descomprimir
                $snapName = str_replace(".zip", "", $filename);
                $zip->renameName('db-dumps/postgresql-kavac.sql', $snapName . '.sql');
                $zip->extractTo($path);
                $zip->close();
                // Código de respuesta en la restauración de datos
                $exitCode = Artisan::call('snapshot:load ' . $snapName);
                // Datos de respuesta en la ejecución del comando
                $output = Artisan::output();
                // Registra un log con los resultados de la ejecución del comando
                Log::info(
                    "Backpack\BackupManager -- " .
                    'se ha generado una nueva restauración de la base de datos desde la interfaz administrativa' .
                    ". \r\n" .
                    $output
                );
                return (strpos($output, 'loaded') > 0);
            } catch (Exception $e) {
                // Registra un log de la excepción
                Log::error($e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Muestra el listado de archivos de respaldo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $disk Nombre del disco del sistema de archivos
     * @param  string $dir  Nombre del directorio del sistema de archivos
     *
     * @return array        Devuelve un arreglo con el listado de archivos de respaldo en orden descendente
     */
    public function getList($disk, $dir)
    {
        // Objeto con información del disco a usar para obtener el listado de archivos disponibles
        $storage_disk = Storage::disk($disk[0]);
        // Objeto con información de los archivos disponibles
        $files = $storage_disk->files($dir);
        // Listado con información detallada de los archivos a restaurar
        $backups = [];
        // Genera un arreglo con la información detallada de los archivos de respaldo (tamaño y fecha de creación)
        foreach ($files as $k => $f) {
            if (substr($f, -4) == '.zip' && $storage_disk->exists($f)) {
                $backups[] = [
                    'file_path' => $f,
                    'file_name' => str_replace($dir . '/', '', $f),
                    'file_size' => $this->humanFileSize($storage_disk->size($f)),
                    'last_modified' => $storage_disk->lastModified($f),
                ];
            }
        }
        // Reordena el arreglo para mostrar los archivos de respaldo desde el mas nuevo al mas antiguo
        return array_reverse($backups);
    }

    /**
     * Obtiene un archivo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $disk      Disk name
     * @param  string $dir       Directory name
     * @param  string $file_name File name
     *
     * @return array             File data
     */
    public function getFile($disk, $dir, $file_name)
    {
        // Ruta del archivo
        $file = $dir . '/' . $file_name;
        // Objeto con información del disco a usar para obtener el archivo
        $storage_disk = Storage::disk($disk[0]);
        if ($storage_disk->exists($file)) {
            // Driver del disco a usar
            $fs = Storage::disk($disk[0])->getDriver();
            // Contenido del archivo
            $stream = $fs->readStream($file);
            return [true, 'stream' => $stream, 'fs' => $fs, 'file' => $file];
        }

        return [false];
    }

    /**
     * Elimina un archivo
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string $disk      Disk name
     * @param  string $dir       Directory name
     * @param  string $file_name File name
     *
     * @return boolean
     */
    public function delFile($disk, $dir, $file_name)
    {
        // Objeto con información del disco del sistema de archivos
        $storage_disk = Storage::disk($disk[0]);
        if ($storage_disk->exists($dir . '/' . $file_name)) {
            $storage_disk->delete($dir . '/' . $file_name);
            Session::flash('message', ['type' => 'destroy']);
            return true;
        }

        return false;
    }

    /**
     * Muestra el tamaño del archivo en formato legible
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  float   $size       Tamaño del archivo a convertir
     * @param  integer $precision  Precisión para el tamaño del archivo
     *
     * @return string
     */
    public function humanFileSize($size, $precision = 2)
    {
        // Listado de unidades de capacidad
        $units = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
        // base de cálculo para determinar el tamaño en formato legible
        $step = 1024;
        // Contador
        $i = 0;
        while (($size / $step) > 0.9) {
            // Tamaño del archivo
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision) . $units[$i];
    }
}
