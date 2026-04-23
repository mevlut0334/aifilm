@extends('admin.layouts.app')

@section('title', 'Custom Görsel Detayı')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Custom Görsel Detayı</h1>
                <a href="{{ route('admin.custom-images.index') }}" class="btn btn-secondary">
                    Geri Dön
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Talep Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>UUID:</strong></div>
                                <div class="col-sm-8"><code>{{ $image->uuid }}</code></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Kullanıcı:</strong></div>
                                <div class="col-sm-8">
                                    <a href="{{ route('admin.users.show', $image->user_id) }}">
                                        {{ $image->user->name }} ({{ $image->user->email }})
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Prompt:</strong></div>
                                <div class="col-sm-8">
                                    <p class="mb-0">{{ $image->prompt }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Görsel Formatı:</strong></div>
                                <div class="col-sm-8">
                                    @if($image->format === 'vertical')
                                        <span class="badge bg-info">Dikey</span>
                                    @elseif($image->format === 'horizontal')
                                        <span class="badge bg-primary">Yatay</span>
                                    @elseif($image->format === 'square')
                                        <span class="badge bg-secondary">Kare</span>
                                    @else
                                        {{ $image->format }}
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Token Maliyeti:</strong></div>
                                <div class="col-sm-8">{{ $image->token_cost }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Oluşturulma:</strong></div>
                                <div class="col-sm-8">{{ $image->created_at->format('d.m.Y H:i:s') }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Güncellenme:</strong></div>
                                <div class="col-sm-8">{{ $image->updated_at->format('d.m.Y H:i:s') }}</div>
                            </div>
                        </div>
                    </div>

                    @if($image->input_image_path)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Referans Görsel</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/' . $image->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 500px;">
                            </div>
                        </div>
                    @endif

                    @if($image->admin_image_url)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Oluşturulan Görsel</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ $image->admin_image_url }}" 
                                     alt="Generated Image" 
                                     class="img-fluid"
                                     style="max-width: 100%;">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <!-- Durum Kartı -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Durum Yönetimi</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Mevcut Durum:</strong>
                                <div class="mt-2">
                                    @if($image->status === 'pending')
                                        <span class="badge bg-warning fs-6">Bekliyor</span>
                                    @elseif($image->status === 'processing')
                                        <span class="badge bg-info fs-6">İşleniyor</span>
                                    @elseif($image->status === 'completed')
                                        <span class="badge bg-success fs-6">Tamamlandı</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Başarısız</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.custom-images.update-status', $image->uuid) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Durum Değiştir</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" {{ $image->status === 'pending' ? 'selected' : '' }}>Bekliyor</option>
                                        <option value="processing" {{ $image->status === 'processing' ? 'selected' : '' }}>İşleniyor</option>
                                        <option value="completed" {{ $image->status === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                        <option value="failed" {{ $image->status === 'failed' ? 'selected' : '' }}>Başarısız</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Görsel URL (Tamamlandı ise) *</label>
                                    <input type="url" name="admin_image_url" class="form-control" 
                                           value="{{ $image->admin_image_url }}"
                                           placeholder="https://example.com/image.png">
                                    <small class="text-muted">Oluşturulan görselin linki</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Hata Nedeni (Başarısız ise)</label>
                                    <textarea name="failure_reason" class="form-control" rows="3">{{ $image->failure_reason }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Durumu Güncelle
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Progress Kartı -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">İlerleme Durumu</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: {{ $image->progress }}%" 
                                         aria-valuenow="{{ $image->progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $image->progress }}%
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.custom-images.update-progress', $image->uuid) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">İlerleme Güncelle (%)</label>
                                    <input type="number" name="progress" class="form-control" 
                                           min="0" max="100" value="{{ $image->progress }}" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    İlerlemeyi Güncelle
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($image->admin_image_url)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Sonuç</h5>
                            </div>
                            <div class="card-body">
                                <a href="{{ $image->admin_image_url }}" 
                                   class="btn btn-success w-100" 
                                   target="_blank">
                                    Görseli Aç
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
