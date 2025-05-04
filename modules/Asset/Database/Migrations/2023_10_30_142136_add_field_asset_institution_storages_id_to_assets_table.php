<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Asset\Models\AssetInstitutionStorage;

/**
 * @class AddFieldAssetInstitutionStoragesIdToAssetsTable
 * @brief Agrega un campo asset_institution_storages_id a la tabla assets
 *
 * Agrega un campo asset_institution_storages_id a la tabla assets
 *
 * @author Manuel Zambrano <mzambrano@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AddFieldAssetInstitutionStoragesIdToAssetsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        $storageId = null;
        if (Schema::hasTable('asset_institution_storages')) {
            // Realizar una consulta a la tabla utilizando el modelo
            $mainStorage = AssetInstitutionStorage::where('manage', true)->firstWhere('main', true);
            if ($mainStorage) {
                $storageId = $mainStorage->id;
            }else{
                echo "\nNo se encontró ningún Deposito por defecto en la base de datos, asigne uno antes de continuar.\n\n";
            }
        }else{
            throw new Exception('No se encontró la tabla asset_institution_storages en la base de datos.');
        }

        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) use ($storageId) {
                if (!Schema::hasColumn('assets', 'asset_institution_storages_id')) {
                    $table->foreignId('asset_institution_storages_id')->default($storageId)->nullable()
                        ->comment('Identificador único asociado al Deposito de la institucion')
                        ->constrained('asset_institution_storages')
                        ->onDelete('restrict')->onUpdate('cascade');
                };
            });
        };
    }


    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_institution_storages_id']);
            $table->dropColumn('asset_institution_storages_id');
        });
    }
}
