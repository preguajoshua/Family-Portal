<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Client;
use Illuminate\Support\Facades\Http;
use App\Services\HomeCareApiPermissions;

class HomecareApiPermissionsTest extends TestCase
{
    /**
     * User permissions.
     *
     * @var  array
     */
    protected $familyPortalUserPermission;

    /**
     * Run before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->familyPortalUserPermission = [
            'familyPortalUserId' => '00000000-0000-0000-0000-000000000000',
            'agencyId' => '00000000-0000-0000-0000-000000000000',
            'patientId' => '00000000-0000-0000-0000-000000000000',
            'isPayor' => false,
            'isAgencyBankAccountSetup' => false,
            'isPatientAccountSetup' => false,
            'familyViewDocumentationAccess' => false,
        ];
    }

    /** @test  */
    public function the_flag_for_viewing_documentation_can_be_set()
    {
        $client = Client::factory()->make(['canViewDocumentation' => false]);
        $this->familyPortalUserPermission['familyViewDocumentationAccess'] = true;
        Http::fake(['*' => Http::response(['familyPortalUserPermission' => $this->familyPortalUserPermission], 200)]);
        $homeCareApiPermissions = new HomeCareApiPermissions($client);
        $this->assertFalse($client->canViewDocumentation);

        $homeCareApiPermissions->loadAndCache();

        $this->assertTrue($client->canViewDocumentation);
    }

    /** @test  */
    public function the_flag_for_viewing_documentation_can_be_cleared()
    {
        $client = Client::factory()->make(['canViewDocumentation' => true]);
        $this->familyPortalUserPermission['familyViewDocumentationAccess'] = false;
        Http::fake(['*' => Http::response(['familyPortalUserPermission' => $this->familyPortalUserPermission], 200)]);
        $homeCareApiPermissions = new HomeCareApiPermissions($client);
        $this->assertTrue($client->canViewDocumentation);

        $homeCareApiPermissions->loadAndCache();

        $this->assertFalse($client->canViewDocumentation);
    }

    /** @test  */
    public function the_app_can_handle_a_missing_view_documentation_flag()
    {
        $client = Client::factory()->make(['canViewDocumentation' => false]);
        unset($this->familyPortalUserPermission['familyViewDocumentationAccess']);
        Http::fake(['*' => Http::response(['familyPortalUserPermission' => $this->familyPortalUserPermission], 200)]);
        $homeCareApiPermissions = new HomeCareApiPermissions($client);
        $this->assertFalse($client->canViewDocumentation);

        $homeCareApiPermissions->loadAndCache();

        $this->assertFalse($client->canViewDocumentation);
    }
}
