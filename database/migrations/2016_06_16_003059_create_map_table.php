<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('war_map', function (Blueprint $table) {
			$table->integer('aggressor');
			$table->tinyInteger('aside');
			$table->integer('defender');
			$table->tinyInteger('dside');
			
			$table->index('aggressor');
			$table->index('defender');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('war_map');
	}
}
