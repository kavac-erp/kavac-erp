<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldTypeToRequiredDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('required_documents')) {
            Schema::table('required_documents', function (Blueprint $table) {
                $table->string('type')->nullable()->comment('Define un tipo de registro en caso de que en un mismo modelo se registre " .
                          "distinta información"');
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
        Schema::table('required_documents', function (Blueprint $table) {
            //
        });
        if (Schema::hasColumn('required_documents', 'type')) {
            Schema::table('required_documents', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
}
