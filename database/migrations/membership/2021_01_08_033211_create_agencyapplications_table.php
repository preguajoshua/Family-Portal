<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyapplicationsTable extends Migration
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
        Schema::connection($this->connection)->create('agencyapplications', function (Blueprint $table) {
            $table->char('Id', 36)->default('00000000-0000-0000-0000-000000000000')->primary();
            $table->char('UniversalAccountId', 36)->nullable();
            $table->char('AgencyId', 36)->index('FK_AgencyApplications_AgencyId');
            $table->char('ApplicationAccountId', 36)->nullable();
            $table->char('ApplicationAgencyId', 36)->nullable();
            $table->char('Trainer', 36)->default('00000000-0000-0000-0000-000000000000');
            $table->char('BackupTrainer', 36)->default('00000000-0000-0000-0000-000000000000');
            $table->char('SalesPerson', 36)->default('00000000-0000-0000-0000-000000000000');
            $table->char('ImplementationSpecialist', 36)->default('00000000-0000-0000-0000-000000000000');
            $table->char('BackupImplementationSpecialist', 36)->default('00000000-0000-0000-0000-000000000000');
            $table->boolean('IsSuspended')->nullable()->default(0);
            $table->boolean('IsDeprecated')->default(0);
            $table->boolean('IsMigrating')->default(0);
            $table->boolean('IsFrozen')->default(0);
            $table->boolean('IsAgreementSigned')->default(0);
            $table->date('FrozenDate')->default('0001-01-01');
            $table->integer('TrialPeriod')->default(0);
            $table->integer('Package')->default(0);
            $table->integer('AnnualPlanId')->default(0);
            $table->date('Created')->default('0001-01-01');
            $table->boolean('ClusterId')->nullable()->default(1);
            $table->smallInteger('Application')->unsigned()->default(1)->index('FK_AgencyApplications_Application');
            $table->string('PreviousSoftware', 100)->default('');
            $table->boolean('AxxessPromotionOff')->default(0);
            $table->boolean('AgencyPromotionOff')->default(0);
            $table->date('SuspendDate')->default('0001-01-01');

            $table->unique(['AgencyId','Application'], 'UIX_Agency_Application');

            // $table->foreign('AgencyId', 'FK_AgencyApplications_AgencyId')->references('Id')->on('agencysnapshots')->onUpdate('CASCADE')->onDelete('CASCADE');
            // $table->foreign('Application', 'FK_AgencyApplications_Application')->references('Id')->on('applications')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('agencyapplications');
    }
}
