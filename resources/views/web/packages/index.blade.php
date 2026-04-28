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
.pkg-card {
    background: #121212 !important;
    border: 1px solid #2a2a2a !important;
    border-radius: 12px !important;
    transition: border-color 0.2s, transform 0.2s;
}
.pkg-card:hover {
    border-color: #D4AF37 !important;
    transform: translateY(-3px);
}
.pkg-card .card-title {
    color: #FFFFFF;
    font-weight: 700;
    font-size: 1.2rem;
}
.token-amount {
    font-size: 1.6rem;
    font-weight: 800;
    color: #7C3AED;
}
.price-amount {
    font-size: 2rem;
    font-weight: 800;
    color: #D4AF37;
    line-height: 1.1;
}
.pkg-card .card-text {
    color: #BFBFBF;
    font-size: 0.9rem;
}
.pkg-card .card-footer {
    background: transparent !important;
    border-top: 1px solid #2a2a2a !important;
}
.btn-purchase {
    background: #D4AF37 !important;
    color: #0B0B0B !important;
    border: none !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 0.6rem 1rem !important;
    transition: background 0.2s !important;
}
.btn-purchase:hover {
    background: #F5D97A !important;
    color: #0B0B0B !important;
}
.btn-login {
    background: transparent !important;
    color: #D4AF37 !important;
    border: 1px solid #D4AF37 !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    transition: all 0.2s !important;
}
.btn-login:hover {
    background: #D4AF37 !important;
    color: #0B0B0B !important;
}
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
.balance-badge {
    background: rgba(212, 175, 55, 0.15) !important;
    color: #D4AF37 !important;
    border: 1px solid rgba(212, 175, 55, 0.3);
    font-size: 1rem !important;
}
</style>

<div class="container">
    <div class="text-center mb-5">
        <h1>@trans_safe('packages.Buy Tokens')</h1>
        @auth
            <p class="lead">@trans_safe('tokens.current_balance'):
                <span class="badge balance-badge fs-5">
                    {{ auth()->user()->tokenBalance->balance ?? 0 }} @trans_safe('packages.tokens')
                </span>
            </p>
        @else
            <p class="lead" style="color: #6B6B6B;">@trans_safe('packages.login_to_see_balance')</p>
        @endauth
    </div>

    @if(session('success'))
        <div class="alert" style="background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.3); color: #7C3AED;">
            {{ session('success') }}
        </div>
    @endif

    @if($packages->isEmpty())
        <div class="alert text-center" style="background: #121212; border: 1px solid #2a2a2a; color: #BFBFBF;">
            @trans_safe('packages.no_packages')
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($packages as $package)
                <div class="col">
                    <div class="card h-100 pkg-card {{ $package->is_active ? '' : 'opacity-50' }}">
                        <div class="card-body text-center">
                            <h3 class="card-title">{{ $package->getTitle() }}</h3>
                            <div class="my-4">
                                <div class="token-amount mb-1">
                                    {{ $package->token_amount }} @trans_safe('packages.tokens')
                                </div>
                                @if($package->price_details)
                                    <p class="price-amount mb-0">
                                        ${{ number_format($package->price_details['amount'] ?? 0, 2) }}
                                    </p>
                                @else
                                    <p style="color: #ef4444; font-weight: 600;">
                                        <i class="bi bi-exclamation-triangle"></i> Price could not be fetched
                                    </p>
                                @endif
                            </div>
                            <div class="card-text">
                                {!! str_replace('- ', '<i class="bi bi-check-circle" style="color:#D4AF37;"></i> ', nl2br(e($package->getDescription()))) !!}
                            </div>
                        </div>
                        <div class="card-footer">
                            @if($package->is_active)
                                @auth
                                    <button type="button"
                                            class="btn btn-purchase w-100"
                                            onclick="purchasePackage('{{ $package->paddle_price_id }}', {{ $package->id }})">
                                        @trans_safe('packages.purchase_now')
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-login w-100">
                                        @trans_safe('packages.login_to_purchase')
                                    </a>
                                @endauth
                            @else
                                <button class="btn w-100" style="background:#1e1e1e; color:#6B6B6B; border:1px solid #2a2a2a;" disabled>
                                    Not Available
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="purchase-consent">
            {{ __('packages.consent_text') }}
            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('terms', [], false)) }}">{{ __('packages.consent_terms') }}</a>
            {{ __('packages.consent_and') }}
            <a href="{{ LaravelLocalization::getLocalizedURL(null, route('refund', [], false)) }}">{{ __('packages.consent_refund') }}</a>{{ __('packages.consent_end') }}
        </div>
    @endif
</div>

@auth
<script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<script>
    Paddle.Environment.set('sandbox');
    Paddle.Initialize({
        token: '{{ config('cashier.client_side_token') }}',
        eventCallback: function(data) {
            if (data.name === 'checkout.completed') {
                window.location.href = '{{ route('paddle.success') }}';
            }
        }
    });

    function purchasePackage(priceId, packageId) {
        Paddle.Checkout.open({
            items: [{ priceId: priceId, quantity: 1 }],
            customer: {
                email: '{{ auth()->user()->email }}'
            },
            customData: {
                user_id: {{ auth()->id() }},
                package_id: packageId
            }
        });
    }
</script>
@endauth
@endsection
