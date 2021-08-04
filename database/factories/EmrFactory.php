<?php

namespace Database\Factories;

use App\Models\Emr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmrFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Emr::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name;

        return [
            'name' => $name,
            'key' => Str::slug($name),
            'application_id' => $this->faker->randomDigit,
        ];
    }
}
