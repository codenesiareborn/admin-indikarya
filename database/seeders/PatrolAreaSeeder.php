<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\PatrolArea;

class PatrolAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all security services projects
        $securityProjects = Project::where('jenis_project', 'security_services')
            ->where('status', 'aktif')
            ->get();

        foreach ($securityProjects as $project) {
            $areas = [
                [
                    'kode_area' => 'PT-AD-' . str_pad($project->id, 2, '0', STR_PAD_LEFT),
                    'nama_area' => 'Area Depan',
                    'deskripsi' => 'Area depan gedung/lokasi',
                    'urutan' => 1,
                ],
                [
                    'kode_area' => 'PT-AB-' . str_pad($project->id, 2, '0', STR_PAD_LEFT),
                    'nama_area' => 'Area Belakang',
                    'deskripsi' => 'Area belakang gedung/lokasi',
                    'urutan' => 2,
                ],
                [
                    'kode_area' => 'PT-AS-' . str_pad($project->id, 2, '0', STR_PAD_LEFT),
                    'nama_area' => 'Area Samping',
                    'deskripsi' => 'Area samping gedung/lokasi',
                    'urutan' => 3,
                ],
                [
                    'kode_area' => 'PT-AT-' . str_pad($project->id, 2, '0', STR_PAD_LEFT),
                    'nama_area' => 'Area Tengah',
                    'deskripsi' => 'Area tengah gedung/lokasi',
                    'urutan' => 4,
                ],
                [
                    'kode_area' => 'PT-AP-' . str_pad($project->id, 2, '0', STR_PAD_LEFT),
                    'nama_area' => 'Area Parkir',
                    'deskripsi' => 'Area parkir kendaraan',
                    'urutan' => 5,
                ],
            ];

            foreach ($areas as $area) {
                PatrolArea::create([
                    'project_id' => $project->id,
                    'kode_area' => $area['kode_area'],
                    'nama_area' => $area['nama_area'],
                    'deskripsi' => $area['deskripsi'],
                    'status' => 'aktif',
                    'urutan' => $area['urutan'],
                ]);
            }
        }
    }
}
