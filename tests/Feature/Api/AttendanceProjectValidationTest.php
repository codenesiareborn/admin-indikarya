<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\ProjectShift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AttendanceProjectValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveProject(): Project
    {
        return Project::factory()->active()->create([
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->addMonths(6),
        ]);
    }

    private function createExpiredProject(): Project
    {
        return Project::factory()->active()->create([
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->subDays(5),
        ]);
    }

    private function createInactiveProject(): Project
    {
        return Project::factory()->completed()->create();
    }

    private function attachProject(User $user, Project $project, array $attributes = []): void
    {
        $user->projects()->attach($project->id, array_merge([
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->addMonths(6),
        ], $attributes));
    }

    private function createActiveShift(Project $project): ProjectShift
    {
        return ProjectShift::factory()->create([
            'project_id' => $project->id,
            'is_active' => true,
            'start_time' => now()->setHour(8)->setMinute(0),
            'end_time' => now()->setHour(17)->setMinute(0),
        ]);
    }

    // ==================== CHECK-IN TESTS ====================

    public function test_user_cannot_check_in_to_expired_project(): void
    {
        $user = User::factory()->create();
        $expiredProject = $this->createExpiredProject();
        // Attach with expired tanggal_selesai on pivot table
        $this->attachProject($user, $expiredProject, [
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->subDay(),
        ]);
        $shift = $this->createActiveShift($expiredProject);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-in', [
                'project_id' => $expiredProject->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_user_cannot_check_in_to_inactive_project(): void
    {
        $user = User::factory()->create();
        $inactiveProject = $this->createInactiveProject();
        $this->attachProject($user, $inactiveProject);
        $shift = $this->createActiveShift($inactiveProject);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-in', [
                'project_id' => $inactiveProject->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_user_can_check_in_to_active_project(): void
    {
        $user = User::factory()->create();
        $activeProject = $this->createActiveProject();
        $this->attachProject($user, $activeProject);
        $shift = $this->createActiveShift($activeProject);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-in', [
                'project_id' => $activeProject->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Check-in berhasil');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'project_id' => $activeProject->id,
        ]);
    }

    public function test_user_cannot_check_in_to_unassigned_project(): void
    {
        $user = User::factory()->create();
        $project = $this->createActiveProject();
        $shift = $this->createActiveShift($project);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-in', [
                'project_id' => $project->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_user_cannot_check_in_when_tanggal_selesai_is_in_past(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->active()->create([
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->subDay(),
        ]);
        $this->attachProject($user, $project, [
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->subDay(),
        ]);
        $shift = $this->createActiveShift($project);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-in', [
                'project_id' => $project->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    // ==================== CHECK-OUT TESTS ====================

    public function test_user_cannot_check_out_from_expired_project(): void
    {
        $user = User::factory()->create();
        $expiredProject = $this->createExpiredProject();
        // Attach with expired tanggal_selesai on pivot table
        $this->attachProject($user, $expiredProject, [
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->subDay(),
        ]);
        $shift = $this->createActiveShift($expiredProject);

        // First create a valid check-in
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'project_id' => $expiredProject->id,
            'shift_id' => $shift->id,
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'check_in_photo' => 'test/path.jpg',
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-out', [
                'project_id' => $expiredProject->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_user_cannot_check_out_from_inactive_project(): void
    {
        $user = User::factory()->create();
        $inactiveProject = $this->createInactiveProject();
        $this->attachProject($user, $inactiveProject);
        $shift = $this->createActiveShift($inactiveProject);

        // First create a valid check-in
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'project_id' => $inactiveProject->id,
            'shift_id' => $shift->id,
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'check_in_photo' => 'test/path.jpg',
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-out', [
                'project_id' => $inactiveProject->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_user_can_check_out_from_active_project(): void
    {
        $user = User::factory()->create();
        $activeProject = $this->createActiveProject();
        $this->attachProject($user, $activeProject);
        $shift = $this->createActiveShift($activeProject);

        // First create a valid check-in
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'project_id' => $activeProject->id,
            'shift_id' => $shift->id,
            'tanggal' => now()->toDateString(),
            'check_in' => '08:00',
            'check_in_photo' => 'test/path.jpg',
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-out', [
                'project_id' => $activeProject->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Check-out berhasil');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'project_id' => $activeProject->id,
        ]);

        $attendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('project_id', $activeProject->id)
            ->latest('created_at')
            ->first();

        $this->assertNotNull($attendance->check_out);
    }

    public function test_user_cannot_check_out_from_unassigned_project(): void
    {
        $user = User::factory()->create();
        $project = $this->createActiveProject();
        $shift = $this->createActiveShift($project);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attendances/check-out', [
                'project_id' => $project->id,
                'shift_id' => $shift->id,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Test Address',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    // ==================== HISTORY TESTS ====================

    public function test_user_can_access_history_from_expired_project(): void
    {
        $user = User::factory()->create();
        $expiredProject = $this->createExpiredProject();
        $this->attachProject($user, $expiredProject);
        $shift = $this->createActiveShift($expiredProject);

        // Create past attendance record
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'project_id' => $expiredProject->id,
            'shift_id' => $shift->id,
            'tanggal' => now()->subDays(30)->toDateString(),
            'check_in' => '08:00',
            'check_out' => '17:00',
            'check_in_photo' => 'test/path.jpg',
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/attendances/history');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($expiredProject->id, $response->json('data.0.project_id'));
    }

    public function test_user_can_access_history_from_inactive_project(): void
    {
        $user = User::factory()->create();
        $inactiveProject = $this->createInactiveProject();
        $this->attachProject($user, $inactiveProject);
        $shift = $this->createActiveShift($inactiveProject);

        // Create past attendance record
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'project_id' => $inactiveProject->id,
            'shift_id' => $shift->id,
            'tanggal' => now()->subDays(30)->toDateString(),
            'check_in' => '08:00',
            'check_out' => '17:00',
            'check_in_photo' => 'test/path.jpg',
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/attendances/history');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($inactiveProject->id, $response->json('data.0.project_id'));
    }
}
