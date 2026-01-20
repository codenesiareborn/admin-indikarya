<x-filament-panels::page>
    {{-- Stats Widgets --}}
    @php
        $stats = $this->getStats();
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Submit</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['total_submissions'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Task Selesai</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['total_completed'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Task Pending</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['total_pending'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Completion Rate</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['completion_rate'] }}%</div>
        </div>
        <div style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Pegawai Aktif</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['active_employees'] }}</div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" class="dark:bg-gray-800">
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">🔍 Filter Laporan</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Tanggal Mulai</label>
                <input 
                    type="date" 
                    wire:model.live="startDate"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Tanggal Selesai</label>
                <input 
                    type="date" 
                    wire:model.live="endDate"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Project</label>
                <select 
                    wire:model.live="projectId"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">-- Semua Project --</option>
                    @foreach($this->getProjects() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Jenis Project</label>
                <select 
                    wire:model.live="projectType"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">-- Semua Jenis Project --</option>
                    @foreach($this->getProjectTypes() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Area/Ruangan</label>
                <select 
                    wire:model.live="roomId"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">-- Semua Area --</option>
                    @foreach($this->getRooms() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Export Buttons - Using anchor links to controller route --}}
        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a 
                href="{{ route('reports.tasklist.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'project_id' => $projectId, 'room_id' => $roomId, 'project_type' => $projectType]) }}"
                style="padding: 0.5rem 1.25rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;"
            >
                📄 Export PDF
            </a>
            <a 
                href="{{ route('reports.tasklist.excel', ['start_date' => $startDate, 'end_date' => $endDate, 'project_id' => $projectId, 'room_id' => $roomId, 'project_type' => $projectType]) }}"
                style="padding: 0.5rem 1.25rem; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;"
            >
                📊 Export Excel
            </a>
        </div>
    </div>

    {{-- Table Section --}}
    <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;" class="dark:bg-gray-800">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--fi-sidebar-border-color, #e5e7eb);" class="dark:border-gray-700">
            <h3 style="font-size: 1.1rem; font-weight: 600;">📋 Data Task List</h3>
        </div>
        
        <div style="padding: 0;">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
