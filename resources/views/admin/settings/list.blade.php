@extends('admin.layouts.vertical', ['title' => 'Settings', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Settings</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Settings Management</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                @if(!empty($canCreate) && $canCreate)
                    <a href="{{ route('admin.settings.create') }}" class="btn btn-primary" title="Add Setting" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add Setting">
                        <i class="ri-add-line me-1"></i> New Setting
                    </a>
                @endif
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Settings List</h4>
                    <p class="text-muted mb-0">Manage application settings and holidays</p>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="settings-table">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Value</th>
                                <th>Created By</th>
                                <th>Updated By</th>
                                <th>Created At</th>
                                <th>Updated At</th>
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
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $ = window.jQuery;
            if (!$) return;

            const table = $('#settings-table');
            if (!table.length) {
                return;
            }

            const dataTable = table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.settings.index') }}',
                order: [[0, 'asc']],
                columns: [
                    { data: 'name', name: 'name', className: 'fw-semibold' },
                    { data: 'value', name: 'value' },
                    { data: 'created_by_name', name: 'creator.firstname', searchable: true },
                    { data: 'updated_by_name', name: 'updater.firstname', searchable: true },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search settings...'
                },
                lengthMenu: [10, 25, 50, 100],
                responsive: true
            });

            table.on('click', '.btn-delete-setting', function (event) {
                event.preventDefault();

                const button = $(this);
                const form = button.closest('form');
                const settingName = button.data('setting-name');

                const showFinalConfirm = () => {
                    if (typeof Swal === 'undefined') {
                        if (window.confirm(`Are you sure you want to delete the ${settingName} setting?`)) {
                            form.trigger('submit');
                        }
                        return;
                    }

                    Swal.fire({
                        title: 'Delete Setting',
                        text: `Are you sure you want to delete the ${settingName} setting?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'btn btn-danger me-2',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    }).then(result => {
                        if (result.isConfirmed) {
                            form.trigger('submit');
                        }
                    });
                };

                if (typeof Swal === 'undefined') {
                    showFinalConfirm();
                    return;
                }

                Swal.fire({
                    title: 'Delete Confirmation',
                    text: `You are about to delete the ${settingName} setting. Continue?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        showFinalConfirm();
                    }
                });
            });
        });
    </script>
@endsection
