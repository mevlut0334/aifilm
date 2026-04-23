@extends('web.layouts.app')

@section('title', __('custom_images.title_detail'))

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">{{ __('custom_images.title_detail') }}</h1>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('custom_images.status') }}:</strong></div>
                        <div class="col-sm-8">
                            @if($image->status === 'pending')
                                <span class="badge bg-warning">{{ __('custom_images.status_pending') }}</span>
                            @elseif($image->status === 'processing')
                                <span class="badge bg-info">{{ __('custom_images.status_processing') }}</span>
                            @elseif($image->status === 'completed')
                                <span class="badge bg-success">{{ __('custom_images.status_completed') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('custom_images.status_failed') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('custom_images.progress') }}:</strong></div>
                        <div class="col-sm-8">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: {{ $image->progress }}%" 
                                     aria-valuenow="{{ $image->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $image->progress }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('custom_images.prompt_label') }}:</strong></div>
                        <div class="col-sm-8">
                            <p class="mb-0">{{ $image->prompt }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('custom_images.format') }}:</strong></div>
                        <div class="col-sm-8">
                            @if($image->format === 'vertical')
                                {{ __('custom_images.format_vertical') }}
                            @elseif($image->format === 'horizontal')
                                {{ __('custom_images.format_horizontal') }}
                            @elseif($image->format === 'square')
                                {{ __('custom_images.format_square') }}
                            @else
                                {{ $image->format }}
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('custom_images.token_cost') }}:</strong></div>
                        <div class="col-sm-8">{{ $image->token_cost }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('custom_images.created_at') }}:</strong></div>
                        <div class="col-sm-8">{{ $image->created_at->format('d.m.Y H:i:s') }}</div>
                    </div>

                    @if($image->input_image_path)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('custom_images.reference_image') }}:</strong></div>
                            <div class="col-sm-8">
                                <img src="{{ asset('storage/' . $image->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 300px;">
                            </div>
                        </div>
                    @endif

                    @if($image->referenceImages->count() > 0)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('custom_images.reference_images') }}:</strong></div>
                            <div class="col-sm-8">
                                <div class="row g-2">
                                    @foreach($image->referenceImages as $reference)
                                        <div class="col-md-4">
                                            <img src="{{ asset('storage/' . $reference->image_path) }}" 
                                                 alt="Reference Image {{ $reference->order + 1 }}" 
                                                 class="img-fluid rounded"
                                                 style="max-width: 100%;">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($image->status === 'completed' && $image->admin_image_url)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('custom_images.result') }}:</strong></div>
                            <div class="col-sm-8">
                                <img src="{{ $image->admin_image_url }}" 
                                     alt="Generated Image" 
                                     class="img-fluid mb-3"
                                     style="max-width: 100%;">
                                <br>
                                <a href="{{ $image->admin_image_url }}" 
                                   class="btn btn-success" 
                                   target="_blank" 
                                   download>
                                    {{ __('custom_images.download') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($image->failure_reason)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('custom_images.failure_reason') }}:</strong></div>
                            <div class="col-sm-8">
                                <div class="alert alert-danger mb-0">{{ $image->failure_reason }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('custom-images.index') }}" class="btn btn-secondary">
                            {{ __('custom_images.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
