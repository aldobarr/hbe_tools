<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recruitment_messages', function (Blueprint $table) {
			$table->increments('id');
			$table->string('subject', 255);
			$table->text('body');
			$table->tinyInteger('active');
		});
		Schema::create('recruitment_messaged', function (Blueprint $table) {
			$table->integer('id');
			$table->integer('time');
			
			$table->index('id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('recruitment_messages');
		Schema::drop('recruitment_messaged');
	}
}