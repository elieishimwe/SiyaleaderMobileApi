<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnstCCGRap extends Migration
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

            $table->string('gps_lat');
            $table->string('gps_lng');
            $table->string('img_url');

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
             $table->dropColumn('gps_lat');
             $table->dropColumn('gps_lng');
             $table->dropColumn('img_url');
         });
    }
}
