<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ProductionRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'production_request_id' => ProductionRequest::factory(),
            'client_id' => Client::factory(),
            'project_name' => fake()->words(3, true),
            'status' => 'active',
            'created_by' => User::factory(),
            'start_date' => now(),
            'end_date' => now()->addMonths(2),
        ];
    }

    /**
     * Create project linked to specific production request.
     */
    public function forRequest(ProductionRequest $request): static
    {
        return $this->state(fn (array $attributes) => [
            'production_request_id' => $request->id,
            'client_id' => $request->client_id,
        ]);
    }
}
