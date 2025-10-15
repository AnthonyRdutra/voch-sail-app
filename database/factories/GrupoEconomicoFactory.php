<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GrupoEconomico; 

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GrupoEconomico>
 */
class GrupoEconomicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = GrupoEconomico::class; 
    
    public function definition()
    {
        return ['nome' => $this->faker->company()] ;
    }
}
