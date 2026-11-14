
@extends('admin.layouts.app')

@section('title', 'ASILOV Studio Talepleri')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1>ASILOV Studio Talepleri</h1>
            <p class="text-muted mb-0">Müşterilerin oluşturduğu video taleplerini buradan yönetebilirsin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Video Talepleri</h5>
            <span class="badge bg-secondary">
                Toplam: {{ $requests->total() }}
            </span>
        </div>

        <div class="card-body">
            @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th>Format</th>
                                <th>Karakter</th>
                                <th>Ortam</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                                <th class="text-end">İşlem</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($requests as $studioRequest)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ trim(($studioRequest->user->first_name ?? '') . ' ' . ($studioRequest->user->last_name ?? '')) ?: 'İsimsiz kullanıcı' }}
                                        </strong>
                                        <div class="small text-muted">
                                            {{ $studioRequest->user->email ?? '-' }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $studioRequest->format }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ count($studioRequest->characters ?? []) }}
                                    </td>

                                    <td>
                                        {{ count($studioRequest->environments ?? []) }}
                                    </td>

                                    <td>
                                        @if($studioRequest->status === 'completed')
                                            <span class="badge bg-success">Tamamlandı</span>
                                        @else
                                            <span class="badge bg-info">İşleniyor</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $studioRequest->created_at->format('d.m.Y H:i') }}
                                    </td>

                                    <td class="text-end">
                                        <a
                                            href="{{ route('admin.studio.show', $studioRequest->uuid) }}"
                                            class="btn btn-sm btn-primary"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Detay
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-camera-reels display-4 text-muted"></i>
                    <h5 class="mt-3">Henüz Studio talebi yok</h5>
                    <p class="text-muted mb-0">
                        Müşteriler video talebi oluşturduğunda burada görünecek.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
