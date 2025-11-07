<div class="d-flex justify-content-end gap-1">
    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-soft-primary" title="Edit Role">
        <i class="ri-pencil-line"></i>
    </a>
    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <input type="hidden" name="force" value="0">
        <button type="button"
                class="btn btn-sm btn-soft-danger btn-delete-role"
                data-role-name="{{ $role->name }}"
                data-users-count="{{ $role->users_count ?? $role->users()->count() }}"
                title="Delete Role">
            <i class="ri-delete-bin-line"></i>
        </button>
    </form>
</div>

