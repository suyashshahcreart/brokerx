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
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Holidays</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Holiday Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary" title="Add Holiday"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add Holiday">
                        <iconify-icon icon="solar:add-circle-broken" class="align-middle fs-18 me-1"></iconify-icon> New Holiday
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
                            <table class="table table-bordered table-hover" id="holidays-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Created By</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $ = window.jQuery;
            if (!$) return;

            const table = $('#holidays-table');
            if (!table.length) {
                return;
            }

            const dataTable = table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.holidays.index') }}',
                order: [[2, 'desc']], // Order by date descending
                columns: [
                    { data: 'id', name: 'id', className: 'fw-semibold' },
                    { data: 'name', name: 'name' },
                    { data: 'date', name: 'date' },
                    { data: 'creator_name', name: 'creator_name', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search holidays...'
                },
                lengthMenu: [10, 25, 50, 100],
                responsive: true,
                drawCallback: function () {
                    // Re-initialize tooltips for dynamically rendered action buttons
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });
        });
    </script>
@endsection
