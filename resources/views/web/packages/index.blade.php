@extends('web.layouts.app')

@section('title', __('packages.Buy Tokens'))

@section('content')
<div class="container">
    <div class="text-center mb-5">
        <h1>{{ __('packages.Buy Tokens') }}</h1>
        @auth
            <p class="lead">{{ __('tokens.current_balance') }}: <span class="badge bg-primary fs-5">{{ auth()->user()->tokenBalance->balance ?? 0 }} {{ __('packages.tokens') }}</span></p>
        @else
            <p class="lead text-muted">{{ __('packages.login_to_see_balance') }}</p>
        @endauth
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($packages->isEmpty())
        <div class="alert alert-info text-center">
            {{ __('packages.no_packages') }}
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($packages as $package)
                <div class="col">
                    <div class="card h-100 {{ $package->is_active ? '' : 'opacity-50' }}">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ $package->getTitle() }}</h3>
                            <div class="my-4">
                                <h2 class="text-primary">
                                    {{ $package->token_amount }} {{ __('packages.tokens') }}
                                </h2>
                                @if($package->price_details)
                                    <p class="text-muted">
                                        {{ $package->price_details['amount'] ?? 'N/A' }} 
                                        {{ $package->price_details['currency'] ?? '' }}
                                    </p>
                                @else
                                    <p class="text-danger fw-bold">
                                        <i class="bi bi-exclamation-triangle"></i> Price could not be fetched
                                    </p>
                                @endif
                            </div>
                            <p class="card-text">{{ $package->getDescription() }}</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            @if($package->is_active)
                                @auth
                                    <button type="button" 
                                            class="btn btn-primary w-100" 
                                            onclick="purchasePackage('{{ $package->paddle_price_id }}', {{ $package->id }})">
                                        {{ __('packages.purchase_now') }}
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                        {{ __('packages.login_to_purchase') }}
                                    </a>
                                @endauth
                            @else
                                <button class="btn btn-secondary w-100" disabled>
                                    Not Available
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@auth
<script src="https://cdn.paddle.com/paddle/paddle.js"></script>
<script>
    // Initialize Paddle
    Paddle.Setup({ 
        vendor: {{ config('services.paddle.vendor_id', 0) }},
        eventCallback: function(data) {
            if (data.event === 'Checkout.Complete') {
                window.location.reload();
            }
        }
    });

    function purchasePackage(priceId, packageId) {
        Paddle.Checkout.open({
            product: priceId,
            email: '{{ auth()->user()->email }}',
            passthrough: JSON.stringify({
                user_id: {{ auth()->id() }},
                package_id: packageId
            })
        });
    }
</script>
@endauth
@endsection
