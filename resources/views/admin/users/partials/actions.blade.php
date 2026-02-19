@php
    $canEdit = $canEdit ?? auth()->user()->can('user_edit');
    $canDelete = $canDelete ?? auth()->user()->can('user_delete');
@endphp

@if($canEdit || $canDelete)
    <div class="d-flex justify-content-end gap-1">
        @if(false)
            <a href="{{ route('admin.customer.show', $user) }}" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View User Profile">
                <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
            </a>
        @endif
        @if($canEdit)
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User Details">
                <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
            </a>
        @endif
        @if($canDelete)
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-soft-danger btn-delete-user" data-user-name="{{ $user->name }}"
                    data-has-admin-role="{{ $user->hasRole('admin') ? '1' : '0' }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete User">
                    <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                </button>
            </form>
        @endif
    </div>
@endif