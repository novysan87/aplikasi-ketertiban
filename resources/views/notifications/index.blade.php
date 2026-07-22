@extends('layouts.app')

@section('title', 'Semua Notifikasi')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Semua Notifikasi</h1>
        <p class="text-sm text-gray-500">Riwayat notifikasi pelanggaran siswa</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($notifications as $notif)
                    <a href="{{ $notif->action_url ?: '#' }}" onclick="event.preventDefault(); fetch('/notifications/{{ $notif->id }}/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }).then(() => { window.location = this.href; });" class="block px-6 py-4 hover:bg-gray-50 transition-colors {{ !$notif->is_read ? 'bg-blue-50/50' : '' }}">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $notif->color ? 'bg-'.$notif->color.'-100' : 'bg-blue-100' }}">
                                <i class="fa-solid fa-bell text-gray-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $notif->title }}</p>
                                <p class="text-sm text-gray-500">{{ $notif->body }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            @if(!$notif->is_read)
                                <span class="flex-shrink-0 w-2 h-2 mt-2 bg-blue-500 rounded-full"></span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-100">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fa-regular fa-bell text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-500">Belum ada notifikasi</p>
            </div>
        @endif
    </div>
</div>
@endsection
