<div class="dropdown">
    <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ri-more-2-fill"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="{{ route('admin.bookings.show', $booking->id) }}">
                <i class="ri-eye-line me-2"></i>View
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('admin.bookings.edit', $booking->id) }}">
                <i class="ri-edit-line me-2"></i>Edit
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <form action="{{ route('admin.bookings.destroy', $booking->id) }}" 
                  method="POST" 
                  data-delete-form 
                  data-booking-id="{{ $booking->id }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item text-danger">
                    <i class="ri-delete-bin-line me-2"></i>Delete
                </button>
            </form>
        </li>
    </ul>
</div>
