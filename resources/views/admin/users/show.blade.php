@extends('admin.layouts.app')

@section('title', __('admin.User Details'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('admin.User Details') }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">{{ __('admin.Back') }}</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>User Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $user->id }}</p>
                    <p><strong>{{ __('admin.Name') }}:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                    <p><strong>{{ __('admin.Email') }}:</strong> {{ $user->email }}</p>
                    <p><strong>Phone:</strong> {{ $user->country_code }} {{ $user->phone }}</p>
                    <p><strong>Registered:</strong> {{ $user->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>{{ __('tokens.Balance') }}</h5>
                </div>
                <div class="card-body">
                    <h2 class="text-primary">{{ $balance }} {{ __('packages.tokens') }}</h2>
                    <div class="mt-3">
                        <a href="{{ route('admin.users.tokens.add', $user->id) }}" class="btn btn-success">
                            {{ __('tokens.Add Tokens') }}
                        </a>
                        <a href="{{ route('admin.users.tokens.deduct', $user->id) }}" class="btn btn-warning">
                            {{ __('tokens.Deduct Tokens') }}
                        </a>
                        <a href="{{ route('admin.users.tokens.transactions', $user->id) }}" class="btn btn-secondary">
                            {{ __('tokens.Token History') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>{{ __('tokens.Transactions') }} (Latest 10)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('tokens.Type') }}</th>
                            <th>{{ __('tokens.Amount') }}</th>
                            <th>{{ __('tokens.Note') }}</th>
                            <th>{{ __('tokens.Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->tokenTransactions->take(10) as $transaction)
                            <tr>
                                <td>{{ __('tokens.' . $transaction->type) }}</td>
                                <td>
                                    <span class="badge {{ $transaction->amount > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                    </span>
                                </td>
                                <td>{{ $transaction->note }}</td>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">{{ __('tokens.no_transactions') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
