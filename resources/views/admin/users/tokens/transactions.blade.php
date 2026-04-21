@extends('admin.layouts.app')

@section('title', __('tokens.Token History'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('tokens.Token History') }}</h1>
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">{{ __('admin.Back') }}</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>User:</strong> {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</p>
            <p><strong>{{ __('tokens.current_balance') }}:</strong> 
                <span class="badge bg-primary fs-5">{{ $balance }} {{ __('packages.tokens') }}</span>
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('tokens.Date') }}</th>
                            <th>{{ __('tokens.Type') }}</th>
                            <th>{{ __('tokens.Amount') }}</th>
                            <th>{{ __('tokens.Note') }}</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ __('tokens.' . $transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $transaction->amount > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                                    </span>
                                </td>
                                <td>{{ $transaction->note }}</td>
                                <td>
                                    @if($transaction->reference_id)
                                        <small class="text-muted">
                                            {{ $transaction->reference_type }}: {{ $transaction->reference_id }}
                                        </small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('tokens.no_transactions') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
