<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustomerBookingController extends Controller
{
    public function list(Request $request)
    {
        $customerId = $request->input('customer_id');

        if (empty($customerId)) {
            return response()->json([
                'success' => false,
                'message' => 'customer_id is required.',
            ], 422);
        }

        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        try {
            $query = Booking::with([
                'propertyType:id,name,icon',
                'propertySubType:id,name,property_type_id,icon',
                'tours' => function ($q) {
                    $q->select([
                        'id', 'booking_id', 'name', 'title', 'slug',
                        'location', 'tour_thumbnail', 'is_active',
                        'is_credentials', 'is_mobile_validation',
                        'is_hosted', 'hosted_link'
                    ]);
                }
            ])->select([
                'id', 'status', 'owner_type',
                'property_type_id', 'property_sub_type_id', 'customer_id'
            ])->where('customer_id', $customer->id);

            // Search filter
            if ($request->filled('search')) {
                $search = trim((string) $request->input('search'));
                if ($search !== '') {
                    $like = '%' . $search . '%';
                    $query->where(function ($q) use ($like) {
                        $q->where('owner_type', 'like', $like)
                            ->orWhere('firm_name', 'like', $like)
                            ->orWhere('tour_code', 'like', $like)
                            ->orWhere('house_no', 'like', $like)
                            ->orWhere('building', 'like', $like)
                            ->orWhere('society_name', 'like', $like)
                            ->orWhere('address_area', 'like', $like)
                            ->orWhere('landmark', 'like', $like)
                            ->orWhere('full_address', 'like', $like)
                            ->orWhere('pin_code', 'like', $like)
                            ->orWhereHas('tours', function ($tourQuery) use ($like) {
                                $tourQuery->where('name', 'like', $like)
                                    ->orWhere('title', 'like', $like)
                                    ->orWhere('slug', 'like', $like)
                                    ->orWhere('location', 'like', $like);
                            })
                            ->orWhereHas('propertyType', function ($typeQuery) use ($like) {
                                $typeQuery->where('name', 'like', $like);
                            })
                            ->orWhereHas('propertySubType', function ($subTypeQuery) use ($like) {
                                $subTypeQuery->where('name', 'like', $like);
                            });
                    });
                }
            }

            // Filter by property_type_id
            if ($request->filled('property_type_id')) {
                $typeIds = is_array($request->input('property_type_id'))
                    ? $request->input('property_type_id')
                    : explode(',', $request->input('property_type_id'));

                $typeIds = array_values(array_filter(array_map('intval', $typeIds)));
                if (!empty($typeIds)) {
                    $query->whereIn('property_type_id', $typeIds);
                }
            }

            // Filter by property_sub_type_id
            if ($request->filled('property_sub_type_id')) {
                $subTypeIds = is_array($request->input('property_sub_type_id'))
                    ? $request->input('property_sub_type_id')
                    : explode(',', $request->input('property_sub_type_id'));

                $subTypeIds = array_values(array_filter(array_map('intval', $subTypeIds)));
                if (!empty($subTypeIds)) {
                    $query->whereIn('property_sub_type_id', $subTypeIds);
                }
            }

            // Combined property type filter (matches either type or sub-type)
            if ($request->filled('property_type_filter')) {
                $filterIds = is_array($request->input('property_type_filter'))
                    ? $request->input('property_type_filter')
                    : explode(',', $request->input('property_type_filter'));

                $filterIds = array_map('intval', $filterIds);

                $query->where(function ($q) use ($filterIds) {
                    $q->whereIn('property_type_id', $filterIds)
                        ->orWhereIn('property_sub_type_id', $filterIds);
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $statuses = is_array($request->input('status'))
                    ? $request->input('status')
                    : explode(',', $request->input('status'));

                $statuses = array_values(array_filter(array_map('trim', $statuses)));
                if (!empty($statuses)) {
                    $query->whereIn('status', $statuses);
                }
            }

            // Build the filter options from this customer's bookings only
            $filterData = $this->getCustomerFilters($customer->id);

            // Customer details
            $customerData = $this->formatCustomerData($customer);

            // Pagination
            $page = $request->input('page');
            $perPage = $request->input('per_page', 15);

            if ($page !== null) {
                $perPage = min(max((int) $perPage, 1), 100);
                $bookings = $query->orderBy('id', 'desc')->paginate($perPage);

                $data = $bookings->map(function ($booking) {
                    return $this->formatBookingData($booking);
                });

                return response()->json([
                    'success' => true,
                    'customer' => $customerData,
                    'data' => $data,
                    'filters' => $filterData,
                    'meta' => [
                        'total' => $bookings->total(),
                        'per_page' => $bookings->perPage(),
                        'current_page' => $bookings->currentPage(),
                        'total_pages' => $bookings->lastPage(),
                    ],
                    'links' => [
                        'first' => $bookings->url(1),
                        'last' => $bookings->url($bookings->lastPage()),
                        'prev' => $bookings->previousPageUrl(),
                        'next' => $bookings->nextPageUrl(),
                    ],
                ]);
            }

            $bookings = $query->orderBy('id', 'desc')->get();
            $data = $bookings->map(function ($booking) {
                return $this->formatBookingData($booking);
            });

            return response()->json([
                'success' => true,
                'customer' => $customerData,
                'data' => $data,
                'filters' => $filterData,
                'meta' => [
                    'total' => $bookings->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Customer booking list error', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch booking data.',
            ], 500);
        }
    }

    protected function formatCustomerData(Customer $customer): array
    {
        $profilePhotoUrl = $customer->profile_photo
            ? Storage::disk('public')->url($customer->profile_photo)
            : null;
        $coverPhotoUrl = $customer->cover_photo
            ? Storage::disk('public')->url($customer->cover_photo)
            : null;

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'email' => $customer->email,
            'base_mobile' => $customer->base_mobile,
            'mobile' => $customer->mobile,
            'country_id' => $customer->country_id,
            'dial_code' => $customer->dial_code,
            'country_code' => $customer->country_code,
            'company_name' => $customer->company_name,
            'company_website' => $customer->company_website,
            'designation' => $customer->designation,
            'tag_line' => $customer->tag_line,
            'social_link' => $customer->social_link,
            'profile_photo' => $customer->profile_photo,
            'cover_photo' => $customer->cover_photo,
            'profile_photo_url' => $profilePhotoUrl,
            'cover_photo_url' => $coverPhotoUrl,
        ];
    }

    /**
     * Get property_type and property_sub_type filter options
     * from only this customer's bookings (not all system data).
     */
    protected function getCustomerFilters(int $customerId): array
    {
        $bookings = Booking::where('customer_id', $customerId)
            ->whereNotNull('property_type_id')
            ->select('property_type_id', 'property_sub_type_id')
            ->get();

        $typeIds = $bookings->pluck('property_type_id')->unique()->filter()->values()->toArray();
        $subTypeIds = $bookings->pluck('property_sub_type_id')->unique()->filter()->values()->toArray();

        $propertyTypes = [];
        if (!empty($typeIds)) {
            $propertyTypes = \App\Models\PropertyType::whereIn('id', $typeIds)
                ->select('id', 'name', 'icon')
                ->get()
                ->toArray();
        }

        $propertySubTypes = [];
        if (!empty($subTypeIds)) {
            $propertySubTypes = \App\Models\PropertySubType::whereIn('id', $subTypeIds)
                ->select('id', 'name', 'property_type_id', 'icon')
                ->get()
                ->toArray();
        }

        return [
            'property_types' => $propertyTypes,
            'property_sub_types' => $propertySubTypes,
        ];
    }

    protected function getTourThumbnailUrl(?string $thumbnail): ?string
    {
        if (empty($thumbnail)) {
            return null;
        }

        if (preg_match('#^https?://#i', $thumbnail)) {
            return $thumbnail;
        }

        try {
            return Storage::disk('s3')->url($thumbnail);
        } catch (\Exception $e) {
            Log::warning('Failed to get S3 URL for tour thumbnail', [
                'thumbnail' => $thumbnail,
                'error' => $e->getMessage(),
            ]);
            return $thumbnail;
        }
    }

    protected function formatBookingData(Booking $booking): array
    {
        $tour = $booking->tours->first();
        $bookingLiveLink = $booking->getTourLiveUrl();

        $tourLiveLink = '#';
        if ($tour) {
            $tour->setRelation('booking', $booking);
            $tourLiveLink = $tour->getTourLiveUrl();
        }

        return [
            'booking_id' => $booking->id,
            'booking_status' => $booking->status,
            'owner_type' => $booking->owner_type,
            'property_type' => $booking->propertyType ? [
                'id' => $booking->propertyType->id,
                'name' => $booking->propertyType->name,
            ] : null,
            'property_sub_type' => $booking->propertySubType ? [
                'id' => $booking->propertySubType->id,
                'name' => $booking->propertySubType->name,
                'property_type_id' => $booking->propertySubType->property_type_id,
            ] : null,
            'booking_live_link' => $bookingLiveLink,
            'tour' => $tour ? [
                'name' => $tour->name,
                'title' => $tour->title,
                'slug' => $tour->slug,
                'location' => $tour->location,
                'tour_thumbnail' => $this->getTourThumbnailUrl($tour->tour_thumbnail),
                'is_active' => (bool) $tour->is_active,
                'is_credentials' => (bool) $tour->is_credentials,
                'is_mobile_validation' => (bool) $tour->is_mobile_validation,
                'is_hosted' => (bool) $tour->is_hosted,
                'hosted_link' => $tour->hosted_link,
                'tour_live_link' => $tourLiveLink,
            ] : null,
        ];
    }
}
