<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewFieldsToFinancePaymentDeductionsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::table('cache')->where('key', 'kavac_cachedocument_status')->delete();
        });
        Schema::table('finance_payment_deductions', function (Blueprint $table) {
            if (!Schema::hasColumns('finance_payment_deductions', ['document_status_id', 'deductions_ids'])) {
                $table->foreignId('document_status_id')->nullable()
                    ->default(default_document_status_el()->id)
                    ->comment('Identificador único asociado al estatus del documento')->constrained('document_status')
                    ->onDelete('restrict')->onUpdate('cascade');

                $table->json('deductions_ids')->nullable()
                    ->comment('Lista de identificadores de decuciones que fueron agrupados para ser pagados');
            }

            if (Schema::hasColumn('finance_payment_deductions', 'finance_payment_execute_id')) {
                $table->foreignId('finance_payment_execute_id')
                    ->comment('Identificador único asociado a la ejecución de pago')
                    ->nullable()
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->change();
            }
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('finance_payment_deductions', function (Blueprint $table) {
            if (Schema::hasColumns('finance_payment_deductions', ['document_status_id', 'deductions_ids'])) {
                $table->dropForeign(['document_status_id']);
                $table->dropColumn('document_status_id');
                $table->dropColumn('deductions_ids');
            }

            if (Schema::hasColumn('finance_payment_deductions', 'finance_payment_execute_id')) {
                $table->foreignId('finance_payment_execute_id')
                    ->comment('Identificador único asociado a la ejecución de pago')
                    ->onDelete('restrict')
                    ->onUpdate('cascade')
                    ->change();
            }
        });
    }
}
