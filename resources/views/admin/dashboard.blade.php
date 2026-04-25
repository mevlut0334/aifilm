@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <h1>
        Admin Dashboard 
        @if($pendingCount > 0)
            <span class="badge bg-danger ms-2">{{ $pendingCount }}</span>
        @endif
    </h1>
    
    @if($pendingRequests->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Bekleyen Talepler</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tip</th>
                            <th>Kullanıcı</th>
                            <th>Açıklama</th>
                            <th>Durum</th>
                            <th>Detaylar</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                        <tr style="cursor: pointer;" onclick="window.location='{{ $request['url'] }}'">
                            <td>
                                @if($request['type'] === 'custom_video')
                                    <span class="badge bg-primary">Custom Video</span>
                                @elseif($request['type'] === 'custom_video_edit')
                                    <span class="badge bg-info">Video Düzenleme</span>
                                @elseif($request['type'] === 'generation_request')
                                    <span class="badge bg-success">Template Talep</span>
                                @elseif($request['type'] === 'custom_image')
                                    <span class="badge bg-warning text-dark">Custom Görsel</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $request['user']->name }}</strong><br>
                                <small class="text-muted">{{ $request['user']->email }}</small>
                            </td>
                            <td>
                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $request['description'] }}">
                                    {{ $request['description'] }}
                                </div>
                            </td>
                            <td>
                                @if($request['status'] === 'pending')
                                    <span class="badge bg-warning text-dark">Beklemede</span>
                                @elseif($request['status'] === 'completed_with_pending')
                                    <span class="badge bg-success">Tamamlandı</span>
                                @elseif($request['status'] === 'pending_edits')
                                    <span class="badge bg-info">Düzenleme Bekliyor</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($request['pending_segments']) && $request['pending_segments'] > 0)
                                    <span class="badge bg-danger">{{ $request['pending_segments'] }} segment bekliyor</span>
                                @elseif(isset($request['pending_edits']) && $request['pending_edits'] > 0)
                                    <span class="badge bg-danger">{{ $request['pending_edits'] }} düzenleme bekliyor</span>
                                @elseif(isset($request['template']))
                                    <span class="badge bg-secondary">{{ $request['template'] }}</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $request['created_at']->format('d.m.Y H:i') }}
                            </td>
                            <td>
                                <a href="{{ $request['url'] }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                                    Görüntüle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info mt-4">
        <strong>Harika!</strong> Şu anda bekleyen talep bulunmuyor.
    </div>
    @endif
</div>

<style>
    .table tbody tr {
        transition: background-color 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: var(--bs-tertiary-bg);
    }
</style>
@endsection
