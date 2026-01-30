<div class="overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">Nama Task</th>
                <th scope="col" class="px-6 py-3 text-center">Status</th>
                <th scope="col" class="px-6 py-3">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($getRecord()->items as $index => $item)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->task?->nama_task ?? 'Task tidak ditemukan' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($item->is_completed)
                            <x-filament::badge color="success">Selesai</x-filament::badge>
                        @else
                            <x-filament::badge color="danger">Belum</x-filament::badge>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->catatan ?? '-' }}
                    </td>
                </tr>
            @endforeach
            
            @if($getRecord()->items->isEmpty())
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center">Tidak ada task</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
