@extends('frontend.layouts.vertical', ['title' => 'Portfolio Details', 'subTitle' => 'Portfolio'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <h3 class="mb-0">Portfolio #{{ $portfolio->id }}</h3>
                <p class="text-muted mb-0">View portfolio information</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portfolios.index') }}" class="btn btn-soft-secondary">
                    <i class="ri-arrow-go-back-line me-1"></i> Back
                </a>
                <a href="{{ route('portfolios.edit', $portfolio) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i> Edit
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ $portfolio->title }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5 class="text-muted mb-2">Description</h5>
                            <p class="mb-0">{{ $portfolio->description ?? 'No description provided.' }}</p>
                        </div>

                        @if($portfolio->link)
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Link</h5>
                                <a href="{{ $portfolio->link }}" target="_blank" rel="noopener" class="d-block text-primary">{{ $portfolio->link }}</a>
                            </div>
                        @endif

                        @if($portfolio->photo)
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Photo</h5>
                                <img src="{{ asset('storage/'.$portfolio->photo) }}" alt="Portfolio Photo" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        @endif

                        @if($portfolio->booking)
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Related Booking</h5>
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="ri-bookmark-line me-2" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <strong>Booking #{{ $portfolio->booking->id }}</strong><br>
                                        <small>Date: {{ optional($portfolio->booking->booking_date)->format('F d, Y') ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <h5 class="text-muted mb-2">Metadata</h5>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Created By</dt>
                                <dd class="col-sm-8">{{ $portfolio->creator?->firstname }} {{ $portfolio->creator?->lastname }}</dd>

                                <dt class="col-sm-4">Created At</dt>
                                <dd class="col-sm-8">{{ $portfolio->created_at->format('F d, Y H:i:s') }}</dd>

                                <dt class="col-sm-4">Updated At</dt>
                                <dd class="col-sm-8">{{ $portfolio->updated_at->format('F d, Y H:i:s') }}</dd>

                                @if($portfolio->updater)
                                <dt class="col-sm-4">Updated By</dt>
                                <dd class="col-sm-8">{{ $portfolio->updater?->firstname }} {{ $portfolio->updater?->lastname }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('portfolios.edit', $portfolio) }}" class="btn btn-primary">
                                <i class="ri-edit-line me-1"></i> Edit Portfolio
                            </a>
                            <form action="{{ route('portfolios.destroy', $portfolio) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this portfolio?')">
                                    <i class="ri-delete-bin-line me-1"></i> Delete Portfolio
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Quick Info</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="ri-calendar-line text-primary me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Created</small>
                                <strong>{{ $portfolio->created_at->diffForHumans() }}</strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ri-refresh-line text-success me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Updated</small>
                                <strong>{{ $portfolio->updated_at->diffForHumans() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
