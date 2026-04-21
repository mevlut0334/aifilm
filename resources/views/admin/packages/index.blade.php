@extends('admin.layouts.app')

@section('title', __('packages.Package List'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('packages.Package List') }}</h1>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
            {{ __('packages.Create Package') }}
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
                            <th>ID</th>
                            <th>Title</th>
                            <th>{{ __('packages.Token Amount') }}</th>
                            <th>Paddle Price ID</th>
                            <th>{{ __('packages.Is Active') }}</th>
                            <th>{{ __('packages.Order') }}</th>
                            <th>{{ __('admin.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td>{{ $package->id }}</td>
                                <td>{{ $package->getTitle('tr') }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $package->token_amount }}</span>
                                </td>
                                <td>
                                    <code>{{ $package->paddle_price_id }}</code>
                                </td>
                                <td>
                                    @if($package->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $package->order }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.packages.edit', $package->id) }}" class="btn btn-sm btn-warning">
                                            {{ __('admin.Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.packages.destroy', $package->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('admin.Are you sure?') }}')">
                                                {{ __('admin.Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ __('packages.no_packages') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
