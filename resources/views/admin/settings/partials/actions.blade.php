<div class="d-flex justify-content-end gap-1">
    @if(!empty($canEdit) && $canEdit)
        <a href="{{ route('admin.settings.edit', $setting) }}" class="btn btn-sm btn-soft-primary" title="Edit Setting">
            <i class="ri-pencil-line"></i>
        </a>
    @endif
    @if(!empty($canDelete) && $canDelete)
        <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button"
                    class="btn btn-sm btn-soft-danger btn-delete-setting"
                    data-setting-name="{{ $setting->name }}"
                    title="Delete Setting">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    @endif
</div>
