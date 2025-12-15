@extends('admin.layouts.vertical', ['title' => 'Photographer Visits', 'subTitle' => 'Management'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Management</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Photographer Visits</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Photographer Visits</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @if($canCreate)
                    <a href="{{ route('admin.photographer-visits.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> New Visit
                    </a>
                    @endif
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Photographer Visits List</h4>
                        <p class="text-muted mb-0">Track photographer site visits and check-ins</p>
                    </div>
                    <div class="panel-actions d-flex gap-2">
                        <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                            <i class="ri-refresh-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                            <i class="ri-arrow-up-s-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="fullscreen"
                            title="Fullscreen">
                            <i class="ri-fullscreen-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3 mb-2">
                            <select id="filter-status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="checked_in">Checked In</option>
                                <option value="checked_out">Checked Out</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select id="filter-photographer" class="form-select">
                                <option value="">All Photographers</option>
                                @foreach($photographers as $photographer)
                                    <option value="{{ $photographer->id }}">{{ $photographer->firstname }} {{ $photographer->lastname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="date" id="filter-date-from" class="form-control" placeholder="From Date">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="date" id="filter-date-to" class="form-control" placeholder="To Date">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="visits-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Photographer</th>
                                    <th>Booking</th>
                                    <th>Visit Date</th>
                                    <th width="120">Status</th>
                                    <th>Check Actions</th>
                                    <th width="100" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
@vite(['resources/js/pages/photographer-visits-index.js'])
<script>
    // Configuration for the photographer visits page
    window.photographerVisitsConfig = {
        indexRoute: '{{ route('admin.photographer-visits.index') }}'
    };
</script>
@endsection