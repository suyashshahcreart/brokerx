@php
    $canEdit = $canEdit ?? auth()->user()->can('user_edit');
    $canDelete = $canDelete ?? auth()->user()->can('user_delete');
@endphp

@if($canEdit || $canDelete)
    <div class="d-flex justify-content-end gap-1">
        @if(true)
            <a href="{{ route('admin.customer.show', $user) }}" class="btn btn-sm btn-soft-secondary" title="Show User">
                <i class="ri-eye-line"></i>
            </a>
        @endif
        @if($canEdit)
            <a href="{{ route('admin.customer.edit', $user) }}" class="btn btn-sm btn-soft-primary" title="Edit User">
                <i class="ri-pencil-line"></i>
            </a>
        @endif
        @if($canDelete)
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-soft-danger btn-delete-user" data-user-name="{{ $user->name }}"
                    data-has-admin-role="{{ $user->hasRole('admin') ? '1' : '0' }}" title="Delete User">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </form>
        @endif
    </div>
@endif