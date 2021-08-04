<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Facades\Identity;
use Illuminate\Support\Str;

class IdentityTest extends TestCase
{
    /** @test  */
    public function an_identity_token_can_be_retrieved()
    {
        $bearerToken = Identity::bearerToken();

        $this->assertIsString($bearerToken);
        $this->assertTrue(Str::startsWith($bearerToken, 'Bearer'));
    }
}
