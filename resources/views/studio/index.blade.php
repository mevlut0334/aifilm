
@extends('web.layouts.app')

@section('title', 'Studio Taleplerim')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Studio Taleplerim</h2>
            <p class="text-muted mb-0">Oluşturduğun video taleplerini buradan takip edebilirsin.</p>
        </div>

        <a href="{{ route('studio.create') }}" class="btn btn-primary">
            Yeni Video Talebi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($requests->count() > 0)
        <div class="row g-3">
            @foreach($requests as $studioRequest)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Studio Talebi</strong>

                                @if($studioRequest->status === 'completed')
                                    <span class="badge bg-success">Tamamlandı</span>
                                @else
                                    <span class="badge bg-info">İşleniyor</span>
                                @endif
                            </div>

                            <p class="mb-2">
                                <strong>Format:</strong>
                                {{ $studioRequest->format }}
                            </p>

                            <p class="mb-2">
                                <strong>Karakter:</strong>
                                {{ count($studioRequest->characters ?? []) }}
                            </p>

                            <p class="mb-2">
                                <strong>Ortam:</strong>
                                {{ count($studioRequest->environments ?? []) }}
                            </p>

                            <p class="text-muted small">
                                {{ $studioRequest->created_at->format('d.m.Y H:i') }}
                            </p>

                            <a
                                href="{{ route('studio.show', $studioRequest->uuid) }}"
                                class="btn btn-outline-primary w-100"
                            >
                                Talep Detayını Gör
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <h5>Henüz video talebin bulunmuyor.</h5>
                <p class="text-muted">İlk Studio video talebini oluşturabilirsin.</p>
                <a href="{{ route('studio.create') }}" class="btn btn-primary">
                    Video Talebi Oluştur
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
