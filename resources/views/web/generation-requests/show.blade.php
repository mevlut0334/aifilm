@extends('web.layouts.app')

@section('title', 'Talep Detayı')

@section('content')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">Talep Detayı</h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Durum:</strong></div>
                        <div class="col-sm-8">
                            @if($request->status === 'pending')
                                <span class="badge bg-warning">Bekliyor</span>
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
                        <div class="col-sm-4"><strong>İlerleme:</strong></div>
                        <div class="col-sm-8">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $request->progress }}%;"
                                     aria-valuenow="{{ $request->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $request->progress }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Tip:</strong></div>
                        <div class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $request->type)) }}</div>
                    </div>

                    @if($request->template)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Template:</strong></div>
                            <div class="col-sm-8">{{ $request->template->title }}</div>
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

                    @if($request->input_image_path)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Yüklenen Görsel:</strong></div>
                            <div class="col-sm-8">
                                <img src="{{ asset('storage/' . $request->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 300px;">
                            </div>
                        </div>
                    @endif

                    @if($request->output_url)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Sonuç:</strong></div>
                            <div class="col-sm-8">
                                <a href="{{ $request->output_url }}" 
                                   class="btn btn-success" 
                                   target="_blank" 
                                   download>
                                    <i class="bi bi-download"></i> İndir
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($request->failure_reason)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Hata Nedeni:</strong></div>
                            <div class="col-sm-8">
                                <div class="alert alert-danger">{{ $request->failure_reason }}</div>
                            </div>
                        </div>
                    @endif

                    @if($request->description)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Açıklama:</strong></div>
                            <div class="col-sm-8">{{ $request->description }}</div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('generation-requests.index') }}" class="btn btn-secondary">
                            Geri Dön
                        </a>

                        @if(in_array($request->status, ['pending', 'failed']))
                            <form method="POST" 
                                  action="{{ route('generation-requests.destroy', $request->uuid) }}" 
                                  class="d-inline"
                                  onsubmit="return confirm('Bu talebi iptal etmek istediğinize emin misiniz? Tokenlarınız iade edilecektir.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    Talebi İptal Et
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
