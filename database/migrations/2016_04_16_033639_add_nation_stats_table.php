<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNationStatsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('nation_stats', function (Blueprint $table) {
			$table->integer('nation_id');
			$table->string('nation', 255);
			$table->string('leader', 255);
			$table->integer('soldiers');
			$table->integer('tanks');
			$table->integer('planes');
			$table->integer('ships');
			$table->integer('missiles');
			$table->integer('nukes');
			$table->tinyInteger('spies')->default(0);
			$table->decimal('score', 12, 2);
			$table->integer('cities');
			$table->decimal('infra', 12, 2);
			$table->boolean('cce');
			$table->integer('city_timer');
			$table->integer('inactive');
			
			$table->primary('nation_id');
			$table->unique('nation');
			$table->unique('leader');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('nation_stats');
	}
}
