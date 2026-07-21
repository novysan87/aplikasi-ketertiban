@extends('layouts.app')

@section('title', 'Ambang Surat Peringatan')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ambang Surat Peringatan</h1>
        <p class="text-sm text-gray-500">Atur batas poin untuk setiap tingkat Surat Peringatan</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('settings.thresholds.update') }}" method="POST" class="p-6 space-y-6">
            @csrf @method('PUT')

            @foreach($thresholds as $t)
                <input type="hidden" name="thresholds[{{ $loop->index }}][id]" value="{{ $t->id }}">
                <div class="p-4 rounded-lg border-2" style="border-color: {{ $t->color }}20; background-color: {{ $t->color }}08">
                    <div class="flex items-center space-x-2 mb-3">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $t->color }}"></span>
                        <h3 class="text-lg font-semibold" style="color: {{ $t->color }}">{{ $t->name }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama</label>
                            <input type="text" name="thresholds[{{ $loop->index }}][name]" value="{{ $t->name }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Minimal Poin</label>
                            <input type="number" name="thresholds[{{ $loop->index }}][min_points]" value="{{ $t->min_points }}" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Maksimal Poin (kosongkan = tak terbatas)</label>
                            <input type="number" name="thresholds[{{ $loop->index }}][max_points]" value="{{ $t->max_points }}" min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Deskripsi</label>
                            <input type="text" name="thresholds[{{ $loop->index }}][default_description]" value="{{ $t->default_description }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>
@endsection
