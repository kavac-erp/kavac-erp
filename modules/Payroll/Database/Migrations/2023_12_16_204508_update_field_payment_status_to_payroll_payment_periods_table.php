<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Payroll\Models\PayrollPaymentPeriod;

/**
 * @class UpdateFieldPaymentStatusToPayrollPaymentPeriodsTable
 * @brief Ejecuta el proceso de migración de la estructura de tablas en base de datos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class UpdateFieldPaymentStatusToPayrollPaymentPeriodsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            $table->enum('payment_status_copy', ['pending', 'generated', 'approved'])->default('pending');
        });

        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            $periods = PayrollPaymentPeriod::get();

            foreach ($periods as $period) {
                $period->payment_status_copy = $period->payment_status;
                $period->save();
            }

            $table->dropColumn('payment_status');
        });

        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'generated', 'approved'])->default('pending')
            ->comment('Establece la condición del pago asociado al período ' .
                                      '(pending: Pendiente, generated: Generado)');
        });

        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            $periods = PayrollPaymentPeriod::get();

            foreach ($periods as $period) {
                $period->payment_status = $period->payment_status_copy;
                $period->save();
            }
        });

        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            $table->dropColumn('payment_status_copy');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_payment_periods', function (Blueprint $table) {
            Schema::table('payroll_payment_periods', function (Blueprint $table) {
                $table->enum('payment_status_copy', ['pending', 'generated', 'approved'])->default('pending');
            });

            Schema::table('payroll_payment_periods', function (Blueprint $table) {
                $periods = PayrollPaymentPeriod::get();

                foreach ($periods as $period) {
                    $period->payment_status_copy = $period->payment_status == 'approved' ||
                        $period->payment_status == 'generated' ? 'generated' : 'pending';
                    $period->save();
                }

                $table->dropColumn('payment_status');
            });

            Schema::table('payroll_payment_periods', function (Blueprint $table) {
                $table->enum('payment_status', ['pending', 'generated'])->default('pending')
                ->comment('Establece la condición del pago asociado al período ' .
                                          '(pending: Pendiente, generated: Generado)');
            });

            Schema::table('payroll_payment_periods', function (Blueprint $table) {
                $periods = PayrollPaymentPeriod::get();

                foreach ($periods as $period) {
                    $period->payment_status = $period->payment_status_copy;
                    $period->save();
                }
            });

            Schema::table('payroll_payment_periods', function (Blueprint $table) {
                $table->dropColumn('payment_status_copy');
            });
        });
    }
}
