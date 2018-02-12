<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllianceDb extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alliances', function (Blueprint $table) {
			$table->integer('id');
			$table->string('name', 50);
			$table->string('label', 50);
			$table->integer('size');
			$table->text('flag');
			
			$table->primary('id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alliances');
	}
}
