@extends('admin.layouts.app')

@section('title', 'Özel Video Talepleri')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Özel Video Talepleri</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>UUID</th>
                            <th>Kullanıcı</th>
                            <th>Prompt</th>
                            <th>Durum</th>
                            <th>Token</th>
                            <th>Segmentler</th>
                            <th>İlerleme</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr>
                                <td><code>{{ Str::limit($request->uuid, 8, '') }}</code></td>
                                <td>
                                    <a href="{{ route('admin.users.show', $request->user_id) }}">
                                        {{ $request->user->name }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($request->prompt, 50) }}</td>
                                <td>
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($request->status === 'processing')
                                        <span class="badge bg-info">İşleniyor</span>
                                    @elseif($request->status === 'completed')
                                        <span class="badge bg-success">Tamamlandı</span>
                                    @else
                                        <span class="badge bg-danger">Başarısız</span>
                                    @endif
                                </td>
                                <td>{{ $request->token_cost }}</td>
                                <td>
                                    {{ $request->segments->where('status', 'completed')->count() }} / 
                                    {{ $request->segments->count() }}
                                </td>
                                <td>
                                    @if($request->segments->count() > 0)
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: {{ $request->getOverallProgress() }}%"
                                                 aria-valuenow="{{ $request->getOverallProgress() }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $request->getOverallProgress() }}%
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.custom-videos.show', $request->uuid) }}" 
                                       class="btn btn-sm btn-primary">
                                        Detay
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Henüz özel video talebi bulunmamaktadır.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
