<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldDescriptionToPurchaseTypesTable
 * @brief Cambia el tipo de dato de string(255) a text de la columna description de la tabla purchase_types
 *
 * Cambia el tipo de dato en la columna description
 *
 * @author
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldDescriptionToPurchaseTypesTable extends Migration
{
  /**
   * Ejecuta las migraciones.
   *
   * @return void
   */
    public function up()
    {
        if (Schema::hasTable("purchase_types")) {
            Schema::table("purchase_types", function (Blueprint $table) {
                if (Schema::hasColumn("purchase_types", "description")) {
                    $table
                    ->text("description")
                    ->nullable()
                    ->change();
                }
            });
        }
    }

  /**
   * Revierte las migraciones.
   *
   * @return void
   */
    public function down()
    {
        if (Schema::hasTable("purchase_types")) {
            Schema::table("purchase_types", function (Blueprint $table) {
                if (Schema::hasColumn("purchase_types", "description")) {
                    $table
                    ->string("description", 255)
                    ->nullable()
                    ->change();
                }
            });
        }
    }
}
