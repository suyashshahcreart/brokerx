<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tour extends Model
{
    /** @use HasFactory<\Database\Factories\TourFactory> */
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'name',
        'title',
        'slug',
        'description',
        'content',
        'featured_image',
        'tour_thumbnail',
        'price',
        'duration_days',
        'location',
        'start_date',
        'end_date',
        'max_participants',
        'status',
        'final_json',
        'sidebar_links',
        'working_json',
        'working_json_last_update_user',
        'revision',
        // SEO Meta Fields
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'og_title',
        'og_description',
        'canonical_url',
        'meta_robots',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'structured_data_type',
        'structured_data',
        'header_code',
        'footer_code',
        // New fields added by migration
        'sidebar_logo',
        'sidebar_node',
        'footer_logo',
        'footer_title',
        'footer_email',
        'footer_mobile',
        'custom_type',
        'footer_decription',
        // Sidebar and Footer fields
        'company_address',
        'sidebar_footer_link',
        'sidebar_footer_text',
        'sidebar_footer_link_show',
        'footer_info_type',
        'footer_brand_logo',
        'footer_brand_text',
        'footer_brand_mobile',
        'gtm_tag',
        'footer_subtitle',
        'is_active',
        'is_credentials',
        'is_mobile_validation',
        'is_hosted',
        'hosted_link',
        // Contact fields
        'contact_user_name',
        'contact_google_location',
        'contact_website',
        'contact_email',
        'contact_phone_no',
        'contact_whatsapp_no',
        // Contact show/hide toggles
        'show_contact_user_name',
        'show_contact_google_location',
        'show_contact_email',
        'show_contact_website',
        'show_contact_phone_no',
        'show_contact_whatsapp_no',
        // Language fields
        'enable_language',
        'default_language',
        // Customization fields
        'overlay_bg_color',
        'loader_text',
        'loader_color',
        'spinner_color',
        // Attachment file
        'attachment_file',
        // Sidebar tag fields
        'sidebar_tag_text',
        'sidebar_tag_color',
        'sidebar_tag_bg_color',
        // Bottommark multilingual fields
        'bottommark_property_name',
        'bottommark_room_type',
        'bottommark_dimensions',
        // Document authentication field
        'document_auth_required',
        'show_document_url',
        'show_document_url2',
        // User details fields
        'show_user_details_button',
        'user_details_button_icon',
        'user_details_button_tooltip',
        'user_details',
        // Bookmark Info modal fields
        'bookmark_title',
        'bookmark_ribbon_background_color',
        'bookmark_ribbon_text_color',
        'bookmark_show_on_tour_load',
        'bookmark_show_on_tour_load_delay_ms',
        'bookmark_action',
        'bookmark_modal_title',
        'bookmark_modal_description',
        'bookmark_info_modal_footer_button_title',
        'bookmark_info_modal_footer_button_link',
        'bookmark_info_modal_footer_text',
        'bookmark_open_link_url',
        'bookmark_document_url',
        'bookmark_video_url',
        'bookmark_image_url',
        'user_star',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'structured_data' => 'array',
            'final_json' => 'array',
            'sidebar_links' => 'array',
            'sidebar_node' => 'array',
            'working_json' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'sidebar_footer_link_show' => 'boolean',
            'is_active' => 'boolean',
            'is_credentials' => 'boolean',
            'is_mobile_validation' => 'boolean',
            'is_hosted' => 'boolean',
            'show_contact_user_name' => 'boolean',
            'show_contact_google_location' => 'boolean',
            'show_contact_email' => 'boolean',
            'show_contact_website' => 'boolean',
            'show_contact_phone_no' => 'boolean',
            'show_contact_whatsapp_no' => 'boolean',
            'document_auth_required' => 'boolean',
            'show_document_url' => 'boolean',
            'show_document_url2' => 'boolean',
            'enable_language' => 'array',
            'loader_color' => 'array',
            'spinner_color' => 'array',
            'attachment_file' => 'array',
            'footer_title' => 'array',
            'footer_subtitle' => 'array',
            'footer_decription' => 'array',
            'bottommark_property_name' => 'array',
            'bottommark_room_type' => 'array',
            'bottommark_dimensions' => 'array',
            'show_user_details_button' => 'boolean',
            'user_details' => 'array',
            'bookmark_show_on_tour_load' => 'boolean',
            'bookmark_show_on_tour_load_delay_ms' => 'integer',
            'bookmark_modal_title' => 'array',
            'bookmark_modal_description' => 'array',
            'bookmark_info_modal_footer_button_title' => 'array',
            'bookmark_info_modal_footer_text' => 'array',
            'user_star' => 'array',
        ];
    }

    /**
     * The booking associated with this tour.
     */
    public function booking()
    {
        return $this->belongsTo(\App\Models\Booking::class);
    }

    /**
     * Get the credentials for the tour.
     */
    public function credentials()
    {
        return $this->hasMany(TourCredential::class);
    }

    public function mobileValidations()
    {
        return $this->hasMany(TourMobileValidation::class);
    }

    public function validationHistories()
    {
        return $this->hasMany(TourMobileValidationHistory::class);
    }


    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'title',
                'slug',
                'status',
                'price',
                'location',
                'start_date',
                'end_date',
                'revision',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from title if not provided
        static::creating(function ($tour) {
            if (empty($tour->slug)) {
                $baseSlug = \Illuminate\Support\Str::slug($tour->title);
                $slug = $baseSlug;
                $counter = 1;
                
                // Ensure slug is unique
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $tour->slug = $slug;
            }
        });

        static::updating(function ($tour) {
            if ($tour->isDirty('title') && empty($tour->slug)) {
                $baseSlug = \Illuminate\Support\Str::slug($tour->title);
                $slug = $baseSlug;
                $counter = 1;
                
                // Ensure slug is unique (exclude current tour)
                while (static::where('slug', $slug)->where('id', '!=', $tour->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $tour->slug = $slug;
            }
        });
    }

    /**
     * Scope a query to only include published tours.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft tours.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived tours.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price !== null ? '₹ ' . number_format((float) $this->price, 2) : '-';
    }

    /**
     * Get the duration in a readable format.
     */
    public function getDurationTextAttribute(): string
    {
        if (!$this->duration_days) {
            return '-';
        }
        
        $days = $this->duration_days;
        $nights = $days - 1;
        
        if ($nights > 0) {
            return "{$nights} Night" . ($nights > 1 ? 's' : '') . " / {$days} Day" . ($days > 1 ? 's' : '');
        }
        
        return "{$days} Day" . ($days > 1 ? 's' : '');
    }

    /**
     * Get FTP URL for tour if booking status is tour_live
     * Returns the full FTP URL without index.php suffix
     * 
     * @return string FTP URL or '#' if not available
     */
    public function getTourLiveUrl(): string {
        // Get the booking associated with this tour
        $booking = $this->booking;
        
        // Only generate URL if booking exists and status is tour_live
            
        // if (!$booking || $booking->status !== 'tour_live') {
        //     return '#';
        // }

        // If tour is hosted and hosted_link is not null, return hosted_link
        if ($this->is_hosted && !empty($this->hosted_link)) {
            return $this->hosted_link;
        }

        // Otherwise, use FTP URL logic
        // Check if tour has required data for FTP URL
        $customerId = $booking->customer_id;
        if (!$this->location || !$this->slug || !$customerId) {
            return '#';
        }   

        // Get FTP configuration based on tour location
        $ftpConfig = \App\Models\FtpConfiguration::where('category_name', $this->location)->first();
        
        if (!$ftpConfig) {
            return '#';
        }

        // Generate FTP URL
        $fullFtpUrl = $ftpConfig->getUrlForTour($this->slug, $customerId);
        $tourFtpUrl = rtrim($fullFtpUrl, '/');
        
        // Remove /index.php if present
        if (substr($tourFtpUrl, -9) === 'index.php') {
            $tourFtpUrl = substr($tourFtpUrl, 0, -9);
        }

        return $tourFtpUrl;
    }
}
