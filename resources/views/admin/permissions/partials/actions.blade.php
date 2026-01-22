<div class="d-flex justify-content-end gap-1">
    @if(!empty($canEdit) && $canEdit)
        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Permission Info">
            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
        </a>
    @endif
    @if(!empty($canDelete) && $canDelete)
        <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button"
                    class="btn btn-sm btn-soft-danger btn-delete-permission"
                    data-permission-name="{{ $permission->name }}"
                    data-roles-count="{{ $permission->roles_count ?? 0 }}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Permission">
                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
            </button>
            <input type="hidden" name="force" value="0">
        </form>
    @endif
</div>

