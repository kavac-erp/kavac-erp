<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Asset\Models\Asset;
use Modules\Asset\Models\AssetBook;

/**
 * @class CreateAssetBooksTable
 * @brief Crea la tabla para registrar los libros de bienes
 *
 * Crea la tabla para registrar los libros de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CreateAssetBooksTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_books', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 20, 10)->default(0)->comment('Monto del bien en libro');
            $table->foreignId('asset_id')
                    ->nullable()
                    ->comment('Identificador asociado al bien')
                    ->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
        });

        /*
         * Se consultan los registros de la tabla Asset con los registros anteriores,
         * para tomar los montos actuales y agregarlo a los libros.
         */

        $assets = Asset::get();

        foreach ($assets as $asset) {
            $details = $asset->asset_details;
            if ((!empty($details['acquisition_value']) && $details['acquisition_value'] != 0)) {
                AssetBook::create([
                    'asset_id' => $asset->id,
                    'amount' => $details['acquisition_value']
                ]);
            }
        }
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_books');
    }
}
