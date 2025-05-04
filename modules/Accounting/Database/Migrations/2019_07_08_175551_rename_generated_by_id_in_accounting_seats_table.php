<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameGeneratedByIdInAccountingSeatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            $table->renameColumn('generated_by_id', 'accounting_seat_categories_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_seats', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_seats', 'accounting_seat_categories_id')) {
                $table->renameColumn('accounting_seat_categories_id', 'generated_by_id');
            }
        });
    }
}
