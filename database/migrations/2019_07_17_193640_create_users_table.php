<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 36);

            $table->char('login_id', 36);

            $table->string('name');
            $table->string('email')->unique();

            $table->string('password', 60);
            $table->string('remember_token', 100)->nullable();

            $table->char('customer_id', 36)->nullable();

            $table->smallInteger('application');
            $table->smallInteger('cluster')->default(2);

            $table->timestamps();

            $table->primary(['login_id','application']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
