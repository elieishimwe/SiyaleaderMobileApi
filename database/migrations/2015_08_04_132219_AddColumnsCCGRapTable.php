<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsCCGRapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccg_rap', function($table)
        {

            $table->string('prop_department');
            $table->string('prop_sub_sub_category');


         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccg_rap', function($table)
        {
             $table->dropColumn('prop_department');
             $table->dropColumn('prop_sub_sub_category');

         });
    }
}
