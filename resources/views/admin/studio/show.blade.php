
@extends('admin.layouts.app')

@section('title', 'ASILOV Studio - Talep Detayı')

@section('content')
@php
    $characters = $studioRequest->characters ?? [];
    $environments = $studioRequest->environments ?? [];
    $voices = collect(config('studio_voices', []));
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1>ASILOV Studio Talep Detayı</h1>
            <small class="text-muted">{{ $studioRequest->uuid }}</small>
        </div>
        <a href="{{ route('admin.studio.index') }}" class="btn btn-secondary">
            Geri Dön
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">

            {{-- TALEP BİLGİLERİ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Talep Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Müşteri:</strong></div>
                        <div class="col-sm-8">
                            {{ trim(($studioRequest->user->first_name ?? '') . ' ' . ($studioRequest->user->last_name ?? '')) ?: ($studioRequest->user->email ?? '-') }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>E-posta:</strong></div>
                        <div class="col-sm-8">
                            {{ $studioRequest->user->email ?? '-' }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Durum:</strong></div>
                        <div class="col-sm-8">
                            @if($studioRequest->status === 'completed')
                                <span class="badge bg-success">Tamamlandı</span>
                            @else
                                <span class="badge bg-info">İşleniyor</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Video Formatı:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge bg-primary">
                                {{ $studioRequest->format }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Oluşturulma:</strong></div>
                        <div class="col-sm-8">
                            {{ $studioRequest->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4"><strong>Senaryo:</strong></div>
                        <div class="col-sm-8">
                            <div class="bg-light p-3 rounded" style="white-space: pre-wrap;">{{ $studioRequest->scenario ?: 'Senaryo girilmemiş.' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- İLK REVİZE TALEBİ --}}
            @if($studioRequest->initial_revision_video_url || $studioRequest->initial_revision_description)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Başlangıç Revize Talebi</h5>
                    </div>
                    <div class="card-body">
                        @if($studioRequest->initial_revision_video_url)
                            <div class="mb-3">
                                <strong>Referans Video:</strong><br>
                                <a href="{{ $studioRequest->initial_revision_video_url }}" target="_blank" rel="noopener noreferrer">
                                    {{ $studioRequest->initial_revision_video_url }}
                                </a>
                            </div>
                        @endif

                        @if($studioRequest->initial_revision_description)
                            <strong>Açıklama:</strong>
                            <p class="bg-light p-3 rounded mt-2" style="white-space: pre-wrap;">{{ $studioRequest->initial_revision_description }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- KARAKTERLER --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Karakterler ({{ count($characters) }})</h5>
                </div>
                <div class="card-body">
                    @forelse($characters as $index => $character)
                        <div class="border rounded p-3 mb-3">
                            <h6>Karakter {{ $index + 1 }}</h6>

                            <div class="mb-3">
                                <strong>Ad:</strong>
                                {{ $character['name'] ?? 'Belirtilmemiş' }}
                            </div>

                            @if(!empty($character['image_path']))
                                <div class="mb-3">
                                    <strong>Karakter Görseli:</strong><br>
                                    <img
                                        src="{{ asset('storage/' . $character['image_path']) }}"
                                        alt="Karakter {{ $index + 1 }}"
                                        class="img-fluid rounded border mt-2"
                                        style="max-width: 300px; max-height: 300px; object-fit: contain;"
                                    >
                                </div>
                            @endif

                            @if(!empty($character['voice_key']))
                                @php
                                    $selectedVoice = $voices->firstWhere('key', $character['voice_key']);
                                @endphp
                                <div class="mb-3">
                                    <strong>Seçilen Hazır Ses:</strong>
                                    {{ $selectedVoice['name'] ?? $character['voice_key'] }}
                                    @if(!empty($selectedVoice['description']))
                                        <div class="text-muted small">
                                            {{ $selectedVoice['description'] }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($character['custom_voice_path']))
                                <div class="mt-3">
                                    <strong>Yüklenen Özel Ses:</strong>
                                    <div class="mt-2">
                                        <a
                                            href="{{ route('admin.studio.download-voice', [$studioRequest->uuid, $index]) }}"
                                            class="btn btn-success btn-sm"
                                        >
                                            <i class="bi bi-download"></i>
                                            Ses Dosyasını İndir
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if(empty($character['voice_key']) && empty($character['custom_voice_path']))
                                <div class="text-muted">
                                    Ses seçilmemiş.
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Karakter eklenmemiş.</p>
                    @endforelse
                </div>
            </div>

            {{-- ORTAMLAR --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ortamlar ({{ count($environments) }})</h5>
                </div>
                <div class="card-body">
                    @forelse($environments as $index => $environment)
                        <div class="border rounded p-3 mb-3">
                            <h6>Ortam {{ $index + 1 }}</h6>

                            <div class="mb-2">
                                <strong>Ad:</strong>
                                {{ $environment['name'] ?? 'Belirtilmemiş' }}
                            </div>

                            @if(!empty($environment['image_path']))
                                <img
                                    src="{{ asset('storage/' . $environment['image_path']) }}"
                                    alt="Ortam {{ $index + 1 }}"
                                    class="img-fluid rounded border mt-2"
                                    style="max-width: 400px; max-height: 300px; object-fit: contain;"
                                >
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Ortam eklenmemiş.</p>
                    @endforelse
                </div>
            </div>

            {{-- REVİZE GEÇMİŞİ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Revize Talepleri</h5>
                </div>
                <div class="card-body">
                    @forelse($studioRequest->revisions as $revision)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Revize #{{ $revision->id }}</strong>
                                @if($revision->status === 'completed')
                                    <span class="badge bg-success">Tamamlandı</span>
                                @else
                                    <span class="badge bg-info">İşleniyor</span>
                                @endif
                            </div>

                            <p style="white-space: pre-wrap;">{{ $revision->description }}</p>

                            @if($revision->reference_video_url)
                                <div class="mb-2">
                                    <strong>Referans Video:</strong>
                                    <a href="{{ $revision->reference_video_url }}" target="_blank" rel="noopener noreferrer">
                                        Videoyu Aç
                                    </a>
                                </div>
                            @endif

                            <small class="text-muted">
                                {{ $revision->created_at->format('d.m.Y H:i') }}
                            </small>

                            @if($revision->status !== 'completed')
                                <form
                                    action="{{ route('admin.studio.revisions.complete', $revision->id) }}"
                                    method="POST"
                                    class="mt-3"
                                >
                                    @csrf
                                    <label class="form-label">
                                        Yeni Video URL
                                    </label>
                                    <input
                                        type="url"
                                        name="video_url"
                                        class="form-control mb-2"
                                        placeholder="https://..."
                                        required
                                    >
                                    <button type="submit" class="btn btn-success">
                                        Revizeyi Tamamla ve Videoyu Güncelle
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Henüz revize talebi yok.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- SAĞ SÜTUN --}}
        <div class="col-lg-4">

            {{-- VİDEO TESLİM --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Video Teslimi</h5>
                </div>
                <div class="card-body">
                    @if($studioRequest->video_url)
                        <div class="alert alert-success">
                            Mevcut video bağlantısı kayıtlı.
                        </div>
                        <div class="mb-3">
                            <strong>Video URL:</strong>
                            <a href="{{ $studioRequest->video_url }}" target="_blank" rel="noopener noreferrer" class="d-block text-break">
                                {{ $studioRequest->video_url }}
                            </a>
                        </div>
                        @if($studioRequest->video_updated_at)
                            <small class="text-muted">
                                Son güncelleme:
                                {{ $studioRequest->video_updated_at->format('d.m.Y H:i') }}
                            </small>
                        @endif
                    @else
                        <p class="text-muted">
                            Henüz video teslim edilmedi.
                        </p>
                    @endif

                    <hr>

                    <form
                        action="{{ route('admin.studio.update-video', $studioRequest->uuid) }}"
                        method="POST"
                    >
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Video URL</label>
                            <input
                                type="url"
                                name="video_url"
                                class="form-control"
                                placeholder="https://..."
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            {{ $studioRequest->video_url ? 'Video Bağlantısını Güncelle' : 'Videoyu Teslim Et' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Talep Özeti</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Karakter sayısı</span>
                        <strong>{{ count($characters) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ortam sayısı</span>
                        <strong>{{ count($environments) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Revize sayısı</span>
                        <strong>{{ $studioRequest->revisions->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
