@extends('admin.layouts.vertical', ['title' => 'Portfolio Details', 'subTitle' => 'Management'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.portfolios.index') }}">Portfolios</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $portfolio->id }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Portfolio #{{ $portfolio->id }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.portfolios.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.portfolios.edit', $portfolio) }}" class="btn btn-primary"><i class="ri-edit-line me-1"></i> Edit</a>
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Portfolio Details</h4>
                    <p class="text-muted mb-0">View portfolio information</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h5 class="mb-3">Basic Information</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">ID</dt>
                            <dd class="col-sm-8">#{{ $portfolio->id }}</dd>

                            <dt class="col-sm-4">Title</dt>
                            <dd class="col-sm-8">{{ $portfolio->title }}</dd>

                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8">{{ $portfolio->description ?? '-' }}</dd>

                            @if($portfolio->link)
                                <dt class="col-sm-4">Link</dt>
                                <dd class="col-sm-8"><a href="{{ $portfolio->link }}" target="_blank" rel="noopener">{{ $portfolio->link }}</a></dd>
                            @endif

                            @if($portfolio->photo)
                                <dt class="col-sm-4">Photo</dt>
                                <dd class="col-sm-8"><img src="{{ asset('storage/'.$portfolio->photo) }}" alt="Portfolio Photo" class="img-thumbnail" style="max-width: 200px;"></dd>
                            @endif

                            @if($portfolio->booking)
                                <dt class="col-sm-4">Related Booking</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ route('admin.bookings.show', $portfolio->booking) }}" class="badge bg-primary text-decoration-none">
                                        Booking #{{ $portfolio->booking->id }}
                                    </a>
                                </dd>
                            @endif
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="mb-3">Metadata</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Created By</dt>
                            <dd class="col-sm-8">{{ $portfolio->creator?->firstname }} {{ $portfolio->creator?->lastname }}</dd>

                            <dt class="col-sm-4">Updated By</dt>
                            <dd class="col-sm-8">{{ $portfolio->updater?->firstname }} {{ $portfolio->updater?->lastname }}</dd>

                            <dt class="col-sm-4">Created At</dt>
                            <dd class="col-sm-8">{{ $portfolio->created_at->format('Y-m-d H:i:s') }}</dd>

                            <dt class="col-sm-4">Updated At</dt>
                            <dd class="col-sm-8">{{ $portfolio->updated_at->format('Y-m-d H:i:s') }}</dd>

                            @if($portfolio->deleted_at)
                            <dt class="col-sm-4">Deleted At</dt>
                            <dd class="col-sm-8">{{ $portfolio->deleted_at->format('Y-m-d H:i:s') }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Actions</h4>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.portfolios.edit', $portfolio) }}" class="btn btn-primary">
                        <i class="ri-edit-line me-1"></i> Edit Portfolio
                    </a>
                    <form action="{{ route('admin.portfolios.destroy', $portfolio) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this portfolio?')">
                            <i class="ri-delete-bin-line me-1"></i> Delete Portfolio
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
