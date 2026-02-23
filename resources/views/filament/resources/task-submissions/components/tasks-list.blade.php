@php
    $record = $getRecord();
    $items = $record->items;
@endphp

<div class="w-full">
    @if($items && $items->count() > 0)
        <div class="space-y-2">
            @foreach($items as $item)
                <div class="flex items-center p-3 rounded-lg {{ $item->is_completed ? 'bg-success-50' : 'bg-gray-50' }}">
                    <div class="flex-shrink-0 mr-3">
                        @if($item->is_completed)
                            <x-heroicon-o-check-circle class="w-6 h-6 text-success-500" />
                        @else
                            <x-heroicon-o-x-circle class="w-6 h-6 text-gray-400" />
                        @endif
                    </div>
                    <div class="flex-1">
                        <span class="{{ $item->is_completed ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                            {{ $item->task->nama_task ?? 'Task #' . $item->task_list_id }}
                        </span>
                    </div>
                    <div class="flex-shrink-0">
                        @if($item->is_completed)
                            <span class="text-xs font-medium text-success-600 bg-success-100 px-2 py-1 rounded-full">Selesai</span>
                        @else
                            <span class="text-xs font-medium text-gray-500 bg-gray-200 px-2 py-1 rounded-full">Belum</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">Tidak ada tugas</p>
    @endif
</div>
