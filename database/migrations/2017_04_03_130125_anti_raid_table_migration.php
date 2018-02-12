<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AntiRaidTableMigration extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('anti_raid_transactions', function (Blueprint $table) {
			$table->increments('id');
			$table->decimal('coal', 12, 2);
			$table->decimal('oil', 12, 2);
			$table->decimal('uranium', 12, 2);
			$table->decimal('lead', 12, 2);
			$table->decimal('iron', 12, 2);
			$table->decimal('bauxite', 12, 2);
			$table->decimal('gasoline', 12, 2);
			$table->decimal('munitions', 12, 2);
			$table->decimal('steel', 12, 2);
			$table->decimal('aluminum', 12, 2);
			$table->decimal('food', 12, 2);
			$table->decimal('cash', 15, 2);
			$table->tinyInteger('returned');
			
			$table->index('returned');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('anti_raid_transactions');
	}
}