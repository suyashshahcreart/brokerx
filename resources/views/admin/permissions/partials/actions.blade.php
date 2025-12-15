<div class="d-flex justify-content-end gap-1">
    @if(!empty($canEdit) && $canEdit)
        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-soft-primary" title="Edit Permission">
            <i class="ri-pencil-line"></i>
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
                    title="Delete Permission">
                <i class="ri-delete-bin-line"></i>
            </button>
            <input type="hidden" name="force" value="0">
        </form>
    @endif
</div>

