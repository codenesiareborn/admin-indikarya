<?php

namespace Tests\Unit\Models\User;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetActiveProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_active_project_returns_most_recently_started_project()
    {
        $user = User::factory()->create();

        // Create old project
        $oldProject = Project::factory()->create([
            'status' => 'aktif',
            'tanggal_mulai' => now()->subMonths(2),
        ]);
        $user->projects()->attach($oldProject->id, [
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => null,
        ]);

        // Create new project
        $newProject = Project::factory()->create([
            'status' => 'aktif',
            'tanggal_mulai' => now()->subDays(7),
        ]);
        $user->projects()->attach($newProject->id, [
            'tanggal_mulai' => now()->subDays(7),
            'tanggal_selesai' => null,
        ]);

        // Should return the most recently started project
        $activeProject = $user->getActiveProject();

        $this->assertNotNull($activeProject);
        $this->assertEquals($newProject->id, $activeProject->id);
    }
}
