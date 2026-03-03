<?php

namespace Tests\Feature\Api;

use App\Models\PatrolArea;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PatrolFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_patrol_flow_with_project_reassignment()
    {
        $user = User::factory()->create();

        // Step 1: User assigned to Project A
        $projectA = Project::factory()->create([
            'status' => 'aktif',
            'nama_project' => 'Project A',
        ]);
        $user->projects()->attach($projectA->id, [
            'tanggal_mulai' => now()->subMonth(),
            'tanggal_selesai' => null,
        ]);

        // Create patrol area for Project A
        PatrolArea::create([
            'project_id' => $projectA->id,
            'kode_area' => 'A1',
            'nama_area' => 'Area A1',
            'deskripsi' => 'Test area A1',
            'status' => 'aktif',
            'urutan' => 1,
        ]);

        // Step 2: Submit patrol to Project A
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/patrols', [
                'project_id' => $projectA->id,
                'area_name' => 'Area A1',
                'area_code' => 'A1',
                'status' => 'Aman',
                'photo' => UploadedFile::fake()->image('test.jpg'),
            ]);

        $response->assertStatus(201);
        $patrolId = $response->json('data.id');

        // Verify patrol saved with correct project
        $this->assertDatabaseHas('patrols', [
            'id' => $patrolId,
            'user_id' => $user->id,
            'project_id' => $projectA->id,
        ]);

        // Step 3: Reassign user to Project B (close Project A)
        $user->projects()->updateExistingPivot($projectA->id, [
            'tanggal_selesai' => now()->subDay()->toDateString(),
        ]);

        $projectB = Project::factory()->create([
            'status' => 'aktif',
            'nama_project' => 'Project B',
        ]);
        $user->projects()->attach($projectB->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => null,
        ]);

        // Create patrol area for Project B
        PatrolArea::create([
            'project_id' => $projectB->id,
            'kode_area' => 'B1',
            'nama_area' => 'Area B1',
            'deskripsi' => 'Test area B1',
            'status' => 'aktif',
            'urutan' => 1,
        ]);

        // Step 4: Get patrol areas (should return Project B areas)
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/patrols/areas?project_id={$projectB->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Area B1');

        // Step 5: Submit patrol to Project B
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/patrols', [
                'project_id' => $projectB->id,
                'area_name' => 'Area B1',
                'area_code' => 'B1',
                'status' => 'Aman',
                'photo' => UploadedFile::fake()->image('test.jpg'),
            ]);

        $response->assertStatus(201);

        // Verify new patrol saved to Project B
        $this->assertDatabaseHas('patrols', [
            'user_id' => $user->id,
            'project_id' => $projectB->id,
        ]);

        // Step 6: Verify cannot submit to old Project A
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/patrols', [
                'project_id' => $projectA->id,
                'area_name' => 'Area A1',
                'area_code' => 'A1',
                'status' => 'Aman',
                'photo' => UploadedFile::fake()->image('test2.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }
}
