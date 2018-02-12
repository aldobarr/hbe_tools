<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('treaty_web', function (Blueprint $table) {
			$table->integer('one');
			$table->integer('two');
			$table->tinyInteger('type');
			
			$table->index('one');
			$table->index('two');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('treaty_web');
	}
}
