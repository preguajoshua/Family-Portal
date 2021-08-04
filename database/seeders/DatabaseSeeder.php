<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Note;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create([
            'login_id' => '2f617af2-71a5-4974-bb91-4549942bb3e2',
            'name' => 'Demo',
            'email' => 'test@axxess.com',
            'password' => '$2y$10$tsl6NDR7UdpiPygufPkdTuXjMqtsmmLw4ooJR12Jb7R8jSKpZEki6', // vagrant
            'customer_id' => '821902c0-9378-11e9-bc13-b98f35c5177e',
            'application' => '2',
            'cluster' => '1',
        ]);

        Note::factory()->create([
            'UserId' => $user->id,
            'PatientId' => 'bb376207-5b86-4d5f-b51c-f4bb4b4096f6',
        ]);
        Note::factory()->count(2)->create([
            'UserId' => $user->id,
            'PatientId' => '77a6ad5d-ca1c-4f21-a129-870b663d3671',
        ]);
        Note::factory()->count(2)->create([
            'UserId' => $user->id,
            'PatientId' => 'b132310e-d2d0-4ca0-9889-c48970abf2b4',
        ]);
    }
}
