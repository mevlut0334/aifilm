@extends('admin.layouts.app')

@section('title', 'Mobile Packages')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mobile Packages</h1>
        <a href="{{ route('admin.mobile-packages.create') }}" class="btn btn-primary">
            Create Mobile Package
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
                            <th>Token Amount</th>
                            <th>iOS Product ID</th>
                            <th>Android Product ID</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Actions</th>
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
                                    @if($package->ios_product_id)
                                        <code>{{ $package->ios_product_id }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($package->android_product_id)
                                        <code>{{ $package->android_product_id }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
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
                                        <a href="{{ route('admin.mobile-packages.edit', $package->id) }}" class="btn btn-sm btn-warning">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.mobile-packages.destroy', $package->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No mobile packages found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
