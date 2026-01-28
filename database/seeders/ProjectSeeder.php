<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectRoom;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Patrol;
use App\Models\PatrolArea;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'nama_project' => 'RS Harapan Sehat',
                'jenis_project' => 'cleaning_services',
                'alamat_lengkap' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'nilai_kontrak' => 150000000,
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'jam_masuk' => '07:00:00',
                'jam_keluar' => '17:00:00',
                'status' => 'aktif',
            ],
            [
                'nama_project' => 'Mall Grand Indonesia',
                'jenis_project' => 'cleaning_services',
                'alamat_lengkap' => 'Jl. MH Thamrin No. 1, Jakarta Pusat',
                'nilai_kontrak' => 200000000,
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'jam_masuk' => '06:00:00',
                'jam_keluar' => '18:00:00',
                'status' => 'aktif',
            ],
            [
                'nama_project' => 'Gedung Perkantoran BCA',
                'jenis_project' => 'security_services',
                'alamat_lengkap' => 'Jl. Jend. Sudirman Kav. 22-23, Jakarta Selatan',
                'nilai_kontrak' => 180000000,
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'jam_masuk' => '07:00:00',
                'jam_keluar' => '19:00:00',
                'status' => 'aktif',
            ],
            [
                'nama_project' => 'Apartemen Taman Anggrek',
                'jenis_project' => 'security_services',
                'alamat_lengkap' => 'Jl. Letjen S. Parman Kav. 21, Jakarta Barat',
                'nilai_kontrak' => 120000000,
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'jam_masuk' => '06:00:00',
                'jam_keluar' => '18:00:00',
                'status' => 'aktif',
            ],
            [
                'nama_project' => 'Hotel Mulia Senayan',
                'jenis_project' => 'cleaning_services',
                'alamat_lengkap' => 'Jl. Asia Afrika Senayan, Jakarta Pusat',
                'nilai_kontrak' => 250000000,
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => '2026-12-31',
                'jam_masuk' => '07:00:00',
                'jam_keluar' => '17:00:00',
                'status' => 'aktif',
            ],
        ];

        foreach ($projects as $index => $projectData) {
            $this->command->info("Creating project: {$projectData['nama_project']}");
            
            $project = Project::create($projectData);

            // Create Rooms
            $rooms = $this->createRooms($project);
            
            // Create Tasks for each room
            foreach ($rooms as $room) {
                $this->createTasks($room);
            }

            // Assign Employees
            $employees = $this->assignEmployees($project);

            // Create 30 days attendance
            $this->createAttendance($project, $employees);

            // Create Patrol Areas and Patrols for security projects
            if ($project->jenis_project === 'security_services') {
                $patrolAreas = $this->createPatrolAreas($project);
                $this->createPatrols($project, $employees, $patrolAreas);
            }

            $this->command->info("✅ Project {$projectData['nama_project']} completed with rooms, tasks, attendance" . ($project->jenis_project === 'security_services' ? ', and patrols' : ''));
        }
    }

    private function createRooms(Project $project): array
    {
        $roomTemplates = [
            ['nama_ruangan' => 'Lobby', 'lantai' => '1', 'status' => 'aktif'],
            ['nama_ruangan' => 'Ruang Tunggu', 'lantai' => '1', 'status' => 'aktif'],
            ['nama_ruangan' => 'Toilet Lantai 1', 'lantai' => '1', 'status' => 'aktif'],
            ['nama_ruangan' => 'Koridor Lantai 2', 'lantai' => '2', 'status' => 'aktif'],
            ['nama_ruangan' => 'Ruang Meeting', 'lantai' => '2', 'status' => 'aktif'],
        ];

        $rooms = [];
        foreach ($roomTemplates as $roomData) {
            $rooms[] = ProjectRoom::create([
                'project_id' => $project->id,
                'nama_ruangan' => $roomData['nama_ruangan'],
                'lantai' => $roomData['lantai'],
                'status' => $roomData['status'],
            ]);
        }

        return $rooms;
    }

    private function createTasks(ProjectRoom $room): void
    {
        $taskTemplates = [
            ['nama_task' => 'Menyapu lantai', 'deskripsi' => 'Sapu seluruh area', 'urutan' => 1],
            ['nama_task' => 'Mengepel lantai', 'deskripsi' => 'Pel dengan cairan pembersih', 'urutan' => 2],
            ['nama_task' => 'Membersihkan kaca', 'deskripsi' => 'Lap kaca hingga bersih', 'urutan' => 3],
            ['nama_task' => 'Membuang sampah', 'deskripsi' => 'Buang sampah ke TPS', 'urutan' => 4],
            ['nama_task' => 'Menyemprot disinfektan', 'deskripsi' => 'Semprot area dengan disinfektan', 'urutan' => 5],
        ];

        foreach ($taskTemplates as $taskData) {
            TaskList::create([
                'project_room_id' => $room->id,
                'nama_task' => $taskData['nama_task'],
                'deskripsi' => $taskData['deskripsi'],
                'urutan' => $taskData['urutan'],
                'status' => 'aktif',
            ]);
        }
    }

    private function assignEmployees(Project $project): array
    {
        $stafType = $project->jenis_project === 'cleaning_services' ? 'cleaning_services' : 'security_services';
        $employees = User::where('staf', $stafType)->take(15)->get();

        foreach ($employees as $employee) {
            $project->employees()->attach($employee->id, [
                'tanggal_mulai' => $project->tanggal_mulai,
                'tanggal_selesai' => null,
            ]);
        }

        return $employees->toArray();
    }

    private function createAttendance(Project $project, array $employees): void
    {
        $startDate = Carbon::parse($project->tanggal_mulai);
        
        // Generate 30 days attendance
        for ($day = 0; $day < 30; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            foreach ($employees as $employee) {
                // Random status distribution
                $rand = rand(1, 100);
                if ($rand <= 70) {
                    $status = 'hadir';
                    $checkIn = Carbon::parse($project->jam_masuk)->subMinutes(rand(0, 15));
                } elseif ($rand <= 90) {
                    $status = 'terlambat';
                    $checkIn = Carbon::parse($project->jam_masuk)->addMinutes(rand(5, 60));
                } elseif ($rand <= 95) {
                    $status = 'izin';
                    $checkIn = null;
                } else {
                    $status = 'alpha';
                    $checkIn = null;
                }

                $checkOut = $checkIn ? Carbon::parse($project->jam_keluar)->addMinutes(rand(-10, 30)) : null;

                Attendance::create([
                    'user_id' => $employee['id'],
                    'project_id' => $project->id,
                    'tanggal' => $date->format('Y-m-d'),
                    'check_in' => $checkIn ? $checkIn->format('H:i:s') : null,
                    'check_in_photo' => $checkIn ? 'attendances/dummy-checkin.jpg' : null,
                    'check_in_latitude' => $checkIn ? -6.2088 + (rand(-1000, 1000) / 10000) : null,
                    'check_in_longitude' => $checkIn ? 106.8456 + (rand(-1000, 1000) / 10000) : null,
                    'check_out' => $checkOut ? $checkOut->format('H:i:s') : null,
                    'check_out_photo' => $checkOut ? 'attendances/dummy-checkout.jpg' : null,
                    'check_out_latitude' => $checkOut ? -6.2088 + (rand(-1000, 1000) / 10000) : null,
                    'check_out_longitude' => $checkOut ? 106.8456 + (rand(-1000, 1000) / 10000) : null,
                    'status' => $status,
                    'keterangan' => $status === 'izin' ? 'Izin sakit' : ($status === 'alpha' ? 'Tanpa keterangan' : null),
                ]);
            }
        }
    }

    private function createPatrolAreas(Project $project): array
    {
        $areaTemplates = [
            ['kode_area' => 'PA-001', 'nama_area' => 'Pos Jaga Utama', 'deskripsi' => 'Pos jaga pintu masuk utama', 'urutan' => 1],
            ['kode_area' => 'PA-002', 'nama_area' => 'Lobby Utama', 'deskripsi' => 'Area lobby utama dan resepsionis', 'urutan' => 2],
            ['kode_area' => 'PA-003', 'nama_area' => 'Parkir Basement', 'deskripsi' => 'Area parkir basement lantai B1', 'urutan' => 3],
            ['kode_area' => 'PA-004', 'nama_area' => 'Koridor Lantai 1', 'deskripsi' => 'Koridor utama lantai 1', 'urutan' => 4],
            ['kode_area' => 'PA-005', 'nama_area' => 'Tangga Darurat A', 'deskripsi' => 'Tangga darurat sisi timur', 'urutan' => 5],
            ['kode_area' => 'PA-006', 'nama_area' => 'Rooftop', 'deskripsi' => 'Area rooftop dan mesin lift', 'urutan' => 6],
            ['kode_area' => 'PA-007', 'nama_area' => 'Pos Jaga Belakang', 'deskripsi' => 'Pos jaga pintu belakang', 'urutan' => 7],
        ];

        $areas = [];
        $projectCode = 'P' . $project->id . '-';
        
        foreach ($areaTemplates as $areaData) {
            $areas[] = PatrolArea::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'kode_area' => $projectCode . $areaData['kode_area'],
                ],
                [
                    'nama_area' => $areaData['nama_area'],
                    'deskripsi' => $areaData['deskripsi'],
                    'status' => 'aktif',
                    'urutan' => $areaData['urutan'],
                ]
            );
        }

        return $areas;
    }

    private function createPatrols(Project $project, array $employees, array $patrolAreas): void
    {
        $startDate = Carbon::parse($project->tanggal_mulai);
        
        // Generate 30 days patrol data
        for ($day = 0; $day < 30; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Each day, security patrols multiple times
            $patrolTimes = ['08:00', '12:00', '16:00', '20:00', '00:00'];
            
            foreach ($patrolTimes as $time) {
                // Pick a random employee for this patrol round
                $randomEmployee = $employees[array_rand($employees)];
                
                foreach ($patrolAreas as $area) {
                    // 85% aman, 15% tidak aman
                    $status = rand(1, 100) <= 85 ? 'Aman' : 'Tidak Aman';
                    $patrolTime = Carbon::parse($time)->addMinutes(rand(0, 10));
                    
                    $notes = [
                        'Aman' => [
                            'Kondisi aman, tidak ada masalah.',
                            'Area aman dan bersih.',
                            'Patroli normal, aman terkendali.',
                            'Tidak ada temuan mencurigakan.',
                            null,
                        ],
                        'Tidak Aman' => [
                            'Ditemukan pintu tidak terkunci.',
                            'Lampu koridor mati, segera perbaiki.',
                            'Ada orang mencurigakan, sudah diusir.',
                            'Kebocoran air ditemukan, lapor maintenance.',
                            'CCTV tidak berfungsi di area ini.',
                        ],
                    ];
                    
                    $note = $notes[$status][array_rand($notes[$status])];
                    
                    Patrol::create([
                        'user_id' => $randomEmployee['id'],
                        'project_id' => $project->id,
                        'patrol_area_id' => $area->id,
                        'area_name' => $area->nama_area,
                        'area_code' => $area->kode_area,
                        'status' => $status,
                        'note' => $note,
                        'photo' => 'patrols/dummy-patrol.jpg',
                        'patrol_date' => $date->format('Y-m-d'),
                        'patrol_time' => $patrolTime->format('H:i:s'),
                        'submitted_at' => $date->copy()->setTimeFromTimeString($patrolTime->format('H:i:s'))->addMinutes(rand(1, 5)),
                    ]);
                }
            }
        }
    }
}
