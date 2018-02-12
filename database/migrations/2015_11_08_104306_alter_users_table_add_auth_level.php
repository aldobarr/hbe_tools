<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTableAddAuthLevel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table){
			$table->string('password', 255)->change();
			$table->tinyInteger('auth_level')->after('password');
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
			$table->dropColumn('auth_level');
			$table->string('password', 60)->change();
		});
    }
}