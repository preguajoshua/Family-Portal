<?php

namespace Database\Factories\Membership;

use App\Models\Membership\UserApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'Id' => $this->faker->uuid,
            'UserId' => $this->faker->uuid,
            'AgencyId' => $this->faker->uuid,
            'LoginId' => $this->faker->uuid,
            'Application' => $this->faker->randomDigit,
            'Status' => 1, // ?
            'Created' => today(),
        ];
    }
}
