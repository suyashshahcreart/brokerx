@extends('frontend.layouts.vertical', ['title' => 'My Portfolios', 'subTitle' => 'Portfolio'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h3 class="mb-0">My Portfolios</h3>
                <p class="text-muted mb-0">Manage your portfolio items</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('portfolios.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> New Portfolio
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            @forelse($portfolios as $portfolio)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $portfolio->title }}</h5>
                            <p class="card-text text-muted">
                                {{ Str::limit($portfolio->description, 100) ?? 'No description' }}
                            </p>
                            @if($portfolio->link)
                                <div class="mb-2">
                                    <a href="{{ $portfolio->link }}" target="_blank" rel="noopener" class="badge bg-info text-decoration-none">
                                        <i class="ri-link"></i> Link
                                    </a>
                                </div>
                            @endif
                            @if($portfolio->photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$portfolio->photo) }}" alt="Portfolio Photo" class="img-thumbnail" style="max-width: 80px;">
                                </div>
                            @endif
                            @if($portfolio->booking)
                                <div class="mb-2">
                                    <span class="badge bg-primary">
                                        <i class="ri-bookmark-line"></i> Booking #{{ $portfolio->booking_id }}
                                    </span>
                                </div>
                            @endif
                            <div class="text-muted small mb-3">
                                <i class="ri-time-line"></i> {{ $portfolio->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('portfolios.show', $portfolio) }}" class="btn btn-sm btn-light">
                                    <i class="ri-eye-line"></i> View
                                </a>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('portfolios.edit', $portfolio) }}" class="btn btn-soft-primary">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <form action="{{ route('portfolios.destroy', $portfolio) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger" onclick="return confirm('Delete this portfolio?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-folder-open-line" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h4 class="mt-3">No portfolios yet</h4>
                            <p class="text-muted">Start by creating your first portfolio item</p>
                            <a href="{{ route('portfolios.create') }}" class="btn btn-primary mt-2">
                                <i class="ri-add-line me-1"></i> Create Portfolio
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($portfolios->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $portfolios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
