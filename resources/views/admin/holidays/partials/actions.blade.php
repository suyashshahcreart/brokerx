<div class="d-flex justify-content-end gap-1">
    <a href="{{ route('admin.holidays.edit', $holiday) }}" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Holiday Info">
        <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
    </a>
    <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-soft-danger btn-delete-holiday" onclick="return confirm('Are you sure you want to delete this holiday?')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Holiday">
            <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
        </button>
    </form>
</div>
