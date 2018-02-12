<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('taxes', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('nation_id');
			$table->string('name', 50);
			$table->decimal('cash', 12, 2);
			$table->decimal('food', 8, 2);
			$table->decimal('coal', 8, 2);
			$table->decimal('oil', 8, 2);
			$table->decimal('uranium', 8, 2);
			$table->decimal('lead', 8, 2);
			$table->decimal('iron', 8, 2);
			$table->decimal('bauxite', 8, 2);
			$table->decimal('gasoline', 8, 2);
			$table->decimal('munitions', 8, 2);
			$table->decimal('steel', 8, 2);
			$table->decimal('aluminum', 8, 2);
			$table->integer('time');
			
			$table->index('nation_id');
			$table->index('time');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('taxes');
	}
}
