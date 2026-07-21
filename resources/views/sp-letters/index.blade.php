@extends('layouts.app')

@section('title', 'Surat Peringatan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Surat Peringatan</h1>
    <p class="text-sm text-gray-500">Daftar surat peringatan siswa</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Surat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis SP</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Poin</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($letters as $sp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $sp->letter_number }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sp->student->full_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full" style="color: {{ $sp->spThreshold->color ?? '#ef4444' }}; background-color: {{ $sp->spThreshold->color ?? '#ef4444' }}20;">
                                {{ $sp->spThreshold->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $sp->total_points_at_time }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['draft' => 'yellow', 'printed' => 'blue', 'signed' => 'green', 'delivered' => 'purple'];
                                $color = $statusColors[$sp->status] ?? 'gray';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800 capitalize">
                                {{ $sp->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $sp->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('sp-letters.show', $sp->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Detail</a>
                            <a href="{{ route('sp-letters.print', $sp->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium" target="_blank">Cetak</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            <p>Belum ada surat peringatan yang digenerate</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($letters->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $letters->links() }}
        </div>
    @endif
</div>
@endsection
