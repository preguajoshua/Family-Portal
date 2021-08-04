<?php

namespace Tests\Feature;

use App\Models\Emr;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Patient;
use App\Models\AccountUser;
use Tests\assets\UserApiFixtures;
use Illuminate\Support\Facades\Http;
use App\Models\Membership\UserApplication;
use App\Models\Membership\AgencyApplications;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiV2Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Testing API token.
     *
     * @var  string
     */
    private $apiToken;

    /**
     * Run before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshSushiDatabase();
        Emr::reboot();
        Emr::truncate();

        $this->apiToken = Emr::factory()
            ->create(['application_id' => UserApiFixtures::APPLICATION_ID])
            ->createToken($name = 'testing-token')
            ->plainTextToken;
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->clearSushiTestCache();
        Emr::reboot();

        parent::tearDown();
    }

    /** @test  */
    public function a_new_user_and_associated_resources_are_created()
    {
        Http::fake([
            '*/connect/token' => Http::response(UserApiFixtures::FAKE_IDENTITY_TOKEN, 200),
            '*/invites/end-users/create' => Http::response(['subjectId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID], 200),
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $this->assertDatabaseHas('accounts', [
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseHas('userapplications', [
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function associated_user_resources_are_created_when_the_user_already_exists()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('accounts', [
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseHas('userapplications', [
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function associated_user_resources_are_created_when_the_user_and_account_already_exist()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseHas('userapplications', [
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function associated_user_resources_are_created_when_the_user_and_account_relationship_already_exist()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user->accounts()->attach($account->id);
        UserApplication::factory()->create([
            'UserId' => $user->id,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseCount('userapplications', 1);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function a_new_patient_can_be_added_to_an_existing_user_and_account_relationship()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user->accounts()->attach($account->id);
        UserApplication::factory()->create([
            'UserId' => $user->id,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::first(),
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => '00000000-0000-0000-0000-000000000001',
            'patient_contact_id' => '00000000-0000-0000-0000-000000000002',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseCount('userapplications', 1);
        $this->assertDatabaseCount('patients', 2);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => '00000000-0000-0000-0000-000000000001',
            'emr_patient_contact_id' => '00000000-0000-0000-0000-000000000002',
        ]);
    }

    /** @test  */
    public function a_new_user_and_associated_resources_are_not_created_when_they_already_exist()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user->accounts()->attach($account->id);
        UserApplication::factory()->create([
            'UserId' => $user->id,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::first(),
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(409);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseCount('userapplications', 1);
        $this->assertDatabaseCount('patients', 1);
    }

    /** @test  */
    public function a_new_user_and_associated_resources_are_created_for_a_hospice_emr()
    {
        Http::fake([
            '*/connect/token' => Http::response(UserApiFixtures::FAKE_IDENTITY_TOKEN, 200),
            '*/invites/end-users/create' => Http::response(['subjectId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID], 200),
        ]);
        Emr::factory()->create(['application_id' => UserApplication::APP_HOSPICE]);
        AgencyApplications::factory()->create([
            'ApplicationAccountId' => UserApiFixtures::HOSPICE_ACCOUNT_ID,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::HOSPICE_ACCOUNT_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApplication::APP_HOSPICE,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $this->assertDatabaseHas('accounts', [
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApplication::APP_HOSPICE,
        ]);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseHas('userapplications', [
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function a_patient_can_be_deleted()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user->accounts()->attach($account->id);
        UserApplication::factory()->create([
            'UserId' => $user->id,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::first(),
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response = $this->withToken($this->apiToken)->deleteJson('/api/v2/patients', [
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseCount('userapplications', 1);
        $this->assertDatabaseMissing('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function a_patient_can_be_moved_to_another_account()
    {
        Http::fake([
            '*/connect/token' => Http::response(UserApiFixtures::FAKE_IDENTITY_TOKEN, 200),
            '*/invites/end-users/create' => Http::response(['subjectId' => '00000000-0000-0000-0000-000000000002'], 200),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => '00000000-0000-0000-0000-000000000001',
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user->accounts()->attach($account->id);
        UserApplication::factory()->create([
            'UserId' => $user->id,
            'LoginId' => '00000000-0000-0000-0000-000000000001',
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::first(),
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response = $this->withToken($this->apiToken)->patchJson('/api/v2/patients', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'login_id' => '00000000-0000-0000-0000-000000000001',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'login_id' => '00000000-0000-0000-0000-000000000002',
        ]);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 2);
        $this->assertDatabaseHas('userapplications', [
            'LoginId' => '00000000-0000-0000-0000-000000000001',
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseHas('userapplications', [
            'LoginId' => '00000000-0000-0000-0000-000000000002',
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseCount('patients', 1);
        $this->assertDatabaseHas('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function a_patient_can_be_moved_to_another_existing_account()
    {
        Http::fake([
            '*' => Http::response('As the user is already created, the Identity endpoint should not be hit.', 500),
        ]);
        $user1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => '00000000-0000-0000-0000-000000000001',
        ]);
        $user2 = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'login_id' => '00000000-0000-0000-0000-000000000002',
        ]);
        $account1 = Account::factory()->create([
            'agency_name' => 'ACME Home Health 1',
            'agency_id' => '00000000-0000-0000-0000-000000000003',
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $account2 = Account::factory()->create([
            'agency_name' => 'ACME Home Health 2',
            'agency_id' => '00000000-0000-0000-0000-000000000004',
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user1->accounts()->attach($account1->id);
        $user1->accounts()->attach($account2->id);
        $user2->accounts()->attach($account1->id);
        UserApplication::factory()->create([
            'UserId' => $user1->id,
            'LoginId' => '00000000-0000-0000-0000-000000000001',
            'AgencyId' => '00000000-0000-0000-0000-000000000003',
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        UserApplication::factory()->create([
            'UserId' => $user1->id,
            'LoginId' => '00000000-0000-0000-0000-000000000001',
            'AgencyId' => '00000000-0000-0000-0000-000000000004',
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        UserApplication::factory()->create([
            'UserId' => $user2->id,
            'LoginId' => '00000000-0000-0000-0000-000000000002',
            'AgencyId' => '00000000-0000-0000-0000-000000000003',
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::where('user_id', $user1->id)->where('account_id', $account1->id)->first(),
            'emr_patient_id' => '00000000-0000-0000-0000-000000000005',
            'emr_patient_contact_id' => '00000000-0000-0000-0000-000000000055',
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::where('user_id', $user1->id)->where('account_id', $account1->id)->first(),
            'emr_patient_id' => '00000000-0000-0000-0000-000000000006',
            'emr_patient_contact_id' => '00000000-0000-0000-0000-000000000066',
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::where('user_id', $user1->id)->where('account_id', $account2->id)->first(),
            'emr_patient_id' => '00000000-0000-0000-0000-000000000007',
            'emr_patient_contact_id' => '00000000-0000-0000-0000-000000000077',
        ]);

        $response = $this->withToken($this->apiToken)->patchJson('/api/v2/patients', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'patient_id' => '00000000-0000-0000-0000-000000000005',
            'patient_contact_id' => '00000000-0000-0000-0000-000000000055',
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseCount('patients', 3);
        $this->assertDatabaseMissing('patients', [
            'account_user_id' => AccountUser::where('user_id', $user1->id)->where('account_id', $account1->id)->first(),
            'emr_patient_id' => '00000000-0000-0000-0000-000000000005',
            'emr_patient_contact_id' => '00000000-0000-0000-0000-000000000055',
        ]);
        $this->assertDatabaseHas('patients', [
            'account_user_id' => AccountUser::where('user_id', $user2->id)->where('account_id', $account1->id)->first()->id,
            'emr_patient_id' => '00000000-0000-0000-0000-000000000005',
            'emr_patient_contact_id' => '00000000-0000-0000-0000-000000000055',
        ]);
    }


    /* Unhappy Paths */

    /** @test  */
    public function user_resources_cannot_be_reached_without_an_api_token()
    {
        $response = $this->postJson('/api/v2/users');

        $response->assertStatus(401);
    }

    /** @test  */
    public function user_resources_cannot_be_reached_with_an_invalid_api_token()
    {
        $invalidApiToken = '9|ESTnsCMwMYindKvXFjmd56ea5t98gfhRbWfRHuGt989';

        $response = $this->withToken($invalidApiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Create user request validation provider.
     *
     * @return  array
     */
    public function createUserRequestValidationProvider()
    {
        return [
            ['name'],
            ['email'],
            ['agency_id'],
            ['agency_name'],
            ['application_id'],
            ['patient_id'],
            ['patient_contact_id'],
        ];
    }

    /**
     * @test
     * @dataProvider  createUserRequestValidationProvider
     */
    public function ensure_the_request_is_validated_for_required_fields($requestField)
    {
        Http::fake([
            '*' => Http::response('Expecting a validation error, so the Identity endpoint should not be hit.', 500),
        ]);
        $requestAttributes = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ];
        // Request fields are set to an empty string, one at a time, each itteration
        $requestAttributes[$requestField] = '';

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', $requestAttributes);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([$requestField], 'data');
    }

    /**
     * @test
     * @dataProvider  createUserRequestValidationProvider
     */
    public function ensure_the_request_is_validated_for_correct_type($requestField)
    {
        Http::fake([
            '*' => Http::response('Expecting a validation error, so the Identity endpoint should not be hit.', 500),
        ]);
        $requestAttributes = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ];
        // An integer is the incorrect type for all request fields
        $requestAttributes[$requestField] = 0;

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', $requestAttributes);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([$requestField], 'data');
    }

    /** @test  */
    public function ensure_the_request_is_validated_for_registered_emrs_only()
    {
        Http::fake([
            '*' => Http::response('Expecting a validation error, so the Identity endpoint should not be hit.', 500),
        ]);
        $unregisteredEmrId = 77;
        $this->assertTrue(Emr::where('application_id', $unregisteredEmrId)->doesntExist());
        $requestAttributes = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => $unregisteredEmrId,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ];

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', $requestAttributes);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['application_id'], 'data');
    }

    /** @test  */
    public function an_unauthorized_identity_request_is_handled()
    {
        Http::fake([
            '*/connect/token' => Http::response(UserApiFixtures::FAKE_IDENTITY_TOKEN, 200),
            '*/invites/end-users/create' => Http::response(null, 401),
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(500);
    }

    /** @test  */
    public function user_creation_is_rolled_back_on_trying_to_insert_duplicate_user()
    {
        Http::fake([
            '*/connect/token' => Http::response(UserApiFixtures::FAKE_IDENTITY_TOKEN, 200),
            '*/invites/end-users/create' => Http::response(['subjectId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID], 200),
        ]);
        User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'application' => UserApiFixtures::APPLICATION_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseMissing('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $this->assertDatabaseMissing('accounts', [
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $this->assertDatabaseCount('account_user', 0);
        $this->assertDatabaseMissing('userapplications', [
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        $this->assertDatabaseMissing('patients', [
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);
    }

    /** @test  */
    public function a_duplicate_patient_and_patient_contact_combination_can_not_be_added()
    {
        Http::fake([
            '*/connect/token' => Http::response(UserApiFixtures::FAKE_IDENTITY_TOKEN, 200),
            '*/invites/end-users/create' => Http::response(['subjectId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID], 200),
        ]);
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login_id' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
        ]);
        $account = Account::factory()->create([
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'application_id' => UserApiFixtures::APPLICATION_ID,
        ]);
        $user->accounts()->attach($account->id);
        UserApplication::factory()->create([
            'UserId' => $user->id,
            'AgencyId' => UserApiFixtures::AGENCY_ID,
            'LoginId' => UserApiFixtures::FAKE_IDENTITY_SUBJECT_ID,
            'Application' => UserApplication::APP_FAMILY_PORTAL,
        ]);
        Patient::factory()->create([
            'account_user_id' => AccountUser::first(),
            'emr_patient_id' => UserApiFixtures::PATIENT_ID,
            'emr_patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response = $this->withToken($this->apiToken)->postJson('/api/v2/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'agency_id' => UserApiFixtures::AGENCY_ID,
            'agency_name' => UserApiFixtures::AGENCY_NAME,
            'application_id' => UserApiFixtures::APPLICATION_ID,
            'patient_id' => UserApiFixtures::PATIENT_ID,
            'patient_contact_id' => UserApiFixtures::PATIENT_CONTACT_ID,
        ]);

        $response->assertStatus(500);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('accounts', 1);
        $this->assertDatabaseCount('account_user', 1);
        $this->assertDatabaseCount('userapplications', 1);
        $this->assertDatabaseCount('patients', 1);
    }
}
