<x-filament-panels::page>
    {{ $this->infolist }}
    
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Daftar Task ({{ $this->record->completed_count }}/{{ $this->record->total_tasks }} selesai)
        </x-slot>
        
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: rgb(249 250 251); border-bottom: 1px solid rgb(229 231 235);">
                    <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: rgb(107 114 128); text-transform: uppercase;">No</th>
                    <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: rgb(107 114 128); text-transform: uppercase;">Nama Task</th>
                    <th style="padding: 12px 16px; text-align: center; font-size: 12px; font-weight: 500; color: rgb(107 114 128); text-transform: uppercase;">Status</th>
                    <th style="padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 500; color: rgb(107 114 128); text-transform: uppercase;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->getTaskItems() as $index => $item)
                    <tr style="border-bottom: 1px solid rgb(229 231 235);">
                        <td style="padding: 12px 16px; font-size: 14px; color: rgb(17 24 39);">{{ $index + 1 }}</td>
                        <td style="padding: 12px 16px; font-size: 14px; color: rgb(17 24 39);">{{ $item['nama_task'] }}</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            @if($item['is_completed'])
                                <x-filament::badge color="success">Selesai</x-filament::badge>
                            @else
                                <x-filament::badge color="danger">Belum</x-filament::badge>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; font-size: 14px; color: rgb(107 114 128);">{{ $item['catatan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding: 32px 16px; text-align: center; font-size: 14px; color: rgb(107 114 128);">
                            Tidak ada task
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
    
    <div style="margin-top: 24px;">
        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.task-lists.report') }}"
            color="gray"
            icon="heroicon-o-arrow-left"
        >
            Kembali ke Laporan
        </x-filament::button>
    </div>
</x-filament-panels::page>
