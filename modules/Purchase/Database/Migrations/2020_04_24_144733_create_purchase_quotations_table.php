<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('purchase_quotations')) {
            Schema::create('purchase_quotations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 20)->unique()->comment('Código único para la cotización');

                $table->foreignId('purchase_supplier_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
                $table->foreignId('currency_id')->constrained()->onDelete('restrict')->onUpdate('cascade');

                $table->enum('status', ['WAIT', 'QUOTED', 'APPROVED'])->default('WAIT')->comment(
                    'Determina el estatus del requerimiento
                    (WAIT) - en espera.
                    (QUOTED) - Cotizado,
                    (APPROVED) - Aprobado',
                );


                $table->float('subtotal', 12, 10)->nullable()
                    ->comment('Subtotal de la orden de compra');
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
        Schema::dropIfExists('purchase_quotations');
    }
}
