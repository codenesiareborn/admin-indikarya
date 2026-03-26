<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectShift>
 */
class ProjectShiftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->randomElement(['Morning Shift', 'Afternoon Shift', 'Night Shift']),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'start_time' => now()->setHour(8)->setMinute(0),
            'end_time' => now()->setHour(17)->setMinute(0),
            'active_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'is_auto_generated' => false,
            'is_active' => true,
            'is_overnight' => false,
        ];
    }

    /**
     * Set shift as active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Set shift as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set shift as overnight.
     */
    public function overnight(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_overnight' => true,
            'start_time' => now()->setHour(20)->setMinute(0),
            'end_time' => now()->setHour(8)->setMinute(0),
        ]);
    }

    /**
     * Set shift as morning shift.
     */
    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Morning Shift',
            'start_time' => now()->setHour(7)->setMinute(0),
            'end_time' => now()->setHour(15)->setMinute(0),
        ]);
    }

    /**
     * Set shift as afternoon shift.
     */
    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Afternoon Shift',
            'start_time' => now()->setHour(15)->setMinute(0),
            'end_time' => now()->setHour(23)->setMinute(0),
        ]);
    }

    /**
     * Set shift as night shift.
     */
    public function night(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Night Shift',
            'start_time' => now()->setHour(23)->setMinute(0),
            'end_time' => now()->setHour(7)->setMinute(0),
            'is_overnight' => true,
        ]);
    }
}
