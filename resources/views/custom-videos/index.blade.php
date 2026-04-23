@extends('web.layouts.app')

@section('title', __('custom_videos.title_index'))

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ __('custom_videos.title_index') }}</h1>
        <a href="{{ route('custom-videos.create') }}" class="btn btn-primary">
            {{ __('custom_videos.new_video') }}
        </a>
    </div>

    @if($requests->isEmpty())
        <div class="alert alert-info">
            {{ __('custom_videos.no_videos') }}
        </div>
    @else
        <div class="row">
            @foreach($requests as $request)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                @if($request->status === 'pending')
                                    <span class="badge bg-warning">{{ __('custom_videos.status_pending') }}</span>
                                @elseif($request->status === 'processing')
                                    <span class="badge bg-info">{{ __('custom_videos.status_processing') }}</span>
                                @elseif($request->status === 'completed')
                                    <span class="badge bg-success">{{ __('custom_videos.status_completed') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('custom_videos.status_failed') }}</span>
                                @endif
                            </h5>
                            <p class="card-text">
                                <small class="text-muted">{{ $request->created_at->format('d.m.Y H:i') }}</small>
                            </p>
                            <p class="card-text">
                                <strong>{{ __('custom_videos.prompt_label') }}:</strong><br>
                                {{ Str::limit($request->prompt, 100) }}
                            </p>
                            <div class="progress mb-3" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $request->getOverallProgress() }}%;"
                                     aria-valuenow="{{ $request->getOverallProgress() }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $request->getOverallProgress() }}%
                                </div>
                            </div>
                            <a href="{{ route('custom-videos.show', $request->uuid) }}" class="btn btn-sm btn-primary">
                                {{ __('custom_videos.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
