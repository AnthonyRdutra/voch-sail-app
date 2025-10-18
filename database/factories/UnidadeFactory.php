<?php

namespace Database\Factories;

use App\Models\Bandeira;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Unidade;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unidade>
 */
class UnidadeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Unidade::class; 
    
    public function definition(): array
    {
        return [
            'nome_fantasia' => $this->faker->company(),
            'razao_social' => $this->faker->companySuffix(),
            'cnpj' => $this->faker->numerify('##.###.###/####-##'),
            'bandeira_id' => Bandeira::factory()
        ];
    }
}
