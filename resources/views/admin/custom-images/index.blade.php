@extends('admin.layouts.app')

@section('title', 'Custom Görseller')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Custom Görsel Talepleri</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($images->isEmpty())
        <div class="alert alert-info">
            Hiç custom görsel talebi bulunmamaktadır.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Kullanıcı</th>
                        <th>Prompt</th>
                        <th>Format</th>
                        <th>Token</th>
                        <th>Durum</th>
                        <th>İlerleme</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($images as $image)
                        <tr>
                            <td>{{ $image->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $image->user_id) }}">
                                    {{ $image->user->name }}
                                </a>
                            </td>
                            <td>{{ Str::limit($image->prompt, 50) }}</td>
                            <td>
                                @if($image->format === 'vertical')
                                    Dikey
                                @elseif($image->format === 'horizontal')
                                    Yatay
                                @elseif($image->format === 'square')
                                    Kare
                                @else
                                    {{ $image->format }}
                                @endif
                            </td>
                            <td>{{ $image->token_cost }}</td>
                            <td>
                                @if($image->status === 'pending')
                                    <span class="badge bg-warning">Bekliyor</span>
                                @elseif($image->status === 'processing')
                                    <span class="badge bg-info">İşleniyor</span>
                                @elseif($image->status === 'completed')
                                    <span class="badge bg-success">Tamamlandı</span>
                                @else
                                    <span class="badge bg-danger">Başarısız</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="width: 100px; height: 20px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: {{ $image->progress }}%" 
                                         aria-valuenow="{{ $image->progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $image->progress }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.custom-images.show', $image->uuid) }}" 
                                   class="btn btn-sm btn-primary">
                                    Detay
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $images->links() }}
        </div>
    @endif
</div>
@endsection
