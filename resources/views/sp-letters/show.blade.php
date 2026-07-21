@extends('layouts.app')

@section('title', 'Detail SP')

@section('content')
<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Surat Peringatan</h1>
            <p class="text-sm text-gray-500">{{ $spLetter->letter_number }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('sp-letters.index') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition">Kembali</a>
            <a href="{{ route('sp-letters.print', $spLetter->id) }}" target="_blank" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Cetak</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">No. Surat</p>
            <p class="text-sm font-mono font-medium text-gray-900 mt-1">{{ $spLetter->letter_number }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Siswa</p>
            <p class="text-sm font-medium text-gray-900 mt-1">{{ $spLetter->student->full_name ?? '-' }}</p>
            <p class="text-xs text-gray-500">{{ $spLetter->student->nisn ?? '' }} - {{ $spLetter->student->class_name ?? '' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Jenis SP</p>
            <p class="text-sm font-medium text-gray-900 mt-1">{{ $spLetter->spThreshold->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Poin Saat Generate</p>
            <p class="text-lg font-bold text-blue-600 mt-1">{{ $spLetter->total_points_at_time }} poin</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
            <p class="text-sm font-medium text-gray-900 mt-1 capitalize">{{ $spLetter->status }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Dibuat Oleh</p>
            <p class="text-sm text-gray-900 mt-1">{{ $spLetter->generator->name ?? 'Sistem' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider">Dibuat Pada</p>
            <p class="text-sm text-gray-900 mt-1">{{ $spLetter->created_at->format('d F Y H:i') }}</p>
        </div>

        @if($spLetter->violations_included)
        <div class="border-t border-gray-100 pt-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Daftar Pelanggaran Terkait</p>
            <div class="bg-gray-50 p-3 rounded-lg">
                @php $violations = is_array($spLetter->violations_included) ? $spLetter->violations_included : json_decode($spLetter->violations_included, true); @endphp
                @if($violations)
                    <ul class="space-y-1">
                        @foreach($violations as $v)
                            <li class="text-sm text-gray-700">• {{ $v['violation_date'] ?? '-' }} — {{ $v['description'] ?? '(deskripsi)' }} <span class="text-red-500 font-medium">(+{{ $v['points'] ?? 0 }})</span></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">Data tidak tersedia</p>
                @endif
            </div>
        </div>
        @endif

        @if($school['kepala_sekolah'])
        <div class="border-t border-gray-100 pt-6">
            <div class="text-right">
                <p class="text-sm text-gray-900">{{ $school['kepala_sekolah'] }}</p>
                <p class="text-xs text-gray-500">Kepala {{ $school['name'] }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
