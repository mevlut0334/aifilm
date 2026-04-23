@extends('web.layouts.app')

@section('title', 'Taleplerim')

@section('content')

<div class="container mt-4">
    <h1 class="mb-4">Taleplerim</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($requests->isEmpty())
        <div class="alert alert-info">
            Henüz talebiniz bulunmamaktadır.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Tip</th>
                        <th>Template</th>
                        <th>Token</th>
                        <th>Durum</th>
                        <th>İlerleme</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $request->type)) }}
                                </span>
                            </td>
                            <td>
                                @if($request->template)
                                    {{ $request->template->title }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $request->token_cost }}</td>
                            <td>
                                @if($request->status === 'pending')
                                    <span class="badge bg-warning">Bekliyor</span>
                                @elseif($request->status === 'processing')
                                    <span class="badge bg-info">İşleniyor</span>
                                @elseif($request->status === 'completed')
                                    <span class="badge bg-success">Tamamlandı</span>
                                @else
                                    <span class="badge bg-danger">Başarısız</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="width: 100px; height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $request->progress }}%;"
                                         aria-valuenow="{{ $request->progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $request->progress }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('generation-requests.show', $request->uuid) }}" 
                                   class="btn btn-sm btn-primary">
                                    Detay
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $requests->links() }}
    @endif
</div>

@endsection
