@php
    $record = $getRecord();
    $foto = $record->foto;
    $photoUrl = \App\Services\MediaStorage::url($foto);
@endphp

<div class="w-full">
    @if($foto)
        <a href="{{ $photoUrl }}" target="_blank" class="block">
            <img
                src="{{ $photoUrl }}"
                alt="Foto Checkpoint"
                class="rounded-lg max-h-80 object-contain"
                style="max-height: 300px;"
            />
        </a>
        <p class="text-sm text-gray-500 mt-2">Klik gambar untuk melihat ukuran penuh</p>
    @else
        <p class="text-gray-500">Tidak ada foto</p>
    @endif
</div>
