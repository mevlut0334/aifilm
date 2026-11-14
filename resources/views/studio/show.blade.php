
@extends('web.layouts.app')

@section('title', 'Studio Talep Detayı')

@section('content')
<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2>Studio Talep Detayı</h2>
            <p class="text-muted mb-0">Video talebinin güncel durumunu buradan takip edebilirsin.</p>
        </div>

        <a href="{{ route('studio.index') }}" class="btn btn-outline-secondary">
            Taleplerime Dön
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Talep Durumu</h5>

                @if($studioRequest->status === 'completed')
                    <span class="badge bg-success">Tamamlandı</span>
                @else
                    <span class="badge bg-info">İşleniyor</span>
                @endif
            </div>

            <div class="row mb-2">
                <div class="col-md-4"><strong>Video Formatı:</strong></div>
                <div class="col-md-8">{{ $studioRequest->format }}</div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4"><strong>Talep Tarihi:</strong></div>
                <div class="col-md-8">{{ $studioRequest->created_at->format('d.m.Y H:i') }}</div>
            </div>

            <div class="row">
                <div class="col-md-4"><strong>Senaryo:</strong></div>
                <div class="col-md-8">
                    <p style="white-space: pre-wrap;">{{ $studioRequest->scenario ?: 'Senaryo belirtilmemiş.' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($studioRequest->video_url)
        <div class="card mb-4 border-success">
            <div class="card-header">
                <h5 class="mb-0">Teslim Edilen Videon</h5>
            </div>
            <div class="card-body">
                <a href="{{ $studioRequest->video_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                    Videoyu İzle / Aç
                </a>

                @if($studioRequest->video_updated_at)
                    <p class="text-muted small mt-3 mb-0">
                        Son güncelleme:
                        {{ $studioRequest->video_updated_at->format('d.m.Y H:i') }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    @if($studioRequest->status === 'completed' && $studioRequest->video_url)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Revize Talebi Oluştur</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('studio.revisions.store', $studioRequest->uuid) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Revize Açıklaması</label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="5"
                            maxlength="10000"
                            placeholder="Videoda değiştirilmesini istediğin noktaları açıkla..."
                            required
                        >{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Referans Video URL (İsteğe bağlı)</label>
                        <input
                            type="url"
                            name="reference_video_url"
                            class="form-control"
                            value="{{ old('reference_video_url') }}"
                            placeholder="https://..."
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Revize Talebi Gönder
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Revize Geçmişi</h5>
        </div>

        <div class="card-body">
            @forelse($studioRequest->revisions as $revision)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Revize #{{ $loop->iteration }}</strong>

                        @if($revision->status === 'completed')
                            <span class="badge bg-success">Tamamlandı</span>
                        @else
                            <span class="badge bg-info">İşleniyor</span>
                        @endif
                    </div>

                    <p style="white-space: pre-wrap;">{{ $revision->description }}</p>

                    <small class="text-muted">
                        {{ $revision->created_at->format('d.m.Y H:i') }}
                    </small>
                </div>
            @empty
                <p class="text-muted mb-0">Henüz revize talebin bulunmuyor.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
