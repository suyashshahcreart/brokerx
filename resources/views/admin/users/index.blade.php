@extends('admin.layouts.vertical', ['title' => 'Users', 'subTitle' => 'System'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Users</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Users Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @can('user_create')
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary" title="Add User"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add User">
                            <i class="ri-user-add-line me-1"></i> New User
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Users List</h4>
                        <p class="text-muted mb-0">Manage system users and their assigned roles</p>
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
                        <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="users-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Full Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Roles</th>
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

            const table = $('#users-table');
            if (!table.length) {
                return;
            }

            const canEdit = @json($canEdit);
            const canDelete = @json($canDelete);
            const canManageActions = Boolean(canEdit || canDelete);

            const dataTable = table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.users.index') }}',
                order: [[0, 'asc']],
                columns: [
                    { data: 'name', name: 'name', className: 'fw-semibold' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'email', name: 'email' },
                    { data: 'roles_badges', name: 'roles.name', orderable: false, searchable: false },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        visible: canManageActions
                    },
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search users...'
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

            if (!canManageActions) {
                return;
            }

            table.on('click', '.btn-delete-user', function (event) {
                event.preventDefault();

                const button = $(this);
                const form = button.closest('form');
                const userName = button.data('user-name');
                const hasAdminRole = String(button.data('has-admin-role')) === '1';

                const submitForm = () => {
                    form.trigger('submit');
                };

                const fallbackConfirm = (message, onConfirm) => {
                    if (window.confirm(message)) {
                        onConfirm();
                    }
                };

                const showFinalConfirm = () => {
                    if (typeof Swal === 'undefined') {
                        const message = hasAdminRole
                            ? `${userName} has the admin role. Deleting may fail if they are the last admin. Proceed with deletion?`
                            : `Delete ${userName}?`;
                        fallbackConfirm(message, submitForm);
                        return;
                    }

                    const title = hasAdminRole ? 'Admin User' : 'Delete User';
                    const text = hasAdminRole
                        ? `${userName} currently has the admin role. Deleting will be blocked if they are the last admin.`
                        : `Are you sure you want to delete ${userName}?`;

                    Swal.fire({
                        title,
                        text,
                        icon: hasAdminRole ? 'warning' : 'question',
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
                            submitForm();
                        }
                    });
                };

                if (typeof Swal === 'undefined') {
                    fallbackConfirm(`You are about to delete ${userName}. Continue?`, showFinalConfirm);
                    return;
                }

                Swal.fire({
                    title: 'Delete Confirmation',
                    text: `You are about to delete ${userName}. Continue?`,
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