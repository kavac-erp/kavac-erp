<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @class UpdateFieldNullableToPayrollPaymentTypesTable
 * @brief Actualiza los campos de la tabla de tipos de pagos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldNullableToPayrollPaymentTypesTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            $table->dropColumn('payment_relationship');
        });
        Schema::table('payroll_payment_types', function (Blueprint $table) {
            $table->date('start_date')->comment('Fecha de inicio del primer período')->nullable()->change();
            $table->enum(
                'payment_relationship',
                [
                    'payroll', 'comprehensive_wages', 'utilities', 'vacations',
                    'social_benefits_guarantees', 'social_benefit_interests', 'liquidations',
                    'ticket_basket', 'kindergarten', 'special_payroll', 'others'
                ]
            )->comment('Relación de pago (payroll, comprehensive_wages, utilities, vacations, ' .
                'social_benefits_guarantees, social_benefit_interests, liquidations, ' .
                'ticket_basket, kindergarten, special_payroll, others)')->nullable();
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_payment_types', function (Blueprint $table) {
        });
    }
}
