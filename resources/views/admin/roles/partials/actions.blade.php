<div class="d-flex justify-content-end gap-1">
    @if(!empty($canEdit) && $canEdit)
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Role Info">
            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
        </a>
    @endif
    @if(!empty($canDelete) && $canDelete)
        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <input type="hidden" name="force" value="0">
            <button type="button"
                    class="btn btn-sm btn-soft-danger btn-delete-role"
                    data-role-name="{{ $role->name }}"
                    data-users-count="{{ $role->users_count ?? $role->users()->count() }}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Role">
                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
            </button>
        </form>
    @endif
</div>

