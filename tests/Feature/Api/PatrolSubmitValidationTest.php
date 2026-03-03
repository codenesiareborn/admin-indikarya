<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PatrolSubmitValidationTest extends TestCase
{
  use RefreshDatabase;

  public function test_cannot_submit_patrol_to_unassigned_project()
  {
    $user = User::factory()->create();

    // Project user is assigned to
    $assignedProject = Project::factory()->create(['status' => 'aktif']);
    $user->projects()->attach($assignedProject->id, [
      'tanggal_mulai' => now(),
      'tanggal_selesai' => null,
    ]);

    // Project user is NOT assigned to
    $unassignedProject = Project::factory()->create(['status' => 'aktif']);

    $response = $this->actingAs($user, 'sanctum')
      ->postJson('/api/patrols', [
        'project_id' => $unassignedProject->id,
        'area_name' => 'Test Area',
        'area_code' => 'TA-001',
        'status' => 'Aman',
        'photo' => UploadedFile::fake()->image('test.jpg'),
      ]);

    $response->assertStatus(422)
      ->assertJsonValidationErrors(['project_id']);
  }

  public function test_can_submit_patrol_to_assigned_active_project()
  {
    $user = User::factory()->create();

    $project = Project::factory()->create(['status' => 'aktif']);
    $user->projects()->attach($project->id, [
      'tanggal_mulai' => now(),
      'tanggal_selesai' => null,
    ]);

    $response = $this->actingAs($user, 'sanctum')
      ->postJson('/api/patrols', [
        'project_id' => $project->id,
        'area_name' => 'Test Area',
        'area_code' => 'TA-001',
        'status' => 'Aman',
        'photo' => UploadedFile::fake()->image('test.jpg'),
      ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('patrols', [
      'user_id' => $user->id,
      'project_id' => $project->id,
    ]);
  }
}
