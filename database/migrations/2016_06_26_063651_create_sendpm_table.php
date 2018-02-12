<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendpmTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('send_pm', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->string('subject', 255);
			$table->text('body');
			$table->integer('time')->unsigned();
			$table->integer('sent')->unsigned()->default(0);
			
			$table->index(['time', 'sent']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('send_pm');
	}
}
