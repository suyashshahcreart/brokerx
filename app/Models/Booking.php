<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    /**
     * Get the QR code assigned to this booking (if any)
     */
    public function qr()
    {
        return $this->hasOne(\App\Models\QR::class, 'booking_id');
    }

    /**
     * Get the booking history entries
     */
    public function histories()
    {
        return $this->hasMany(BookingHistory::class)->orderByDesc('created_at');
    }

    /**
     * Get the latest booking history entry
     */
    public function latestHistory()
    {
        return $this->hasOne(BookingHistory::class)->latestOfMany();
    }
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_type_id',
        'property_sub_type_id',
        'owner_type',
        'bhk_id',
        'city_id',
        'state_id',
        'furniture_type',
        'other_option_details',
        'firm_name',
        'gst_no',
        'tour_final_link',
        'tour_code',
        'area',
        'price',
        'house_no',
        'building',
        'society_name',
        'address_area',
        'landmark',
        'full_address',
        'pin_code',
        'booking_date',
        'booking_time',
        'booking_notes',
        'payment_status',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'cashfree_order_id',
        'cashfree_payment_session_id',
        'cashfree_payment_status',
        'cashfree_payment_method',
        'cashfree_payment_amount',
        'cashfree_payment_currency',
        'cashfree_reference_id',
        'cashfree_payment_at',
        'cashfree_payment_message',
        'cashfree_payment_meta',
        'cashfree_last_response',
        'json_data',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'cashfree_payment_at' => 'datetime',
        'cashfree_payment_meta' => 'array',
        'cashfree_last_response' => 'array',
        'json_data' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function propertySubType()
    {
        return $this->belongsTo(PropertySubType::class);
    }

    public function bhk()
    {
        return $this->belongsTo(BHK::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Check if booking has complete property data
     * Based on validation logic from setup page
     */
    public function hasCompletePropertyData(): bool
    {
        // Owner Type is required
        if (empty($this->owner_type)) {
            return false;
        }

        // Property Type is required
        if (empty($this->property_type_id)) {
            return false;
        }

        // Get property type name to determine which validations apply
        $propertyTypeName = $this->propertyType?->name ?? '';
        $isResidential = stripos($propertyTypeName, 'residential') !== false;
        $isCommercial = stripos($propertyTypeName, 'commercial') !== false;
        $isOther = !$isResidential && !$isCommercial;

        // Residential validations
        if ($isResidential) {
            // Property Sub Type is required
            if (empty($this->property_sub_type_id)) {
                return false;
            }
            // Furnish Type is required
            if (empty($this->furniture_type)) {
                return false;
            }
            // Size (BHK/RK) is required
            if (empty($this->bhk_id)) {
                return false;
            }
            // Area is required and must be greater than 0
            if (empty($this->area) || floatval($this->area) <= 0) {
                return false;
            }
        }

        // Commercial validations
        if ($isCommercial) {
            // Property Sub Type is required
            if (empty($this->property_sub_type_id)) {
                return false;
            }
            // Furnish Type is required
            if (empty($this->furniture_type)) {
                return false;
            }
            // Area is required and must be greater than 0
            if (empty($this->area) || floatval($this->area) <= 0) {
                return false;
            }
        }

        // Other validations
        if ($isOther) {
            // For "Other" property type, we need either:
            // 1. Property Sub Type selected (if available), OR
            // 2. Other option details filled
            // Area is required and must be greater than 0
            if (empty($this->area) || floatval($this->area) <= 0) {
                return false;
            }
            // Check if either property_sub_type_id or other_option_details is filled
            if (empty($this->property_sub_type_id) && empty($this->other_option_details)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if booking has complete address data
     * Based on validation logic from setup page
     */
    public function hasCompleteAddressData(): bool
    {
        // House / Office No. is required
        if (empty($this->house_no)) {
            return false;
        }

        // Society / Building Name is required
        if (empty($this->building)) {
            return false;
        }

        // Pincode is required and must be 6 digits
        if (empty($this->pin_code) || !preg_match('/^[0-9]{6}$/', $this->pin_code)) {
            return false;
        }

        // Full address is required
        if (empty($this->full_address)) {
            return false;
        }

        return true;
    }

    /**
     * Check if booking is ready for payment
     * Both property and address data must be complete
     */
    public function isReadyForPayment(): bool
    {
        return $this->hasCompletePropertyData() && $this->hasCompleteAddressData();
    }

    /**
     * Change booking status and log to history
     * 
     * @param string $newStatus The new status to set
     * @param int|null $userId The user making the change
     * @param string|null $notes Optional notes about the change
     * @param array|null $metadata Optional metadata
     * @return bool
     */
    public function changeStatus(string $newStatus, ?int $userId = null, ?string $notes = null, ?array $metadata = null): bool
    {
        $oldStatus = $this->status;
        
        // Don't create history if status hasn't changed
        if ($oldStatus === $newStatus) {
            return true;
        }

        // Update the booking status
        $this->status = $newStatus;
        $this->save();

        // Create history entry
        BookingHistory::create([
            'booking_id' => $this->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => $userId ?? auth()->id(),
            'notes' => $notes,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return true;
    }

    /**
     * Get all available booking statuses
     * 
     * @return array
     */
    public static function getAvailableStatuses(): array
    {
        return [
            'inquiry',
            'pending',
            'schedul_pending',
            'schedul_accepted',
            'schedul_decline',
            'reschedul_pending',
            'reschedul_accepted',
            'reschedul_decline',
            'reschedul_blocked',
            'schedul_assign',
            'schedul_completed',
            'tour_pending',
            'tour_completed',
            'tour_live',
            'maintenance',
            'expired',
        ];
    }

    /**
     * Get status label for display
     * 
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'inquiry' => 'Inquiry',
            'pending' => 'Pending',
            'schedul_pending' => 'Schedule Pending',
            'schedul_accepted' => 'Schedule Accepted',
            'schedul_decline' => 'Schedule Declined',
            'reschedul_pending' => 'Reschedule Pending',
            'reschedul_accepted' => 'Reschedule Accepted',
            'reschedul_decline' => 'Reschedule Declined',
            'reschedul_blocked' => 'Reschedule Blocked',
            'schedul_assign' => 'Schedule Assigned',
            'schedul_completed' => 'Schedule Completed',
            'tour_pending' => 'Tour Pending',
            'tour_completed' => 'Tour Completed',
            'tour_live' => 'Tour Live',
            'maintenance' => 'Maintenance',
            'expired' => 'Expired',
            default => ucfirst($this->status),
        };
    }
}
