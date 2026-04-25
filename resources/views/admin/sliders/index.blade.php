@extends('admin.layouts.app')

@section('title', 'Slider Yönetimi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Slider Yönetimi</h1>
        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
            </svg>
            Yeni Slider Ekle
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($sliders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Görsel</th>
                                <th>Başlık (TR)</th>
                                <th>Başlık (EN)</th>
                                <th style="width: 80px;">Sıra</th>
                                <th style="width: 100px;">Durum</th>
                                <th style="width: 200px;" class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sliders as $slider)
                                <tr>
                                    <td>
                                        <img src="{{ $slider->image_url }}" 
                                             alt="Slider" 
                                             class="img-thumbnail"
                                             style="max-width: 80px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>{{ $slider->title['tr'] ?? '-' }}</td>
                                    <td>{{ $slider->title['en'] ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $slider->order }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.sliders.toggle-active', $slider) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $slider->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                    style="min-width: 70px;">
                                                {{ $slider->is_active ? 'Aktif' : 'Pasif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.sliders.edit', $slider) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                Düzenle
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="if(confirm('Bu slider\'ı silmek istediğinize emin misiniz?')) document.getElementById('delete-form-{{ $slider->id }}').submit();">
                                                Sil
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $slider->id }}" 
                                              action="{{ route('admin.sliders.destroy', $slider) }}" 
                                              method="POST" 
                                              class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $sliders->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted mb-3">Henüz hiç slider eklenmemiş.</p>
                    <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
                        İlk Slider'ı Ekle
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
