<?php

namespace Database\Factories;

use App\Enums\PhaseStatus;
use App\Enums\ProductionRequestPhase;
use App\Enums\RequestType;
use App\Models\ProductionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionRequest>
 */
class ProductionRequestFactory extends Factory
{
    protected $model = ProductionRequest::class;

    public function definition(): array
    {
        return [
            'project_name' => fake()->words(3, true),
            'client_id' => \App\Models\Client::factory(),
            'request_type' => RequestType::Direct->value,
            'current_phase' => ProductionRequestPhase::FactoryIntake->value,
            'phase_status' => PhaseStatus::Pending->value,
            'created_by' => \App\Models\User::factory(),
        ];
    }

    public function indirect(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => RequestType::Indirect->value,
            'current_phase' => ProductionRequestPhase::ShowroomReview->value,
        ]);
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => RequestType::Direct->value,
            'current_phase' => ProductionRequestPhase::FactoryIntake->value,
        ]);
    }
}

