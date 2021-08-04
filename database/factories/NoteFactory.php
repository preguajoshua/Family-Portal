<?php

namespace Database\Factories;

use DateInterval;
use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $startDate = $this->faker->dateTimeThisMonth();
        $endDate = ($this->faker->boolean) ? $startDate : $startDate->add(new DateInterval('P1D'));

        return [
            'Id' => $this->faker->uuid,
            'UserId' => $this->faker->uuid,
            'PatientId' => $this->faker->uuid,
            'Title' => $this->faker->sentence(),
            'Description' => $this->faker->paragraph(),
            'Completed' => $this->faker->boolean,
            'StartDate' => $startDate->format('Y-m-d H:i:s'),
            'EndDate' => $endDate->format('Y-m-d H:i:s'),
        ];
    }
}
