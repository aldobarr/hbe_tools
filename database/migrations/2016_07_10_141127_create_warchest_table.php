<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarchestTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('warchests', function (Blueprint $table) {
			$table->integer('id')->unsigned();
			$table->integer('credits');
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
			$table->integer('time')->unsigned();
			
			$table->primary('id');
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
		Schema::drop('warchests');
	}
}