@extends('admin.layouts.vertical',['title' => 'Permissions', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Permissions Management</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary" title="New Permission" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="New Permission">
                    <i class="ri-add-line me-1"></i> New Permission
                </a>
            </div>
        </div>

        @if(session('permission_delete_warning'))
            @php $warning = session('permission_delete_warning'); @endphp
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex flex-column gap-2">
                    <div>
                        <strong>{{ $warning['permission_name'] }}</strong> is currently assigned to <strong>{{ $warning['role_count'] }}</strong> role(s).
                        Please remove it from those roles before deleting, or confirm deletion to automatically detach it.
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.permissions.destroy', $warning['permission_id']) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="force" value="1">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="ri-delete-bin-line me-1"></i>Delete Anyway
                            </button>
                        </form>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Permissions List</h4>
                    <p class="text-muted mb-0">Manage permissions available throughout the system</p>
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
                    <table class="table table-hover align-middle mb-0" id="permissions-table">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Guard</th>
                                <th>Assigned Roles</th>
                                <th>Created At</th>
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
            const $table = window.jQuery ? window.jQuery('#permissions-table') : null;
            if (!$table || !$table.length) {
                return;
            }

            $table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.permissions.index') }}',
                order: [[1, 'asc']],
                columns: [
                    { data: 'id', name: 'id', className: 'fw-semibold' },
                    { data: 'name', name: 'name' },
                    {
                        data: 'guard_name',
                        name: 'guard_name',
                        render: function (data) {
                            const value = data ?? '';
                            const safe = window.jQuery('<div/>').text(value).html();
                            return `<span class="badge bg-soft-primary text-primary">${safe}</span>`;
                        }
                    },
                    { data: 'roles_count_badge', name: 'roles_count', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search permissions...',
                },
                responsive: true,
                lengthMenu: [10, 25, 50, 100],
                drawCallback: function () {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
                    }
                }
            });

            $table.on('click', '.btn-delete-permission', function (event) {
                event.preventDefault();

                const button = window.jQuery(this);
                const form = button.closest('form');
                const permissionName = button.data('permission-name');
                const rolesCount = parseInt(button.data('roles-count'), 10) || 0;

                const submitForm = (force = false) => {
                    form.find('input[name="force"]').val(force ? 1 : 0);
                    form.trigger('submit');
                };

                const fallbackConfirm = (message, onConfirm) => {
                    if (window.confirm(message)) {
                        onConfirm();
                    }
                };

                const showFinalConfirm = () => {
                    if (typeof Swal === 'undefined') {
                        const message = rolesCount > 0
                            ? `${permissionName} is assigned to ${rolesCount} role(s). Delete it and detach automatically?`
                            : `Delete the ${permissionName} permission?`;
                        fallbackConfirm(message, () => submitForm(rolesCount > 0));
                        return;
                    }

                    if (rolesCount > 0) {
                        Swal.fire({
                            title: 'Assigned to Roles',
                            text: `${rolesCount} role(s) currently use the ${permissionName} permission. Deleting it will detach from those roles.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Delete and Detach',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                confirmButton: 'btn btn-danger me-2',
                                cancelButton: 'btn btn-outline-secondary'
                            },
                            buttonsStyling: false
                        }).then(result => {
                            if (result.isConfirmed) {
                                submitForm(true);
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Delete Permission',
                            text: `Are you sure you want to delete the ${permissionName} permission?`,
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
                                submitForm(false);
                            }
                        });
                    }
                };

                if (typeof Swal === 'undefined') {
                    fallbackConfirm(`You are about to delete the ${permissionName} permission. Continue?`, showFinalConfirm);
                    return;
                }

                Swal.fire({
                    title: 'Delete Confirmation',
                    text: `You are about to delete the ${permissionName} permission. Continue?`,
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