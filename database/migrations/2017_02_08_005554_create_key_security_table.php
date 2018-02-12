<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeySecurityTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('key_security', function (Blueprint $table) {
			$table->increments('id');
			$table->string('key', 255);
			$table->integer('authorized_user');
			$table->string('client', 255);
			$table->string('product', 50);
			$table->text('features');
			$table->tinyInteger('valid');
			
			$table->unique('key');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('key_security');
	}
}