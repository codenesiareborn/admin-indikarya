@php
    $record = $getRecord();
    $photo = $record->photo;
@endphp

<div class="w-full">
    @if($photo)
        <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="block">
            <img 
                src="{{ asset('storage/' . $photo) }}" 
                alt="Foto Patroli" 
                class="rounded-lg max-h-80 object-contain"
                style="max-height: 300px;"
            />
        </a>
        <p class="text-sm text-gray-500 mt-2">Klik gambar untuk melihat ukuran penuh</p>
    @else
        <p class="text-gray-500">Tidak ada foto</p>
    @endif
</div>
