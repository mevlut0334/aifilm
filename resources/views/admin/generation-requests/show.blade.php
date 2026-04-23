@extends('admin.layouts.app')

@section('title', 'Talep Detayı')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Talep Detayı</h1>
                <a href="{{ route('admin.generation-requests.index') }}" class="btn btn-secondary">
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
                                <div class="col-sm-8"><code>{{ $request->uuid }}</code></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Kullanıcı:</strong></div>
                                <div class="col-sm-8">
                                    <a href="{{ route('admin.users.show', $request->user_id) }}">
                                        {{ $request->user->name }} ({{ $request->user->email }})
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Tip:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                    </span>
                                </div>
                            </div>

                            @if($request->template)
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Template:</strong></div>
                                    <div class="col-sm-8">
                                        {{ $request->template->title }} 
                                        <small class="text-muted">({{ $request->template->token_cost }} token)</small>
                                    </div>
                                </div>
                            @endif

                            @if($request->orientation)
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Orientation:</strong></div>
                                    <div class="col-sm-8">{{ ucfirst($request->orientation) }}</div>
                                </div>
                            @endif

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Token Maliyeti:</strong></div>
                                <div class="col-sm-8">{{ $request->token_cost }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Oluşturulma:</strong></div>
                                <div class="col-sm-8">{{ $request->created_at->format('d.m.Y H:i:s') }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Güncellenme:</strong></div>
                                <div class="col-sm-8">{{ $request->updated_at->format('d.m.Y H:i:s') }}</div>
                            </div>

                            @if($request->description)
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Açıklama:</strong></div>
                                    <div class="col-sm-8">{{ $request->description }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($request->input_image_path)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Kullanıcı Görseli</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/' . $request->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 500px;">
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
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning fs-6">Bekliyor</span>
                                    @elseif($request->status === 'processing')
                                        <span class="badge bg-info fs-6">İşleniyor</span>
                                    @elseif($request->status === 'completed')
                                        <span class="badge bg-success fs-6">Tamamlandı</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Başarısız</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.generation-requests.update-status', $request->uuid) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Durum Değiştir</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Bekliyor</option>
                                        <option value="processing" {{ $request->status === 'processing' ? 'selected' : '' }}>İşleniyor</option>
                                        <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                        <option value="failed" {{ $request->status === 'failed' ? 'selected' : '' }}>Başarısız</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Output URL (Tamamlandı ise)</label>
                                    <input type="url" name="output_url" class="form-control" 
                                           value="{{ $request->output_url }}"
                                           placeholder="https://example.com/output.mp4">
                                    <small class="text-muted">Kullanıcının indireceği link</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Hata Nedeni (Başarısız ise)</label>
                                    <textarea name="failure_reason" class="form-control" rows="3">{{ $request->failure_reason }}</textarea>
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
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $request->progress }}%;"
                                         aria-valuenow="{{ $request->progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $request->progress }}%
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.generation-requests.update-progress', $request->uuid) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">İlerleme Güncelle (%)</label>
                                    <input type="number" name="progress" class="form-control" 
                                           min="0" max="100" value="{{ $request->progress }}" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    İlerlemeyi Güncelle
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($request->output_url)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Sonuç</h5>
                            </div>
                            <div class="card-body">
                                <a href="{{ $request->output_url }}" 
                                   class="btn btn-success w-100" 
                                   target="_blank">
                                    <i class="bi bi-download"></i> Dosyayı İndir
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
