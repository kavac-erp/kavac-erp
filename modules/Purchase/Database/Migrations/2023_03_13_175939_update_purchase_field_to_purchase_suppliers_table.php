<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdatePurchaseFieldToPurchaseSuppliersTable
 * @brief Actualización de tipo de dato de campo de la tabla purchase_suppliers.
 *
 * Clase que gestiona la actualización del tipo de dato de un campo de la tabla
 * purchase_suppliers.
 *
 * @author   Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdatePurchaseFieldToPurchaseSuppliersTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE purchase_suppliers DROP CONSTRAINT IF EXISTS purchase_suppliers_person_type_check");
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('purchase_suppliers', function (Blueprint $table) {
            $table->string('person_type')->nullable()
                ->comment(
                    'Tipo de persona:
                        (N)atural,
                        (J)urídica,
                        (G)ubernamental,
                        (E)Extranjero'
                )
                ->change();
        });
    }

    /**
     * Método que elimina las migraciones
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_suppliers', 'person_type')) {
                $table->string('person_type')->default('J')
                    ->comment('Tipo de persona. (N)atural o (J)urídica')->change();
            } else {
                $table->enum('person_type', ['N', 'J'])->default('J')
                    ->comment('Tipo de persona. (N)atural o (J)urídica');
            }
        });
    }
}
