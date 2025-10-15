<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Bandeira; 

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bandeira>
 */
class BandeiraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Bandeira::class; 
    
    public function definition(): array
    {
        return [
            'nome' => $this->faker->company() 
        ];
    }
}
