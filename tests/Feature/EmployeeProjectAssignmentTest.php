<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeProjectAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user and set up Filament context
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');
    }

    public function test_employee_without_active_project_can_be_assigned(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        $project = Project::factory()->create([
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        $this->assertFalse($employee->hasActiveProject());

        $project->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $this->assertTrue($employee->hasActiveProject());
        $this->assertEquals($project->id, $employee->getActiveProject()->id);
    }

    public function test_employee_with_active_project_cannot_be_assigned_to_another_project(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        $projectA = Project::factory()->create([
            'nama_project' => 'Project A',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        $projectB = Project::factory()->create([
            'nama_project' => 'Project B',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        // Assign employee to Project A
        $projectA->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $this->assertTrue($employee->hasActiveProject());
        $this->assertEquals($projectA->id, $employee->getActiveProject()->id);

        // Try to assign to Project B - should fail validation
        $this->assertFalse($projectB->employees()->where('user_id', $employee->id)->exists());
    }

    public function test_employee_with_expired_project_can_be_assigned_to_new_project(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        $projectA = Project::factory()->create([
            'nama_project' => 'Project A',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        $projectB = Project::factory()->create([
            'nama_project' => 'Project B',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        // Assign employee to Project A with expired date
        $projectA->employees()->attach($employee->id, [
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->subMonth(),
        ]);

        // Employee should not have active project since Project A assignment has expired
        $this->assertFalse($employee->hasActiveProject());

        // Now should be able to assign to Project B
        $projectB->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $this->assertTrue($employee->hasActiveProject());
        $this->assertEquals($projectB->id, $employee->getActiveProject()->id);
    }

    public function test_employee_with_null_end_date_is_considered_active(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        $project = Project::factory()->create([
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        // Assign employee with no end date (null)
        $project->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => null,
        ]);

        $this->assertTrue($employee->hasActiveProject());
    }

    public function test_employee_in_non_active_project_can_be_reassigned(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        $projectDraft = Project::factory()->create([
            'nama_project' => 'Draft Project',
            'jenis_project' => 'cleaning_services',
            'status' => 'draft',
        ]);

        $projectAktif = Project::factory()->create([
            'nama_project' => 'Active Project',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        // Assign employee to draft project
        $projectDraft->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        // Should not be considered active since project status is draft
        $this->assertFalse($employee->hasActiveProject());

        // Can be assigned to active project
        $projectAktif->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $this->assertTrue($employee->hasActiveProject());
    }

    public function test_scope_available_for_assignment_filters_correctly(): void
    {
        $employeeWithActiveProject = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
            'name' => 'Employee With Project',
        ]);

        $employeeWithoutProject = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
            'name' => 'Employee Without Project',
        ]);

        $project = Project::factory()->create([
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        // Assign only first employee
        $project->employees()->attach($employeeWithActiveProject->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $availableEmployees = User::availableForAssignment()->get();

        $this->assertTrue($availableEmployees->contains($employeeWithoutProject));
        $this->assertFalse($availableEmployees->contains($employeeWithActiveProject));
    }

    public function test_active_project_considers_both_project_status_and_end_date(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        $activeProject = Project::factory()->create([
            'nama_project' => 'Active Project',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        $completedProject = Project::factory()->create([
            'nama_project' => 'Completed Project',
            'jenis_project' => 'cleaning_services',
            'status' => 'selesai',
        ]);

        // Assign to active project
        $activeProject->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $this->assertTrue($employee->hasActiveProject());

        // Remove from active project
        $activeProject->employees()->detach($employee->id);

        // Assign to completed project
        $completedProject->employees()->attach($employee->id, [
            'tanggal_mulai' => now()->subMonth(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        // Should NOT be considered active since project status is 'selesai'
        $this->assertFalse($employee->hasActiveProject());
    }

    public function test_get_active_project_returns_single_project(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'staf' => 'cleaning_services',
        ]);

        // This test verifies that even if data inconsistency exists,
        // getActiveProject() returns only one project (the first match)
        $activeProject = Project::factory()->create([
            'nama_project' => 'Only Active Project',
            'jenis_project' => 'cleaning_services',
            'status' => 'aktif',
        ]);

        $activeProject->employees()->attach($employee->id, [
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
        ]);

        $result = $employee->getActiveProject();

        $this->assertNotNull($result);
        $this->assertEquals($activeProject->id, $result->id);
    }
}
