<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToReceiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('receivers')) {
            if (!Schema::hasColumn('receivers', 'associateable_id') && !Schema::hasColumn('receivers', 'associateable_type')) {
                Schema::table('receivers', function (Blueprint $table) {
                    $table->nullableMorphs('associateable');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('receivers')) {
            if (Schema::hasColumn('receivers', 'associateable_id') && Schema::hasColumn('receivers', 'associateable_type')) {
                Schema::table('receivers', function (Blueprint $table) {
                    $table->dropColumn('associateable_type');
                    $table->dropColumn('associateable_id');
                });
            }
        }
    }
}
