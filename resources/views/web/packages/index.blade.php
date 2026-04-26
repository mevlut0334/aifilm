@extends('web.layouts.app')

@section('title', trans('packages.Buy Tokens'))

@section('content')

<style>
.container h1 {
    color: #FFFFFF !important;
    font-weight: bold;
}

.container .lead {
    color: #FFFFFF !important;
}

/* Consent note */
.purchase-consent {
    margin-top: 2.5rem;
    padding: 1rem 1.4rem;
    background: rgba(212, 175, 55, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 10px;
    text-align: center;
    font-size: 0.88rem;
    color: #BFBFBF;
    line-height: 1.7;
}

.purchase-consent a {
    color: #D4AF37;
    text-decoration: underline;
    text-underline-offset: 3px;
    text-decoration-color: rgba(212, 175, 55, 0.4);
    transition: color 0.2s;
}

.purchase-consent a:hover {
    color: #F5D97A;
}
</style>

<div class="container">
    <div class="text-center mb-5">
        <h1>@trans_safe('packages.Buy Tokens')</h1>
        @auth
            <p class="lead">@trans_safe('tokens.current_balance'): <span class="badge bg-primary fs-5">{{ auth()->user()->tokenBalance->balance ?? 0 }} @trans_safe('packages.tokens')</span></p>
        @else
            <p class="lead text-muted">@trans_safe('packages.login_to_see_balance')</p>
        @endauth
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($packages->isEmpty())
        <div class="alert alert-info text-center">
            @trans_safe('packages.no_packages')
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
                                    {{ $package->token_amount }} @trans_safe('packages.tokens')
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
                            <div class="card-text">
                                {!! str_replace('- ', '<i class="bi bi-check-circle text-success"></i> ', nl2br(e($package->getDescription()))) !!}
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            @if($package->is_active)
                                @auth
                                    <button type="button"
                                            class="btn btn-primary w-100"
                                            onclick="purchasePackage('{{ $package->paddle_price_id }}', {{ $package->id }})">
                                        @trans_safe('packages.purchase_now')
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary w-100">
                                        @trans_safe('packages.login_to_purchase')
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

        {{-- Consent Note --}}
        <div class="purchase-consent">
            {{ __('packages.consent_text') }}
            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('terms', [], false)) }}">{{ __('packages.consent_terms') }}</a>
            {{ __('packages.consent_and') }}
            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('refund', [], false)) }}">{{ __('packages.consent_refund') }}</a>{{ __('packages.consent_end') }}
        </div>

    @endif
</div>

@auth
<script src="https://cdn.paddle.com/paddle/paddle.js"></script>
<script>
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
