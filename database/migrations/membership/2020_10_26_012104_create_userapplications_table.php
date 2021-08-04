<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserapplicationsTable extends Migration
{
    /**
     * The database connection.
     *
     * @var string
     */
    protected $connection;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = config('database.membership_default');
    }

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection($this->connection)->create('userapplications', function(Blueprint $table) {
			$table->char('Id', 36)->primary();
			$table->char('UserId', 36)->index('UserId');
			$table->char('AgencyId', 36)->index('FK_UserApplications_AgencyId');
			$table->char('LoginId', 36)->index('LoginId');
			$table->smallInteger('Application')->unsigned()->index('FK_UserApplications_Application');
			$table->boolean('Status');
			$table->date('Created');
			$table->string('TitleType', 50)->nullable();
			$table->boolean('IsDeprecated')->default(0);

            // $table->foreign('AgencyId', 'FK_UserApplications_AgencyId')->references('Id')->on('agencysnapshots')->onUpdate('CASCADE')->onDelete('CASCADE');
            // $table->foreign('Application', 'FK_UserApplications_Application')->references('Id')->on('applications')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection($this->connection)->dropIfExists('userapplications');

        // $table->dropForeign('FK_UserApplications_AgencyId');
        // $table->dropForeign('FK_UserApplications_Application');
	}
}
