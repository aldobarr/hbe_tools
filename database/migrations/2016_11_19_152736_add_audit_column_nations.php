<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuditColumnNations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nation_stats', function($table){
			$table->integer('next_audit')->after('inactive')->unsigned()->default(0);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nation_stats', function($table){
			$table->dropColumn('next_audit');
		});
    }
}