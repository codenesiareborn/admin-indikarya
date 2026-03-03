<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_project' => fake()->company().' Project',
            'jenis_project' => fake()->randomElement(['cleaning_services', 'security_services']),
            'alamat_lengkap' => fake()->address(),
            'nilai_kontrak' => fake()->randomFloat(2, 1000000, 100000000),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonths(6),
            'status' => 'draft',
            'enable_attendance_status' => true,
            'auto_mark_alpha' => false,
        ];
    }

    /**
     * Set project as active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aktif',
        ]);
    }

    /**
     * Set project as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
        ]);
    }

    /**
     * Set project type to cleaning services.
     */
    public function cleaningServices(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_project' => 'cleaning_services',
        ]);
    }

    /**
     * Set project type to security services.
     */
    public function securityServices(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_project' => 'security_services',
        ]);
    }
}
