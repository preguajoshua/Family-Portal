<?php

namespace Database\Factories\Membership;

use App\Models\Membership\AgencyApplications;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgencyApplicationsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AgencyApplications::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'AgencyId' => $this->faker->uuid,
        ];
    }
}
