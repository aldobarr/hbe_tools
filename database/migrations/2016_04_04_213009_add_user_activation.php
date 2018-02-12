<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserActivation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table){
			$table->tinyInteger('activated')->after('auth_level')->default(0);
			$table->tinyInteger('force_logout')->after('activated')->default(0);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table){
			$table->dropColumn('activated');
			$table->dropColumn('force_logout');
		});
    }
}