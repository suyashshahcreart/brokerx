@extends('admin.layouts.vertical', ['title' => 'Holidays', 'subTitle' => 'System'])

@section('css')
    <style>
        #holidayContainer {
            min-height: 100px;
            max-height: 400px;
            overflow-y: auto;
        }

        .holiday-card {
            transition: all 0.2s ease;
        }

        .holiday-card:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .holiday-card .btn-danger {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .holiday-card:hover .btn-danger {
            opacity: 1;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Holidays</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Holiday Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary" title="Add Holiday"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add Holiday">
                        <i class="ri-add-line me-1"></i> New Holiday
                    </a>
                </div>
            </div>
            <div class="col-12">
                <div class="card panel-card border-primary border-top" data-panel-card>
                    <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h4 class="card-title mb-1">Holiday List</h4>
                            <p class="text-muted mb-0">Manage holidays for the system</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($holidays as $holiday)
                                        <tr>
                                            <td>{{ $holiday->name }}</td>
                                            <td>{{ $holiday->date }}</td>
                                            <td>{{ $holiday->creator?->name ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No holidays found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $holidays->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
