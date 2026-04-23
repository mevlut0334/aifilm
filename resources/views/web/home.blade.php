@extends('web.layouts.app')

@section('title', __('home.title'))

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">{{ __('home.welcome_title') }}</h1>
        <p class="lead text-muted">{{ __('home.welcome_subtitle') }}</p>
    </div>

    @if($templates->count() > 0)
        <section class="mb-5">
            <h2 class="h3 mb-4">{{ __('home.templates') }}</h2>
            <div class="row g-4">
                @foreach($templates as $template)
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">{{ $template->title }}</h5>
                                <p class="card-text text-muted">{{ Str::limit($template->description, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">{{ $template->token_cost }} {{ __('home.tokens') }}</span>
                                    <a href="{{ route('templates.show', $template->uuid) }}" class="btn btn-sm btn-outline-primary">
                                        {{ __('home.view_template') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="text-center mt-5">
        <a href="{{ route('templates.index') }}" class="btn btn-lg btn-primary">
            {{ __('home.view_all_templates') }}
        </a>
    </section>
</div>
@endsection
