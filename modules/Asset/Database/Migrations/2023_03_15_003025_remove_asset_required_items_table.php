<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * @class RemoveAssetRequiredItemsTable
 * @brief Elimina la tabla asset_required_items
 *
 * Elimina la tabla asset_required_items
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RemoveAssetRequiredItemsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('asset_required_items');
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
    }
}
