<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketDataTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('market_data', function (Blueprint $table) {
			$table->string('resource');
			$table->decimal('high_buy', 12, 2);
			$table->decimal('low_buy', 12, 2);
			$table->decimal('avg', 12, 2);
			$table->integer('time');
			
			$table->index('resource');
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
		Schema::drop('market_data');
	}
}
