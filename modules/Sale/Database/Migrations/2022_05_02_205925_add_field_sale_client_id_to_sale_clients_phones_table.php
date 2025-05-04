<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldSaleClientIdToSaleClientsPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_clients_phones', function (Blueprint $table) {
             $table->foreignId('sale_client_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_clients_phones', function (Blueprint $table) {
            if (Schema::hasColumn('sale_clients_phones', 'sale_client_id')) {
                $table->dropForeign(['sale_client_id']);
                $table->dropColumn('sale_client_id');
            }
        });
    }
}
