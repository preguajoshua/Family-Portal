<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notes', function (Blueprint $table) {
			$table->char('Id', 36)->primary();

			$table->char('UserId', 36);
			$table->char('PatientId', 36);
            $table->string('Title', 100)->nullable();
			$table->text('Description', 65535)->nullable();

            $table->boolean('Completed')->nullable()->default(0);
			$table->dateTime('StartDate')->nullable();
			$table->dateTime('EndDate');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('notes');
	}
}
