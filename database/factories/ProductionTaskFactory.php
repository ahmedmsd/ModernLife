<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\ProductionTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionTask>
 */
class ProductionTaskFactory extends Factory
{
    protected $model = ProductionTask::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'department_id' => Department::factory(),
            'status' => 'pending',
            'due_date' => now()->addWeeks(2),
        ];
    }

    /**
     * Task in pending status awaiting assignment.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Task received by department manager.
     */
    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'received',
            'received_at' => now(),
        ]);
    }

    /**
     * Task waiting for materials.
     */
    public function materialsWait(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'materials_wait',
        ]);
    }

    /**
     * Task with materials done, waiting to start production.
     */
    public function waitingProduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waiting_production',
        ]);
    }

    /**
     * Task in progress (manufacturing).
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'actual_start_at' => now(),
        ]);
    }

    /**
     * Task under QA review.
     */
    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_review',
        ]);
    }

    /**
     * Task approved by QA.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Assign task to a specific department and user.
     */
    public function assignedTo(Department $department, ?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $department->dept_id,
            'assigned_to_user_id' => $user?->id ?? $department->manager_id,
            'assigned_at' => now(),
            'current_owner_role' => 'department_manager',
            'current_owner_user_id' => $user?->id ?? $department->manager_id,
            'sent_to_owner_at' => now(),
        ]);
    }

    /**
     * Set current owner role.
     */
    public function ownedBy(string $role, ?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'current_owner_role' => $role,
            'current_owner_user_id' => $user?->id,
            'sent_to_owner_at' => now(),
        ]);
    }
}
