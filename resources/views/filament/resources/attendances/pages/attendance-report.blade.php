<x-filament-panels::page>
    <style>
        .search-input-wrapper-custom {
            padding: 0.75rem 1rem 0.75rem 1.5rem !important;
        }
    </style>
    {{-- Stats Widgets --}}
    @php
        $stats = $this->getStats();
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Data</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['total'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Hadir</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['hadir'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Terlambat</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['terlambat'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Izin</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['izin'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Sakit</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['sakit'] }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; padding: 1.25rem; border-radius: 0.75rem;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Alpha</div>
            <div style="font-size: 2rem; font-weight: bold;">{{ $stats['alpha'] }}</div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" class="dark:bg-gray-800">
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;"><i class="fa fa-search"></i> Filter Laporan</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Tanggal Mulai</label>
                <input 
                    type="date" 
                    wire:model="startDate"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Tanggal Selesai</label>
                <input 
                    type="date" 
                    wire:model="endDate"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Project</label>
                <select 
                    wire:model="projectId"
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
                    wire:model="projectType"
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
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Status</label>
                <select 
                    wire:model="status"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff);"
                    class="dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">-- Semua Status --</option>
                    <option value="hadir">Hadir</option>
                    <option value="terlambat">Terlambat</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha</option>
                </select>
            </div>

            <div 
                x-data="{ 
                    open: false, 
                    search: '', 
                    selected: @entangle('employeeId'),
                    employees: @js($employees),
                    get filteredEmployees() {
                        if (!this.search) return this.employees;
                        return Object.fromEntries(
                            Object.entries(this.employees).filter(([id, name]) => 
                                name.toLowerCase().includes(this.search.toLowerCase())
                            )
                        );
                    },
                    get selectedName() {
                        return this.selected ? this.employees[this.selected] : '-- Semua Pegawai --';
                    }
                }"
                x-init="$watch('selected', value => $wire.set('employeeId', value))"
            >
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--fi-body-text-color, #374151);" class="dark:text-gray-200">
                    Pegawai
                </label>
                
                <div style="position: relative;">
                    <!-- Selected Display Button -->
                    <button 
                        @click="open = !open"
                        type="button"
                        style="width: 100%; height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; background: var(--fi-input-bg, #fff); text-align: left; display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; transition: all 0.15s ease;"
                        class="dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                        :class="open && 'ring-2 ring-primary-600 border-primary-600'"
                    >
                        <span x-text="selectedName" style="color: var(--fi-body-text-color, #374151); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" class="dark:text-gray-200"></span>
                        <svg 
                            width="12" 
                            height="12"
                            style="transition: transform 0.2s; flex-shrink: 0; margin-left: 0.5rem; color: var(--fi-body-text-color, #6b7280);" 
                            :style="open && 'transform: rotate(180deg)'" 
                            xmlns="http://www.w3.org/2000/svg" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                            stroke-width="2.5"
                            class="dark:text-gray-400"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Dropdown Panel -->
                    <div 
                        x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        style="position: absolute; top: calc(100% + 0.25rem); left: 0; min-width: 100%; width: max-content; max-width: 400px; background: var(--fi-body-bg, #fff); border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); z-index: 50; max-height: 20rem; overflow: hidden;"
                        class="dark:bg-gray-800 dark:border-gray-600"
                        x-cloak
                    >
                        <!-- Search Input -->
                        <div class="search-input-wrapper-custom dark:border-gray-600">
                            <input 
                                x-model="search"
                                type="text"
                                placeholder="Start typing to search..."
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--fi-input-border-color, #d1d5db); border-radius: 0.375rem; background: var(--fi-input-bg, #fff); font-size: 0.875rem; outline: none;"
                                class="dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-600 focus:border-primary-600"
                                @click.stop
                                x-ref="searchInput"
                            >
                        </div>
                        
                        <!-- Options List -->
                        <div style="max-height: 15rem; overflow-y: auto; border-top: 1px solid var(--fi-input-border-color, #e5e7eb);" class="dark:border-gray-600">
                            <!-- Semua Pegawai Option -->
                            <div 
                                @click="selected = ''; open = false; search = ''"
                                @mouseenter="$el.style.backgroundColor = '#3b82f6'; $el.style.color = '#ffffff';"
                                @mouseleave="if (selected !== '') { $el.style.backgroundColor = 'transparent'; $el.style.color = ''; } else { $el.style.backgroundColor = '#dbeafe'; $el.style.color = '#1e40af'; }"
                                style="padding: 0.75rem 1rem; cursor: pointer; font-size: 0.875rem; transition: all 0.15s ease;"
                                :style="selected === '' && 'background-color: #dbeafe; font-weight: 600; color: #1e40af;'"
                                class="dark:text-gray-200"
                            >
                                -- Semua Pegawai --
                            </div>
                            
                            <!-- Employee Options -->
                            <template x-for="[id, name] in Object.entries(filteredEmployees)" :key="id">
                                <div 
                                    @click="selected = id; open = false; search = ''"
                                    @mouseenter="$el.style.backgroundColor = '#3b82f6'; $el.style.color = '#ffffff';"
                                    @mouseleave="if (selected != id) { $el.style.backgroundColor = 'transparent'; $el.style.color = ''; } else { $el.style.backgroundColor = '#dbeafe'; $el.style.color = '#1e40af'; }"
                                    style="padding: 0.75rem 1rem; cursor: pointer; font-size: 0.875rem; transition: all 0.15s ease;"
                                    :style="selected == id && 'background-color: #dbeafe; font-weight: 600; color: #1e40af;'"
                                    class="dark:text-gray-200"
                                    x-text="name"
                                ></div>
                            </template>
                            
                            <!-- No Results -->
                            <div 
                                x-show="Object.keys(filteredEmployees).length === 0"
                                style="padding: 1rem 0.75rem; color: #9ca3af; text-align: center; font-size: 0.875rem;"
                                class="dark:text-gray-400"
                            >
                                Tidak ada hasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: flex-end;">
                <button 
                    wire:click="applyFilter"
                    wire:loading.attr="disabled"
                    style="width: 100%; padding: 0.5rem 1.25rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                    class="hover:opacity-90"
                >
                    <svg wire:loading.remove wire:target="applyFilter" style="width: 18px; height: 18px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <svg wire:loading wire:target="applyFilter" style="width: 18px; height: 18px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="applyFilter">Terapkan Filter</span>
                    <span wire:loading wire:target="applyFilter">Memuat...</span>
                </button>
            </div>
        </div>
        
        <style>
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>

        {{-- Export Buttons - Using Livewire actions --}}
        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <button 
                wire:click="exportPdf"
                wire:loading.attr="disabled"
                style="padding: 0.5rem 1.25rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;"
            >
                <i class="fa fa-file-pdf" wire:loading.remove wire:target="exportPdf"></i>
                <svg wire:loading wire:target="exportPdf" style="width: 16px; height: 16px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                <span wire:loading wire:target="exportPdf">Memuat...</span>
            </button>
            <button 
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                style="padding: 0.5rem 1.25rem; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;"
            >
                <i class="fa fa-file-excel" wire:loading.remove wire:target="exportExcel"></i>
                <svg wire:loading wire:target="exportExcel" style="width: 16px; height: 16px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                <span wire:loading wire:target="exportExcel">Memuat...</span>
            </button>
        </div>
    </div>

    {{-- Table Section --}}
    <div style="background: var(--fi-body-bg, #fff); border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;" class="dark:bg-gray-800">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--fi-sidebar-border-color, #e5e7eb);" class="dark:border-gray-700">
            <h3 style="font-size: 1.1rem; font-weight: 600;">Data Presensi</h3>
        </div>
        
        <div style="padding: 0;">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
