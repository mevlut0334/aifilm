@extends('admin.layouts.app')

@section('title', 'Template Listesi')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Template Listesi</h1>
        <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
            Yeni Template Oluştur
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sıra</th>
                            <th>Başlık</th>
                            <th>Token Maliyeti</th>
                            <th>Video Durumu</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>{{ $template->order }}</td>
                                <td>
                                    <strong>{{ $template->getTranslation('title', 'tr') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $template->getTranslation('title', 'en') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $template->token_cost }} token</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($template->landscape_video_path)
                                            <span class="badge bg-success" title="Landscape">L</span>
                                        @else
                                            <span class="badge bg-secondary" title="Landscape">L</span>
                                        @endif
                                        
                                        @if($template->portrait_video_path)
                                            <span class="badge bg-success" title="Portrait">P</span>
                                        @else
                                            <span class="badge bg-secondary" title="Portrait">P</span>
                                        @endif
                                        
                                        @if($template->square_video_path)
                                            <span class="badge bg-success" title="Square">S</span>
                                        @else
                                            <span class="badge bg-secondary" title="Square">S</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.templates.toggle-active', $template->uuid) }}" class="d-inline">
                                        @csrf
                                        @if($template->is_active)
                                            <button type="submit" class="btn btn-sm btn-success">Aktif</button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-secondary">Pasif</button>
                                        @endif
                                    </form>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.templates.edit', $template->uuid) }}" class="btn btn-sm btn-warning">
                                            Düzenle
                                        </a>
                                        <form method="POST" action="{{ route('admin.templates.destroy', $template->uuid) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Template silinecek, emin misiniz?')">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Henüz template oluşturulmamış.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
