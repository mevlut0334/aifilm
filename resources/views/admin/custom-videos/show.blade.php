@extends('admin.layouts.app')

@section('title', 'Özel Video Detayı')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Özel Video Detayı</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.custom-videos.index') }}" class="btn btn-secondary">
                        Geri Dön
                    </a>
                    <form method="POST" action="{{ route('admin.custom-videos.destroy', $request->uuid) }}" 
                          onsubmit="return confirm('Bu talebi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Talebi Sil
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <!-- Request Info -->
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
                                <div class="col-sm-4"><strong>Prompt:</strong></div>
                                <div class="col-sm-9">
                                    <p class="mb-0">{{ $request->prompt }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Video Formatı:</strong></div>
                                <div class="col-sm-9">
                                    @if($request->format === 'vertical')
                                        <span class="badge bg-info">Dikey (9:16)</span>
                                    @elseif($request->format === 'horizontal')
                                        <span class="badge bg-primary">Yatay (16:9)</span>
                                    @else
                                        <span class="badge bg-secondary">Kare (1:1)</span>
                                    @endif
                                </div>
                            </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Token Maliyeti:</strong></div>
                                <div class="col-sm-8">
                                    {{ $request->token_cost ?? 'Belirlenmedi' }}
                                    @if($request->token_deducted)
                                        <span class="badge bg-success ms-2">Düşüldü</span>
                                    @elseif($request->token_cost)
                                        <span class="badge bg-warning ms-2">Beklemede</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Durum:</strong></div>
                                <div class="col-sm-8">
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($request->status === 'processing')
                                        <span class="badge bg-info">İşleniyor</span>
                                    @elseif($request->status === 'completed')
                                        <span class="badge bg-success">Tamamlandı</span>
                                    @else
                                        <span class="badge bg-danger">Başarısız</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Oluşturulma:</strong></div>
                                <div class="col-sm-8">{{ $request->created_at->format('d.m.Y H:i:s') }}</div>
                            </div>

                            @if($request->failure_reason)
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Hata Nedeni:</strong></div>
                                    <div class="col-sm-8">
                                        <span class="text-danger">{{ $request->failure_reason }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($request->input_image_path)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Ana Referans Görsel</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/' . $request->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 500px;">
                            </div>
                        </div>
                    @endif

                    @if($request->referenceImages->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Ek Referans Görseller</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($request->referenceImages as $refImage)
                                        <div class="col-md-4">
                                            <img src="{{ asset('storage/' . $refImage->image_path) }}" 
                                                 alt="Reference Image {{ $refImage->order + 1 }}" 
                                                 class="img-fluid rounded border"
                                                 style="width: 100%; height: 200px; object-fit: cover;">
                                            <p class="text-center mt-1 mb-0"><small>Görsel {{ $refImage->order + 1 }}</small></p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Segments -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Video Segmentleri</h5>
                            <form action="{{ route('admin.custom-videos.add-segment', $request->uuid) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Yeni Segment Ekle
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            @if($request->segments->count() > 0)
                                @foreach($request->segments as $segment)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                Segment #{{ $segment->segment_number }}
                                                @if($segment->status === 'pending')
                                                    <span class="badge bg-warning">Beklemede</span>
                                                @elseif($segment->status === 'processing')
                                                    <span class="badge bg-info">İşleniyor</span>
                                                @elseif($segment->status === 'completed')
                                                    <span class="badge bg-success">Tamamlandı</span>
                                                @else
                                                    <span class="badge bg-danger">Başarısız</span>
                                                @endif
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>İlerleme:</strong>
                                                <div class="progress mt-2">
                                                    <div class="progress-bar" 
                                                         role="progressbar" 
                                                         style="width: {{ $segment->progress }}%"
                                                         aria-valuenow="{{ $segment->progress }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $segment->progress }}%
                                                    </div>
                                                </div>
                                            </div>

                                            @if($segment->video_url)
                                                <div class="mb-3">
                                                    <strong>Video URL:</strong><br>
                                                    @php
                                                        $isExternalUrl = filter_var($segment->video_url, FILTER_VALIDATE_URL);
                                                        $displayUrl = $isExternalUrl ? $segment->video_url : asset('storage/' . $segment->video_url);
                                                    @endphp
                                                    <a href="{{ $displayUrl }}" target="_blank">
                                                        {{ Str::limit($segment->video_url, 80) }}
                                                    </a>
                                                    @if($isExternalUrl)
                                                        <span class="badge bg-info">External</span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($segment->failure_reason)
                                                <div class="mb-3">
                                                    <strong>Hata Nedeni:</strong><br>
                                                    <span class="text-danger">{{ $segment->failure_reason }}</span>
                                                </div>
                                            @endif

                                            <!-- Edit Requests -->
                                            @if($segment->editRequests->count() > 0)
                                                <div class="mb-3">
                                                    <strong>Düzenleme Talepleri:</strong>
                                                    <div class="mt-2">
                                                        @foreach($segment->editRequests as $editRequest)
                                                            <div class="alert alert-{{ $editRequest->status === 'pending' ? 'warning' : ($editRequest->status === 'processing' ? 'info' : ($editRequest->status === 'completed' ? 'success' : 'danger')) }}">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div class="flex-grow-1">
                                                                        <strong>Prompt:</strong> {{ Str::limit($editRequest->edit_prompt, 100) }}<br>
                                                                        <strong>Durum:</strong> 
                                                                        @if($editRequest->status === 'pending')
                                                                            <span class="badge bg-warning">Beklemede</span>
                                                                        @elseif($editRequest->status === 'processing')
                                                                            <span class="badge bg-info">İşleniyor</span>
                                                                        @elseif($editRequest->status === 'completed')
                                                                            <span class="badge bg-success">Tamamlandı</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Reddedildi</span>
                                                                        @endif
                                                                        <br>
                                                                        @if($editRequest->admin_note)
                                                                            <strong>Admin Notu:</strong> {{ $editRequest->admin_note }}<br>
                                                                        @endif
                                                                        <small class="text-muted">{{ $editRequest->created_at->format('d.m.Y H:i') }}</small>
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editRequestModal{{ $editRequest->id }}">
                                                                        Düzenle
                                                                    </button>
                                                                </div>

                                                                <!-- Edit Request Modal -->
                                                                <div class="modal fade" id="editRequestModal{{ $editRequest->id }}" tabindex="-1">
                                                                    <div class="modal-dialog modal-lg">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Düzenleme Talebini İşle</h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <form action="{{ route('admin.custom-videos.edit-requests.update-status', $editRequest->id) }}" method="POST">
                                                                                @csrf
                                                                                <div class="modal-body">
                                                                                    <div class="mb-3">
                                                                                        <strong>Kullanıcının Düzenleme Talebi:</strong>
                                                                                        <p class="bg-light p-3 rounded">{{ $editRequest->edit_prompt }}</p>
                                                                                    </div>

                                                                                    @if($segment->video_url)
                                                                                        <div class="mb-3">
                                                                                            <strong>Mevcut Video:</strong><br>
                                                                                            <a href="{{ asset('storage/' . $segment->video_url) }}" target="_blank" class="btn btn-sm btn-info" download>
                                                                                                <i class="fas fa-download"></i> Videoyu İndir ve İncele
                                                                                            </a>
                                                                                        </div>
                                                                                    @endif

                                                                                    <div class="mb-3">
                                                                                        <label for="edit_cost{{ $editRequest->id }}" class="form-label">Düzenleme Token Maliyeti</label>
                                                                                        <input type="number" 
                                                                                               name="edit_cost" 
                                                                                               id="edit_cost{{ $editRequest->id }}" 
                                                                                               class="form-control" 
                                                                                               value="{{ $editRequest->edit_cost ?? 50 }}"
                                                                                               min="0">
                                                                                        <small class="text-muted">
                                                                                            @if($editRequest->token_deducted)
                                                                                                <span class="badge bg-success">Token düşüldü ({{ $editRequest->edit_cost }})</span>
                                                                                            @else
                                                                                                Tamamlandı olarak işaretlerseniz bu miktar kullanıcıdan düşülecektir.
                                                                                            @endif
                                                                                        </small>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <label for="status{{ $editRequest->id }}" class="form-label">Durum</label>
                                                                                        <select name="status" id="status{{ $editRequest->id }}" class="form-select" required>
                                                                                            <option value="processing" {{ $editRequest->status === 'processing' ? 'selected' : '' }}>İşleniyor</option>
                                                                                            <option value="completed" {{ $editRequest->status === 'completed' ? 'selected' : '' }}>Tamamlandı (Yeni video yüklediyseniz)</option>
                                                                                            <option value="rejected" {{ $editRequest->status === 'rejected' ? 'selected' : '' }}>Reddet</option>
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <label for="admin_note{{ $editRequest->id }}" class="form-label">Admin Notu</label>
                                                                                        <textarea name="admin_note" 
                                                                                                  id="admin_note{{ $editRequest->id }}" 
                                                                                                  class="form-control" 
                                                                                                  rows="3">{{ $editRequest->admin_note }}</textarea>
                                                                                        <small class="text-muted">Reddediyorsanız neden reddettiğinizi açıklayın. Tamamlandıysa kullanıcıya bilgi verebilirsiniz.</small>
                                                                                    </div>

                                                                                    <div class="alert alert-info">
                                                                                        <strong>Not:</strong> Düzenlemeyi tamamladıktan sonra yukarıdaki "Video URL Güncelle" formundan yeni video linkini ekleyin ve bu talebi "Tamamlandı" olarak işaretleyin.
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                                    <button type="submit" class="btn btn-primary">Kaydet</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Update Progress Form -->
                                            <form action="{{ route('admin.custom-videos.segments.update-progress', $segment->id) }}" 
                                                  method="POST" 
                                                  class="mb-2">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <input type="number" 
                                                           name="progress" 
                                                           class="form-control" 
                                                           value="{{ $segment->progress }}" 
                                                           min="0" 
                                                           max="100"
                                                           required>
                                                    <button type="submit" class="btn btn-primary">İlerleme Güncelle</button>
                                                </div>
                                            </form>

                                            <!-- Update Video URL Form -->
                                            <form action="{{ route('admin.custom-videos.segments.update-video-url', $segment->id) }}" 
                                                  method="POST" 
                                                  class="mb-2">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <input type="text" 
                                                           name="video_url" 
                                                           class="form-control" 
                                                           placeholder="Video URL girin (Drive link veya storage path)" 
                                                           value="{{ $segment->video_url }}"
                                                           required>
                                                    <button type="submit" class="btn btn-success">Video URL Güncelle</button>
                                                </div>
                                                <small class="text-muted">Drive link veya storage/videos/file.mp4 formatında girebilirsiniz.</small>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Henüz segment oluşturulmadı.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Set Token Cost -->
                    @if(!$request->token_deducted)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Token Maliyeti Belirle ve Düş</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.custom-videos.set-token-cost', $request->uuid) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="token_cost" class="form-label">Token Maliyeti</label>
                                        <input type="number" 
                                               name="token_cost" 
                                               id="token_cost" 
                                               class="form-control" 
                                               value="{{ $request->token_cost ?? 100 }}"
                                               min="1" 
                                               required>
                                        <small class="text-muted">Video işlendikçe token kullanıcıdan düşülecektir.</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Token Düş</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Create Segments -->
                    @if($request->segments->count() == 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Segment Oluştur</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.custom-videos.create-segments', $request->uuid) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="number_of_segments" class="form-label">Segment Sayısı</label>
                                        <input type="number" 
                                               name="number_of_segments" 
                                               id="number_of_segments" 
                                               class="form-control" 
                                               min="1" 
                                               max="20" 
                                               value="3"
                                               required>
                                        <small class="text-muted">Video kaç parçaya bölünecek?</small>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Segmentleri Oluştur</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Update Status -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Durum Güncelle</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.custom-videos.update-status', $request->uuid) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="status" class="form-label">Durum</label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Beklemede</option>
                                        <option value="processing" {{ $request->status === 'processing' ? 'selected' : '' }}>İşleniyor</option>
                                        <option value="completed" {{ $request->status === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                        <option value="failed" {{ $request->status === 'failed' ? 'selected' : '' }}>Başarısız</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="failure_reason" class="form-label">Hata Nedeni (opsiyonel)</label>
                                    <textarea name="failure_reason" 
                                              id="failure_reason" 
                                              class="form-control" 
                                              rows="3">{{ $request->failure_reason }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Durumu Güncelle</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
