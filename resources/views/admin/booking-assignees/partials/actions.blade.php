<div class="dropdown">
    <button class="btn btn-sm btn-soft-secondary dropdown-toggle"
        type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ri-more-2-fill"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @can('booking_assignee_view')
            <li>
                <a class="dropdown-item"
                    href="{{ route('admin.booking-assignees.show', $assignee->id) }}">
                    <i class="ri-eye-line me-2"></i> View
                </a>
            </li>
        @endcan
        @can('booking_assignee_edit')
            <li>
                <a class="dropdown-item"
                    href="{{ route('admin.booking-assignees.edit', $assignee->id) }}">
                    <i class="ri-edit-line me-2"></i> Edit
                </a>
            </li>
        @endcan
        <li>
            <hr class="dropdown-divider">
        </li>
        @can('booking_assignee_delete')
            <li>
                <form method="POST"
                    action="{{ route('admin.booking-assignees.destroy', $assignee->id) }}"
                    data-delete-form
                    data-assignee-id="{{ $assignee->id }}"
                    style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="ri-delete-bin-line me-2"></i> Delete
                    </button>
                </form>
            </li>
        @endcan
    </ul>
</div>
