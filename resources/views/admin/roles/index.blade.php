@extends('admin.layouts.vertical', ['title' => 'Roles', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('role_delete_warning'))
            @php $warning = session('role_delete_warning'); @endphp
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex flex-column gap-2">
                    <div>
                        <strong>{{ $warning['role_name'] }}</strong> is currently assigned to <strong>{{ $warning['user_count'] }}</strong> user(s).
                        Please reassign or remove the role from those users before deleting, or proceed to delete and automatically reassign those users to the <strong>broker</strong> role.
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.roles.destroy', $warning['role_id']) }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="force" value="1">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="ri-delete-bin-line me-1"></i>Delete Anyway
                            </button>
                        </form>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                            Cancel
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Roles</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Roles Management</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary" title="Add Role" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add Role">
                    <i class="ri-shield-user-line me-1"></i> New Role
                </a>
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Roles List</h4>
                    <p class="text-muted mb-0">Review roles, assigned permissions, and user counts</p>
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
                    <table class="table table-hover align-middle mb-0" id="roles-table">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Permissions</th>
                                <th>Users</th>
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

            const table = $('#roles-table');
            if (!table.length) {
                return;
            }

            const dataTable = table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.roles.index') }}',
                order: [[0, 'asc']],
                columns: [
                    { data: 'name', name: 'name', className: 'fw-semibold' },
                    { data: 'permissions', name: 'permissions', orderable: false, searchable: false },
                    {
                        data: 'users_count',
                        name: 'users_count',
                        render: function (data) {
                            return `<span class="badge bg-soft-secondary text-secondary">${data ?? 0}</span>`;
                        }
                    },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search roles...'
                },
                lengthMenu: [10, 25, 50, 100],
                responsive: true
            });

            table.on('click', '.btn-delete-role', function (event) {
                event.preventDefault();

                const button = $(this);
                const form = button.closest('form');
                const roleName = button.data('role-name');
                const usersCount = parseInt(button.data('users-count'), 10) || 0;

                const protectedRoles = ['admin', 'broker'];
                if (protectedRoles.includes(String(roleName).toLowerCase())) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Protected Role',
                            text: `${roleName} role cannot be deleted.`,
                            confirmButtonText: 'OK',
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        });
                    } else {
                        alert(`${roleName} role cannot be deleted.`);
                    }
                    return;
                }

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
                        const message = usersCount > 0
                            ? `${roleName} is assigned to ${usersCount} user(s). Delete it and reassign them to the broker role?`
                            : `Delete the ${roleName} role?`;
                        fallbackConfirm(message, () => submitForm(usersCount > 0));
                        return;
                    }

                    if (usersCount > 0) {
                        Swal.fire({
                            title: 'Users Assigned',
                            text: `${usersCount} user(s) currently have the ${roleName} role. Deleting it will move them to the broker role.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Delete and Reassign',
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
                            title: 'Delete Role',
                            text: `Are you sure you want to delete the ${roleName} role?`,
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
                    fallbackConfirm(`You are about to delete the ${roleName} role. Continue?`, showFinalConfirm);
                    return;
                }

                Swal.fire({
                    title: 'Delete Confirmation',
                    text: `You are about to delete the ${roleName} role. Continue?`,
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


