<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUniqueKeyToExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->dropUnique(['start_at', 'end_at', 'active']);
        });
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->unique(['from_currency_id', 'to_currency_id', 'start_at', 'end_at', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->dropUnique(['from_currency_id', 'to_currency_id', 'start_at', 'end_at', 'active']);
        });
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->unique(['start_at', 'end_at', 'active']);
        });
    }
}
