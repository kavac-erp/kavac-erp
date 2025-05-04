<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetCompromiseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('budget_compromise_details')) {
            Schema::create('budget_compromise_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('description');
                $table->float('amount', 30, 10)->comment('Monto comprometido a la cuenta presupuestaria');
                $table->float('tax_amount', 30, 10)->default(0)
                      ->comment('Monto del impuesto aplicado al comprometido de la cuenta presupuestaria');
                $table->foreignId('tax_id')->constrained()->onUpdate('cascade');
                $table->foreignId('budget_compromise_id')->constrained()->onUpdate('cascade');
                $table->foreignId('budget_account_id')->constrained()->onUpdate('cascade');
                $table->unsignedBigInteger('budget_sub_specific_formulation_id')->comment(
                    'Identificador asociado a la Formulación'
                );
                $table->foreign(
                    'budget_sub_specific_formulation_id',
                    'budget_compromise_details_formulation_fk'
                )->references('id')->on('budget_sub_specific_formulations')->onUpdate('cascade');

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
        Schema::dropIfExists('budget_compromise_details');
    }
}
