<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnstoOssUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imb_oss_users', function($table)
        {
            $table->timestamps();
            $table->string('api_key');

         });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imb_oss_users', function($table)
        {
             $table->dropColumn('created_at');
             $table->dropColumn('updated_at');
             $table->dropColumn('api_key');
         });
    }
}
