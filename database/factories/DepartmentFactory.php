<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'dept_name' => fake()->company(),
            'dept_code' => strtoupper(fake()->unique()->lexify('DEPT-???')),
            'manager_id' => User::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Configure department with a specific manager.
     */
    public function withManager(User $manager): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $manager->id,
        ]);
    }
}
