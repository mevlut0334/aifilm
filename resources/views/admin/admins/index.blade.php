@extends('admin.layouts.app')

@section('title', 'Admin Yönetimi')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Admin Yönetimi</h1>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
            Yeni Admin Ekle
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
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>İsim</th>
                            <th>E-posta</th>
                            <th>Oluşturulma Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>
                                    {{ $admin->name }}
                                    @if($admin->id === $currentAdminId)
                                        <span class="badge bg-info">Siz</span>
                                    @endif
                                </td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($admin->id !== $currentAdminId)
                                        <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" 
                                              onsubmit="return confirm('Bu admini silmek istediğinizden emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Sil
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Kendinizi silemezsiniz</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Hiç admin bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
