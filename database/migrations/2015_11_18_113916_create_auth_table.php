<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_levels', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
			$table->string('description');
			
			$table->primary('id');
        });
		DB::table('auth_levels')->insert(
			array(
				'id' => 0,
				'name' => 'User',
				'description' => 'Regular user with no rights.'
			)
		);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('auth_levels');
    }
}
