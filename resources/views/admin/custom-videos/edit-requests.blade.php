@extends('admin.layouts.app')

@section('title', 'Video Segmenti Düzenleme Talepleri')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Video Segmenti Düzenleme Talepleri</h1>
                <a href="{{ route('admin.custom-videos.index') }}" class="btn btn-secondary">
                    Geri Dön
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($editRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı</th>
                                        <th>Video Talebi</th>
                                        <th>Segment #</th>
                                        <th>Düzenleme Prompt'u</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($editRequests as $editRequest)
                                        <tr>
                                            <td>{{ $editRequest->id }}</td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $editRequest->segment->customVideoRequest->user_id) }}">
                                                    {{ $editRequest->segment->customVideoRequest->user->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.custom-videos.show', $editRequest->segment->customVideoRequest->uuid) }}">
                                                    {{ Str::limit($editRequest->segment->customVideoRequest->prompt, 50) }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">Segment {{ $editRequest->segment->segment_number }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal" data-bs-target="#promptModal{{ $editRequest->id }}">
                                                    Prompt'u Gör
                                                </button>

                                                <!-- Prompt Modal -->
                                                <div class="modal fade" id="promptModal{{ $editRequest->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Düzenleme Prompt'u</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>{{ $editRequest->edit_prompt }}</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($editRequest->status === 'pending')
                                                    <span class="badge bg-warning">Beklemede</span>
                                                @elseif($editRequest->status === 'processing')
                                                    <span class="badge bg-info">İşleniyor</span>
                                                @elseif($editRequest->status === 'completed')
                                                    <span class="badge bg-success">Tamamlandı</span>
                                                @else
                                                    <span class="badge bg-danger">Reddedildi</span>
                                                @endif
                                            </td>
                                            <td>{{ $editRequest->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $editRequest->id }}">
                                                    İşle
                                                </button>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="editModal{{ $editRequest->id }}" tabindex="-1">
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
                                                                        <strong>Kullanıcının Talebi:</strong>
                                                                        <p class="bg-light p-3 rounded">{{ $editRequest->edit_prompt }}</p>
                                                                    </div>

                                                                                    @if($editRequest->segment->video_url)
                                                                                        <div class="mb-3">
                                                                                            <strong>Mevcut Video:</strong><br>
                                                                                            @php
                                                                                                $isExternal = filter_var($editRequest->segment->video_url, FILTER_VALIDATE_URL);
                                                                                                $videoUrl = $isExternal ? $editRequest->segment->video_url : asset('storage/' . $editRequest->segment->video_url);
                                                                                            @endphp
                                                                                            <a href="{{ $videoUrl }}" target="_blank" class="btn btn-sm btn-info">
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
                                                                            <option value="completed" {{ $editRequest->status === 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                                                                            <option value="rejected" {{ $editRequest->status === 'rejected' ? 'selected' : '' }}>Reddet</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="admin_note{{ $editRequest->id }}" class="form-label">Admin Notu</label>
                                                                        <textarea name="admin_note" 
                                                                                  id="admin_note{{ $editRequest->id }}" 
                                                                                  class="form-control" 
                                                                                  rows="3">{{ $editRequest->admin_note }}</textarea>
                                                                        <small class="text-muted">Reddediyorsanız neden reddettiğinizi açıklayın.</small>
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
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $editRequests->links() }}
                        </div>
                    @else
                        <p class="text-muted text-center">Henüz düzenleme talebi bulunmamaktadır.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
