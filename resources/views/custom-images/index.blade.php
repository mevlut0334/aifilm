@extends('web.layouts.app')

@section('title', __('custom_images.title_index'))

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('custom_images.title_index') }}</h1>
        <a href="{{ route('custom-images.create') }}" class="btn btn-primary">
            {{ __('custom_images.new_image') }}
        </a>
    </div>

    @if($images->isEmpty())
        <div class="alert alert-info">
            {{ __('custom_images.no_images') }}
        </div>
    @else
        <div class="row">
            @foreach($images as $image)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                @if($image->status === 'pending')
                                    <span class="badge bg-warning">{{ __('custom_images.status_pending') }}</span>
                                @elseif($image->status === 'processing')
                                    <span class="badge bg-info">{{ __('custom_images.status_processing') }}</span>
                                @elseif($image->status === 'completed')
                                    <span class="badge bg-success">{{ __('custom_images.status_completed') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('custom_images.status_failed') }}</span>
                                @endif
                            </h5>
                            <p class="card-text">
                                <small class="text-muted">{{ $image->created_at->format('d.m.Y H:i') }}</small>
                            </p>
                            <p class="card-text">
                                <strong>{{ __('custom_images.prompt_label') }}:</strong><br>
                                {{ Str::limit($image->prompt, 100) }}
                            </p>
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $image->progress }}%;"
                                     aria-valuenow="{{ $image->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $image->progress }}%
                                </div>
                            </div>
                            @if($image->status === 'completed' && $image->admin_image_url)
                                <img src="{{ $image->admin_image_url }}" class="img-fluid mb-3" alt="Custom Image">
                            @endif
                            <a href="{{ route('custom-images.show', $image->uuid) }}" class="btn btn-sm btn-primary">
                                {{ __('custom_images.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $images->links() }}
        </div>
    @endif
</div>
@endsection
