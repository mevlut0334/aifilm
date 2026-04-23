@extends('web.layouts.app')

@section('title', __('requests.Request Detail'))

@section('content')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">{{ __('requests.Request Detail') }}</h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('requests.Status') }}:</strong></div>
                        <div class="col-sm-8">
                            @if($request->status === 'pending')
                                <span class="badge bg-warning">{{ __('requests.Pending') }}</span>
                            @elseif($request->status === 'processing')
                                <span class="badge bg-info">{{ __('requests.Processing') }}</span>
                            @elseif($request->status === 'completed')
                                <span class="badge bg-success">{{ __('requests.Completed') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('requests.Failed') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('requests.Progress') }}:</strong></div>
                        <div class="col-sm-8">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $request->progress }}%;"
                                     aria-valuenow="{{ $request->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $request->progress }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('requests.Type') }}:</strong></div>
                        <div class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $request->type)) }}</div>
                    </div>

                    @if($request->template)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('requests.Template') }}:</strong></div>
                            <div class="col-sm-8">{{ $request->template->title }}</div>
                        </div>
                    @endif

                    @if($request->orientation)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('requests.Orientation') }}:</strong></div>
                            <div class="col-sm-8">{{ ucfirst($request->orientation) }}</div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('requests.Token Cost') }}:</strong></div>
                        <div class="col-sm-8">{{ $request->token_cost }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>{{ __('requests.Created At') }}:</strong></div>
                        <div class="col-sm-8">{{ $request->created_at->format('d.m.Y H:i:s') }}</div>
                    </div>

                    @if($request->input_image_path)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('requests.Uploaded Image') }}:</strong></div>
                            <div class="col-sm-8">
                                <img src="{{ asset('storage/' . $request->input_image_path) }}" 
                                     alt="Input Image" 
                                     class="img-fluid"
                                     style="max-width: 300px;">
                            </div>
                        </div>
                    @endif

                    @if($request->output_url)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('requests.Result') }}:</strong></div>
                            <div class="col-sm-8">
                                <a href="{{ $request->output_url }}" 
                                   class="btn btn-success" 
                                   target="_blank" 
                                   download>
                                    <i class="bi bi-download"></i> {{ __('requests.Download') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($request->failure_reason)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('requests.Failure Reason') }}:</strong></div>
                            <div class="col-sm-8">
                                <div class="alert alert-danger">{{ $request->failure_reason }}</div>
                            </div>
                        </div>
                    @endif

                    @if($request->description)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>{{ __('requests.Description') }}:</strong></div>
                            <div class="col-sm-8">{{ $request->description }}</div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('generation-requests.index') }}" class="btn btn-secondary">
                            {{ __('requests.Back') }}
                        </a>

                        @if(in_array($request->status, ['pending', 'failed']))
                            <form method="POST" 
                                  action="{{ route('generation-requests.destroy', $request->uuid) }}" 
                                  class="d-inline"
                                  onsubmit="return confirm('{{ __('requests.Are you sure you want to cancel this request? Your tokens will be refunded.') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    {{ __('requests.Cancel Request') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
