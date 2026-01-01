@php
    $canShow = $canShow ?? auth()->user()->can('customer_view');
    $canEdit = $canEdit ?? auth()->user()->can('customer_edit');
    $canDelete = $canDelete ?? auth()->user()->can('customer_delete');
@endphp

@if($canShow || $canEdit || $canDelete)
    <div class="d-flex justify-content-end gap-1">
        @if($canShow)
            <a href="{{ route('admin.customer.show', $user) }}" class="btn btn-sm btn-soft-secondary" title="View Customer">
                <i class="ri-eye-line"></i>
            </a>
        @endif
        @if($canEdit)
            <a href="{{ route('admin.customer.edit', $user) }}" class="btn btn-sm btn-soft-primary" title="Edit Customer">
                <i class="ri-pencil-line"></i>
            </a>
        @endif
        @if($canDelete)
            <form action="{{ route('admin.customer.destroy', $user) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-soft-danger btn-delete-user" data-user-name="{{ $user->name }}"
                    data-has-admin-role="{{ $user->hasRole('admin') ? '1' : '0' }}" title="Delete Customer">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </form>
        @endif
    </div>
@endif