@php
    $canShow = $canShow ?? auth()->user()->can('customer_view');
    $canEdit = $canEdit ?? auth()->user()->can('customer_edit');
    $canDelete = $canDelete ?? auth()->user()->can('customer_delete');
@endphp

@if($canShow || $canEdit || $canDelete)
    <div class="d-flex justify-content-end gap-1">
        @if($canShow)
            <a href="{{ route('admin.customer.show', $customer) }}" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="View Customer Profile">
                <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
            </a>
        @endif
        @if($canEdit)
            <a href="{{ route('admin.customer.edit', $customer) }}" class="btn btn-sm btn-soft-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Customer Details">
                 <iconify-icon icon="solar:pen-new-square-broken" class="align-middle fs-18"></iconify-icon>
            </a>
        @endif
        @if($canDelete)
            <form action="{{ route('admin.customer.destroy', $customer) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-soft-danger btn-delete-user" data-user-name="{{ $customer->name }}"
                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Customer">
                    <iconify-icon icon="solar:trash-bin-minimalistic-broken" class="align-middle fs-18"></iconify-icon>
                </button>
            </form>
        @endif
    </div>
@endif