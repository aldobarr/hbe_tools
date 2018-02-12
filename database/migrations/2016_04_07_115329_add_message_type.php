<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageType extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('recruitment_messages', function($table){
			$table->tinyInteger('type')->after('body');
		});
		Schema::table('recruitment_messaged', function($table){
			$table->tinyInteger('type')->after('time');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('recruitment_messages', function($table){
			$table->dropColumn('type');
		});
		Schema::table('recruitment_messaged', function($table){
			$table->dropColumn('type');
		});
	}
}