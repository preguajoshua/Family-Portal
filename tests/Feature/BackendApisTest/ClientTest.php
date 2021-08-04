<?php

namespace Tests\Feature\BackendApisTest;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    /** @test  */
    function a_client_can_be_fetched()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('front/clients');

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors();
    }

    /** @test  */
    function a_client_can_not_be_fetched_without_authentication()
    {
        $response = $this->getJson('front/clients');

        $response->assertStatus(401);
    }
}
