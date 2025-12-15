<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_date' => $this->booking_date?->format('Y-m-d'),
            'booking_notes' => $this->booking_notes,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'price' => $this->price,
            'tour_final_link' => $this->tour_final_link,
            'tour_code' => $this->tour_code,
            
            // Property Details
            'property' => [
                'type' => [
                    'id' => $this->propertyType?->id,
                    'name' => $this->propertyType?->name,
                    'slug' => $this->propertyType?->slug,
                ],
                'sub_type' => [
                    'id' => $this->propertySubType?->id,
                    'name' => $this->propertySubType?->name,
                    'slug' => $this->propertySubType?->slug,
                ],
                'bhk' => [
                    'id' => $this->bhk?->id,
                    'name' => $this->bhk?->name,
                    'slug' => $this->bhk?->slug,
                ],
                'owner_type' => $this->owner_type,
                'furniture_type' => $this->furniture_type,
                'area' => $this->area,
                'other_option_details' => $this->other_option_details,
            ],
            
            // Address Details
            'address' => [
                'house_no' => $this->house_no,
                'building' => $this->building,
                'society_name' => $this->society_name,
                'address_area' => $this->address_area,
                'landmark' => $this->landmark,
                'full_address' => $this->full_address,
                'pin_code' => $this->pin_code,
                'city' => [
                    'id' => $this->city?->id,
                    'name' => $this->city?->name,
                ],
                'state' => [
                    'id' => $this->state?->id,
                    'name' => $this->state?->name,
                ],
            ],
            
            // Firm Details
            'firm' => [
                'name' => $this->firm_name,
                'gst_no' => $this->gst_no,
            ],
            
            // User Information
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            
            // Payment Information
            'payment' => [
                'cashfree_order_id' => $this->cashfree_order_id,
                'cashfree_payment_session_id' => $this->cashfree_payment_session_id,
                'cashfree_payment_status' => $this->cashfree_payment_status,
                'cashfree_payment_method' => $this->cashfree_payment_method,
                'cashfree_payment_amount' => $this->cashfree_payment_amount,
                'cashfree_payment_currency' => $this->cashfree_payment_currency,
                'cashfree_reference_id' => $this->cashfree_reference_id,
                'cashfree_payment_at' => $this->cashfree_payment_at?->format('Y-m-d H:i:s'),
                'cashfree_payment_message' => $this->cashfree_payment_message,
            ],
            
            // Computed Fields
            'meta' => [
                'is_ready_for_payment' => $this->isReadyForPayment(),
                'has_complete_property_data' => $this->hasCompletePropertyData(),
                'has_complete_address_data' => $this->hasCompleteAddressData(),
            ],
            
            // Audit Fields
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'updated_by' => [
                'id' => $this->updater?->id,
                'name' => $this->updater?->name,
            ],
        ];
    }
}
