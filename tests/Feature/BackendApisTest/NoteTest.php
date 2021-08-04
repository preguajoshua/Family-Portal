<?php

namespace Tests\Feature\BackendApisTest;

use App\Models\Note;
use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    /** @test  */
    public function a_note_can_be_fetched()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('front/notes');

        $response->assertStatus(200)
            ->assertSuccessful();
    }

    /** @test  */
    public function a_user_can_create_note()
    {
        $this->markTestIncomplete('This test has not been completed yet.');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('front/notes', [
            '_StartDate' => '12/04/2020',
            '_StartTime' => '5:01 PM',
            'Title' => 'test'
        ]);

        $response->assertStatus(201)
            ->assertSuccessful([
                '_StartDate' => '12/04/2020',
                '_StartTime' => '5:01 PM',
                'Title' => 'test'
        ]);
    }

    /** @test  */
    public function a_user_can_update_a_note()
    {
        $this->markTestIncomplete('This test has not been completed yet.');

        $user = User::factory()->create();
        $note = Note::factory()->create();

        $response = $this->actingAs($user)->patchJson('front/notes/{$note->id}', [
            '_StartDate' => '12/04/2020',
            '_StartTime' => '5:01 PM',
        ]);

        $response->assertStatus(200)
            ->assertSuccessful();
    }

    /** @test  */
    public function a_user_can_get_a_single_note()
    {
        $this->markTestIncomplete('This test has not been completed yet.');

        $user = User::factory()->create();
        $note = Note::factory()->create();

        $response = $this->actingAs($user)->getJson('front/notes');

        $response->assertStatus(200)
            ->assertSuccessful()
            ->assertJson([
                '_StartDate' => '12/04/2020',
                '_StartTime' => '5:01 PM',
                'Title' => 'test',
                'Description' => 'no description',
                'PatientId' => $note->PatientId,
                'UserId' => '2f617af2-71a5-4974-bb91-4549942bb3e2'
            ]);
    }

    /** @test */
    public function user_can_delete_a_note()
    {
        $this->markTestIncomplete('This test has not been completed yet.');

        $user = User::factory()->create();

        $this->post('/notes', [
            '_StartDate' => '12/04/2020',
            '_StartTime' => '5:01 PM',
            'Title' => 'test'
        ]);
        $note = Note::first();
        $this->assertCount(1, Note::all());

        $response = $this->actingAs($user)->deleteJson("front/notes/{$note->id}", [
            '_StartDate' => '12/04/2020',
            '_StartTime' => '5:01 PM',
            'Title' => 'test'
        ]);

        $response->assertCount(0, Note::all());
    }

    // Unhappy path

    /** @test */
    public function ensure_the_request_is_validated_for_required_fields()
    {
        $this->markTestIncomplete('This test has not been completed yet.');

        $user = User::factory()->create();
        $note = Note::factory()->create([
            'UserId' => $user->id,
            'PatientId' => '00000000-0000-0000-0000-000000000001',
            'StartDate' => '2019-10-10 10:10:10',
            'Title' => 'Note Title',
            'Description' => 'The description of the note.',
        ]);

        $response = $this->actingAs($user)->patchJson("front/notes/{$note->id}");

        $response->assertStatus(422)
            ->assertJsonMissingValidationErrors([
                '_StartDate' => '',
                '_StartTime' => '',
                'Title' => '',
        ]);
    }
}
