@extends('admin.layouts.app')

@section('title', __('admin.User List'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('admin.User List') }}</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" id="emailSearchForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="email" 
                                id="emailSearch" 
                                class="form-control" 
                                placeholder="{{ __('admin.Search by email') }}..." 
                                value="{{ request('email') }}"
                                minlength="3"
                            >
                            <button type="submit" class="btn btn-primary">
                                {{ __('admin.Search') }}
                            </button>
                            @if(request('email'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                    {{ __('admin.Clear') }}
                                </a>
                            @endif
                        </div>
                        <small class="text-muted">{{ __('admin.Minimum 3 characters required') }}</small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('admin.Name') }}</th>
                            <th>{{ __('admin.Email') }}</th>
                            <th>{{ __('admin.Token Balance') }}</th>
                            <th>{{ __('admin.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $user->tokenBalance->balance ?? 0 }} {{ __('packages.tokens') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info">
                                            {{ __('admin.View') }}
                                        </a>
                                        <a href="{{ route('admin.users.tokens.add', $user->id) }}" class="btn btn-sm btn-success">
                                            {{ __('tokens.Add Tokens') }}
                                        </a>
                                        <a href="{{ route('admin.users.tokens.deduct', $user->id) }}" class="btn btn-sm btn-warning">
                                            {{ __('tokens.Deduct Tokens') }}
                                        </a>
                                        <a href="{{ route('admin.users.tokens.transactions', $user->id) }}" class="btn btn-sm btn-secondary">
                                            {{ __('tokens.Token History') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('emailSearch');
    const searchForm = document.getElementById('emailSearchForm');
    let searchTimeout;

    emailInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        const searchValue = this.value.trim();
        
        if (searchValue.length >= 3) {
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 500);
        } else if (searchValue.length === 0 && '{{ request('email') }}' !== '') {
            window.location.href = '{{ route('admin.users.index') }}';
        }
    });
});
</script>
@endsection
