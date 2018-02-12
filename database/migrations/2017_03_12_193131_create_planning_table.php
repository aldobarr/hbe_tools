<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanningTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('war_planning_threads', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('target_id');
			$table->string('target_name', 100);
			$table->string('target_leader', 100);
			$table->string('target_aa', 100);
			$table->integer('target_aa_id');
			$table->text('data');
			$table->integer('thread_id');
			$table->tinyInteger('moved')->default(0);
			
			$table->index(['target_id', 'target_aa_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('war_planning_threads');
	}
}