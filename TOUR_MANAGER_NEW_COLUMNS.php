<?php
// New columns to add to tour manager after booking_info column:

// 1. Add booking_id column (after booking_info)
->addColumn('booking_id', function (Booking $booking) {
    return '<strong>#' . $booking->id . '</strong>';
})

// 2. Add city_state column (before location or replace location)
->addColumn('city_state', function (Booking $booking) {
    return ($booking->city?->name ?? '-') . '<div class="text-muted small">' . ($booking->state?->name ?? '-') . '</div>';
})

// 3. Add created_at date column (after qr_code, before booking_date)
->addColumn('created_at', function (Booking $booking) {
    return \Carbon\Carbon::parse($booking->created_at)->format('d M Y') . '<br>' .
        '<small class="text-muted">' . \Carbon\Carbon::parse($booking->created_at)->format('h:i A') . '</small>';
})

// Update rawColumns to include new columns:
->rawColumns(['booking_id', 'booking_info', 'customer', 'city_state', 'qr_code', 'created_at', 'booking_date', 'status', 'payment_status', 'actions'])
