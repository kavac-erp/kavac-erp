<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseTypeOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_type_operations')) {
            Schema::create('purchase_type_operations', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('name')->comment('Nombre del tipo de compra');
                $table->text('description')->nullable()->comment('Descripción del tipo de compra');

                $table->timestamps();
                $table->softDeletes()->comment('Fecha y hora en la que el registro fue eliminado');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_type_operations');
    }
}
