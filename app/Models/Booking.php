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
     * Get all QR analytics records for this booking
     */
    public function qrAnalytics()
    {
        return $this->hasMany(\App\Models\QRAnalytics::class, 'booking_id');
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
        'base_url',
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


    public function assignees()
    {
        return $this->hasMany(BookingAssignee::class);
    }

    /**
     * Get the photographer visit job for this booking
     */
    public function photographerVisitJob()
    {
        return $this->hasOne(PhotographerVisitJob::class);
    }
    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    /**
     * Get all payment history entries for this booking
     */
    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class)->orderByDesc('created_at');
    }

    /**
     * Get the latest payment history entry
     */
    public function latestPaymentHistory()
    {
        return $this->hasOne(PaymentHistory::class)->latestOfMany();
    }

    /**
     * Get all successful payments
     */
    public function successfulPayments()
    {
        return $this->hasMany(PaymentHistory::class)->where('status', 'completed');
    }

    /**
     * Get total amount paid (sum of all successful payments)
     * Returns amount in paise (smallest currency unit)
     */
    public function getTotalPaidAttribute(): int
    {
        $sum = $this->paymentHistories()
            ->where('status', 'completed')
            ->sum('amount');
        return (int) ($sum ?? 0);
    }

    /**
     * Get total amount paid in rupees
     */
    public function getTotalPaidInRupeesAttribute(): float
    {
        return $this->total_paid / 100;
    }

    /**
     * Get remaining amount to be paid (in paise)
     */
    public function getRemainingAmountAttribute(): int
    {
        $totalAmount = (int) ($this->price ?? 0) * 100; // Convert to paise
        $paidAmount = $this->total_paid;
        $remaining = $totalAmount - $paidAmount;
        return max(0, $remaining);
    }

    /**
     * Get remaining amount in rupees
     */
    public function getRemainingAmountInRupeesAttribute(): float
    {
        return $this->remaining_amount / 100;
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0 && $this->total_paid > 0;
    }

    /**
     * Check if booking has any payments
     */
    public function hasPayments(): bool
    {
        return $this->paymentHistories()->exists();
    }

    /**
     * Check if booking has partial payment
     */
    public function hasPartialPayment(): bool
    {
        return $this->total_paid > 0 && !$this->isFullyPaid();
    }

    /**
     * Update payment status based on payment history
     * This method aggregates payment history and updates the booking's payment_status
     */
    public function updatePaymentStatusFromHistory(): void
    {
        $totalAmount = (int) ($this->price ?? 0) * 100; // Convert to paise
        $paidAmount = $this->total_paid;
        
        // Check if there are any recent successful payments
        $hasSuccessfulPayment = $this->paymentHistories()
            ->where('status', 'completed')
            ->exists();
        
        // Check if there are any pending payments
        $hasPendingPayment = $this->paymentHistories()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
        
        // Determine payment status
        if ($paidAmount >= $totalAmount && $totalAmount > 0) {
            $this->payment_status = 'paid';
            // If booking was not confirmed, mark it as confirmed
            if ($this->status === 'pending' || $this->status === 'inquiry') {
                $this->status = 'confirmed';
            }
        } elseif ($paidAmount > 0) {
            // Partial payment
            $this->payment_status = 'pending';
        } elseif ($hasPendingPayment) {
            $this->payment_status = 'pending';
        } else {
            // Check if all payments failed
            $allFailed = $this->paymentHistories()
                ->whereIn('status', ['failed', 'cancelled'])
                ->count() > 0 
                && !$hasSuccessfulPayment
                && !$hasPendingPayment;
            
            if ($allFailed) {
                $this->payment_status = 'failed';
            } else {
                $this->payment_status = 'unpaid';
            }
        }
        
        $this->save();
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

    /**
     * Get FTP URL for tour if status is tour_live
     * Returns the full FTP URL without index.php suffix
     * 
     * @return string FTP URL or '#' if not available
     */
    public function getTourLiveUrl(): string {
        // Only generate URL if status is tour_live
        if ($this->status !== 'tour_live') {
            return '#';
        }

        // Get the latest tour for this booking
        $tour = $this->tours()
            ->orderBy('created_at', 'desc')
            ->first();

        // Check if tour exists and has required data
        if (!$tour || !$tour->location || !$tour->slug || !$this->user_id) {
            return '#';
        }   

        // Get FTP configuration based on tour location
        $ftpConfig = \App\Models\FtpConfiguration::where('category_name', $tour->location)->first();
        
        if (!$ftpConfig) {
            return '#';
        }

        // Generate FTP URL
        $fullFtpUrl = $ftpConfig->getUrlForTour($tour->slug, $this->user_id);
        $tourFtpUrl = rtrim($fullFtpUrl, '/');
        
        // Remove /index.php if present
        if (substr($tourFtpUrl, -10) === '/index.php') {
            $tourFtpUrl = substr($tourFtpUrl, 0, -10);
        }

        return $tourFtpUrl;
    }
}
