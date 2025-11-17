@extends('admin.layouts.vertical', ['title' => 'Portfolios', 'subTitle' => 'Management'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Management</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Portfolios</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Portfolios</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.portfolios.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> New Portfolio
                </a>
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Portfolios List</h4>
                    <p class="text-muted mb-0">Manage portfolio items</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                        <i class="ri-refresh-line"></i>
                    </button>
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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Booking</th>
                                <th>Link</th>
                                <th>Photo</th>
                                <th>Created By</th>
                                <th>Updated By</th>
                                <th>Created At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($portfolios as $portfolio)
                            <tr>
                                <td>#{{ $portfolio->id }}</td>
                                <td>{{ $portfolio->title }}</td>
                                <td>{{ Str::limit($portfolio->description, 50) ?? '-' }}</td>
                                <td>
                                    @if($portfolio->booking)
                                        <a href="{{ route('admin.bookings.show', $portfolio->booking) }}" class="badge bg-primary text-decoration-none">
                                            #{{ $portfolio->booking->id }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($portfolio->link)
                                        <a href="{{ $portfolio->link }}" target="_blank" rel="noopener">Link</a>
                                    @endif
                                </td>
                                <td>
                                    @if($portfolio->photo)
                                        <img src="{{ asset('storage/'.$portfolio->photo) }}" alt="Portfolio Photo" class="img-thumbnail" style="max-width: 60px;">
                                    @endif
                                </td>
                                <td>{{ $portfolio->creator?->firstname }} {{ $portfolio->creator?->lastname }}</td>
                                <td>{{ $portfolio->updater?->firstname }} {{ $portfolio->updater?->lastname }}</td>
                                <td>{{ $portfolio->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.portfolios.show', $portfolio) }}" class="btn btn-light btn-sm border" title="View"><i class="ri-eye-line"></i></a>
                                    <a href="{{ route('admin.portfolios.edit', $portfolio) }}" class="btn btn-soft-primary btn-sm" title="Edit"><i class="ri-edit-line"></i></a>
                                    <form action="{{ route('admin.portfolios.destroy', $portfolio) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('Delete this portfolio?')" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No portfolios found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $portfolios->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
