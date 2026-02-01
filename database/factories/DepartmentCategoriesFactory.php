<?php

namespace Database\Factories;

use App\Models\DepartmentCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentCategoriesFactory extends Factory
{
    protected $model = DepartmentCategories::class;

    public function definition(): array
    {
        return [
            'category_name' => fake()->word(),
            'description' => fake()->sentence(),
            'color_code' => fake()->safeHexColor(),
        ];
    }
}
