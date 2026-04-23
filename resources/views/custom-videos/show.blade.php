@extends('web.layouts.app')

@section('title', __('custom_videos.title_detail'))

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <h1 class="mb-4">{{ __('custom_videos.title_detail') }}</h1>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>{{ __('custom_videos.status') }}:</strong></div>
                        <div class="col-sm-9">
                            @if($request->status === 'pending')
                                <span class="badge bg-warning">{{ __('custom_videos.status_pending') }}</span>
                            @elseif($request->status === 'processing')
                                <span class="badge bg-info">{{ __('custom_videos.status_processing') }}</span>
                            @elseif($request->status === 'completed')
                                <span class="badge bg-success">{{ __('custom_videos.status_completed') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('custom_videos.status_failed') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>{{ __('custom_videos.progress') }}:</strong></div>
                        <div class="col-sm-9">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: {{ $request->getOverallProgress() }}%" 
                                     aria-valuenow="{{ $request->getOverallProgress() }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $request->getOverallProgress() }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>{{ __('custom_videos.prompt_label') }}:</strong></div>
                        <div class="col-sm-9">
                            <p class="mb-0">{{ $request->prompt }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>{{ __('custom_videos.format_label') }}:</strong></div>
                        <div class="col-sm-9">
                            @if($request->format === 'vertical')
                                <span class="badge bg-info">{{ __('custom_videos.format_vertical') }} (9:16)</span>
                            @elseif($request->format === 'horizontal')
                                <span class="badge bg-primary">{{ __('custom_videos.format_horizontal') }} (16:9)</span>
                            @else
                                <span class="badge bg-secondary">{{ __('custom_videos.format_square') }} (1:1)</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>{{ __('custom_videos.token_cost') }}:</strong></div>
                        <div class="col-sm-9">{{ $request->token_cost ?? __('custom_videos.not_set') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>{{ __('custom_videos.created_at') }}:</strong></div>
                        <div class="col-sm-9">{{ $request->created_at->format('d.m.Y H:i:s') }}</div>
                    </div>

                    @if($request->input_image_path)
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>{{ __('custom_videos.input_image') }}:</strong></div>
                            <div class="col-sm-9">
                                <img src="{{ asset('storage/' . $request->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 300px;">
                            </div>
                        </div>
                    @endif

                    @if($request->referenceImages->isNotEmpty())
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>{{ __('custom_videos.reference_images') }}:</strong></div>
                            <div class="col-sm-9">
                                <div class="row g-2">
                                    @foreach($request->referenceImages as $refImage)
                                        <div class="col-md-3">
                                            <img src="{{ asset('storage/' . $refImage->image_path) }}" 
                                                 alt="Reference Image {{ $refImage->order + 1 }}" 
                                                 class="img-fluid rounded border"
                                                 style="width: 100%; height: 150px; object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($request->failure_reason)
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>{{ __('custom_videos.failure_reason') }}:</strong></div>
                            <div class="col-sm-9">
                                <div class="alert alert-danger mb-0">{{ $request->failure_reason }}</div>
                            </div>
                        </div>
                    @endif

                    @if($request->segments->isNotEmpty())
                        <div class="row mb-3">
                            <div class="col-12">
                                <h4 class="mt-4 mb-3">{{ __('custom_videos.segments') }}</h4>
                                @foreach($request->segments as $segment)
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">{{ __('custom_videos.segment_number') }} {{ $segment->segment_number }}</h5>
                                            @if($segment->status === 'pending')
                                                <span class="badge bg-warning">{{ __('custom_videos.status_pending') }}</span>
                                            @elseif($segment->status === 'processing')
                                                <span class="badge bg-info">{{ __('custom_videos.status_processing') }}</span>
                                            @elseif($segment->status === 'completed')
                                                <span class="badge bg-success">{{ __('custom_videos.status_completed') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('custom_videos.status_failed') }}</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label"><strong>{{ __('custom_videos.progress') }}:</strong></label>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar @if($segment->progress >= 100) bg-success @endif" 
                                                         role="progressbar" 
                                                         style="width: {{ $segment->progress }}%">
                                                        {{ $segment->progress }}%
                                                    </div>
                                                </div>
                                            </div>

                                            @if($segment->status === 'completed' && $segment->video_url)
                                                <div class="mb-3">
                                                    @php
                                                        // Check if it's an external URL (Drive, etc.)
                                                        $isExternalUrl = filter_var($segment->video_url, FILTER_VALIDATE_URL);
                                                        $videoUrl = $isExternalUrl ? $segment->video_url : asset('storage/' . $segment->video_url);
                                                    @endphp
                                                    
                                                    @if(!$isExternalUrl)
                                                        <video width="100%" style="max-width: 600px;" controls>
                                                            <source src="{{ $videoUrl }}" type="video/mp4">
                                                            {{ __('custom_videos.video_not_supported') }}
                                                        </video>
                                                    @else
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i> {{ __('custom_videos.external_video_info') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="d-flex gap-2 mb-3">
                                                    <a href="{{ $videoUrl }}" 
                                                       class="btn btn-success" 
                                                       target="_blank"
                                                       {{ !$isExternalUrl ? 'download' : '' }}>
                                                        <i class="fas fa-download"></i> {{ __('custom_videos.download') }}
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal{{ $segment->id }}">
                                                        <i class="fas fa-edit"></i> {{ __('custom_videos.Request Edit') }}
                                                    </button>
                                                </div>

                                                <!-- Edit Request Modal -->
                                                <div class="modal fade" id="editModal{{ $segment->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('custom-videos.segments.request-edit', $segment->id) }}">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ __('custom_videos.Request Edit') }} - {{ __('custom_videos.segment_number') }} {{ $segment->segment_number }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="edit_prompt{{ $segment->id }}" class="form-label">{{ __('custom_videos.Edit Prompt') }}</label>
                                                                        <textarea 
                                                                            class="form-control" 
                                                                            id="edit_prompt{{ $segment->id }}" 
                                                                            name="edit_prompt" 
                                                                            rows="5" 
                                                                            required
                                                                            placeholder="{{ __('custom_videos.Enter your edit instructions') }}"
                                                                        ></textarea>
                                                                        <small class="form-text text-muted">
                                                                            {{ __('custom_videos.edit_prompt_help') }}
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('custom_videos.cancel') }}</button>
                                                                    <button type="submit" class="btn btn-primary">{{ __('custom_videos.Submit Edit Request') }}</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($segment->failure_reason)
                                                <div class="alert alert-danger">
                                                    <strong>{{ __('custom_videos.failure_reason') }}:</strong> {{ $segment->failure_reason }}
                                                </div>
                                            @endif

                                            @if($segment->editRequests->isNotEmpty())
                                                <div class="mt-3">
                                                    <h6>{{ __('custom_videos.Edit Requests') }}</h6>
                                                    @foreach($segment->editRequests as $editRequest)
                                                        <div class="alert alert-info">
                                                            <div class="d-flex justify-content-between">
                                                                <strong>{{ __('custom_videos.status') }}:</strong>
                                                                @if($editRequest->status === 'pending')
                                                                    <span class="badge bg-warning">{{ __('custom_videos.status_pending') }}</span>
                                                                @elseif($editRequest->status === 'processing')
                                                                    <span class="badge bg-info">{{ __('custom_videos.status_processing') }}</span>
                                                                @elseif($editRequest->status === 'completed')
                                                                    <span class="badge bg-success">{{ __('custom_videos.status_completed') }}</span>
                                                                @else
                                                                    <span class="badge bg-danger">{{ __('custom_videos.status_rejected') }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="mt-2">
                                                                <strong>{{ __('custom_videos.Edit Prompt') }}:</strong>
                                                                <p class="mb-0">{{ $editRequest->edit_prompt }}</p>
                                                            </div>
                                                            @if($editRequest->admin_note)
                                                                <div class="mt-2">
                                                                    <strong>{{ __('custom_videos.Admin Note') }}:</strong>
                                                                    <p class="mb-0">{{ $editRequest->admin_note }}</p>
                                                                </div>
                                                            @endif
                                                            <small class="text-muted">{{ $editRequest->created_at->format('d.m.Y H:i') }}</small>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('custom_videos.No segments created yet') }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('custom-videos.index') }}" class="btn btn-secondary">
                            {{ __('custom_videos.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
