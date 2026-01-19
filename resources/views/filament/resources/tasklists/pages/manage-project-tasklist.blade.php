<x-filament-panels::page>
    @if($project)
        {{-- Stats Widget --}}
        @livewire(\App\Filament\Resources\TaskLists\Widgets\TaskListStatsWidget::class, [
            'projectId' => $projectId,
            'filterDate' => $filterDate,
        ])

        {{-- Filter Section --}}
        <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" class="dark:bg-gray-800">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">📋 Filter Task List</h3>
            
            <form method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" name="project" value="{{ $projectId }}">
                
                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Tanggal</label>
                    <input 
                        type="date" 
                        name="date" 
                        value="{{ $filterDate }}"
                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                        class="dark:bg-gray-700 dark:border-gray-600"
                    >
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Area/Ruangan</label>
                    <select 
                        name="room"
                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                        class="dark:bg-gray-700 dark:border-gray-600"
                    >
                        <option value="">Semua Ruangan</option>
                        @foreach($this->getRooms() as $id => $nama)
                            <option value="{{ $id }}" {{ $filterRoom == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button 
                        type="submit"
                        style="padding: 0.5rem 1.25rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;"
                    >
                        🔍 Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Task Submissions Table Section --}}
        <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;" class="dark:bg-gray-800">
            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--fi-sidebar-border-color, #e5e7eb);" class="dark:border-gray-700">
                <h3 style="font-size: 1.1rem; font-weight: 600;">📝 Laporan Task List - {{ \Carbon\Carbon::parse($filterDate)->translatedFormat('d F Y') }}</h3>
            </div>
            
            <div style="padding: 0;">
                {{ $this->table }}
            </div>
        </div>
    @else
        <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; padding: 3rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" class="dark:bg-gray-800">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Project tidak ditemukan</h3>
            <p style="color: #6b7280; font-size: 0.875rem;">Pilih project untuk melihat laporan task list.</p>
        </div>
    @endif
</x-filament-panels::page>
