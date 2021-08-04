<?php

namespace Tests\Feature\BackendApisTest;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use App\Models\Membership\UserApplication;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhysicianTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A user to authenticate with.
     *
     * @var  \App\Models\User
     */
    protected $user;

    /**
     * Run before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Session::put('emrId', UserApplication::APP_HOME_CARE);
    }

    /** @test  */
    function a_physicians_record_can_be_fetched()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->getJson('front/physicians');

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors();
    }
}
