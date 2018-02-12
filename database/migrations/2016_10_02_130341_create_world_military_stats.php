<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorldMilitaryStats extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('world_military_stats', function (Blueprint $table) {
			$table->integer('nation_id');
			$table->string('nation', 255);
			$table->string('leader', 255);
			$table->integer('alliance_id');
			$table->integer('alliance_pos');
			$table->integer('soldiers');
			$table->integer('tanks');
			$table->integer('planes');
			$table->integer('ships');
			$table->integer('missiles');
			$table->integer('nukes');
			$table->decimal('score', 12, 2);
			$table->integer('cities');
			$table->integer('projects');
			$table->decimal('infra', 12, 2);
			$table->integer('city_timer');
			$table->integer('inactive');
			
			$table->primary('nation_id');
			$table->unique('nation');
			$table->unique('leader');
			$table->index('alliance_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('world_military_stats');
	}
}