<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'Id' => $this->faker->uuid,
            'FirstName' => $this->faker->firstName,
            'LastName' => $this->faker->lastName,
            'MiddleInitial' => ($this->faker->boolean()) ? $this->faker->randomLetter : null,
            'DOB' => $this->faker->dateTimeBetween($startDate = '-50 years', $endDate = '-20 years')->format('Y-m-d'),
            'Gender' => $this->faker->randomElement(['Male', 'Female']),
            'PhotoId' => $this->faker->uuid,
            'AgencyId' => $this->faker->uuid,
            'AgencyLocationId' => $this->faker->uuid,
            'PrimaryPhone' => $this->faker->phoneNumber(),
            'SecondaryPhone' => null,
            'EmailAddress' => $this->faker->email,
            'StartofCareDate' => $this->faker->dateTimeThisYear(),
            'ContactPayor' => false,
            'ContactPrimary' => false,
        ];
    }


    public function payor()
    {
        return $this->state([
            'ContactPayor' => true,
        ]);
    }
}
