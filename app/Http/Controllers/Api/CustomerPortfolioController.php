<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CustomerPortfolioController extends Controller
{
    public function list(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        try {
            $query = Booking::with([
                'propertyType:id,name',
                'propertySubType:id,name,property_type_id',
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

            if ($request->filled('property_type_id')) {
                $typeIds = is_array($request->input('property_type_id'))
                    ? $request->input('property_type_id')
                    : explode(',', $request->input('property_type_id'));

                $typeIds = array_values(array_filter(array_map('intval', $typeIds)));
                if (!empty($typeIds)) {
                    $query->whereIn('property_type_id', $typeIds);
                }
            }

            if ($request->filled('property_sub_type_id')) {
                $subTypeIds = is_array($request->input('property_sub_type_id'))
                    ? $request->input('property_sub_type_id')
                    : explode(',', $request->input('property_sub_type_id'));

                $subTypeIds = array_values(array_filter(array_map('intval', $subTypeIds)));
                if (!empty($subTypeIds)) {
                    $query->whereIn('property_sub_type_id', $subTypeIds);
                }
            }

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
                    'data' => $data,
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
                'data' => $data,
                'meta' => [
                    'total' => $bookings->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Customer portfolio list error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch portfolio data.',
            ], 500);
        }
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
            'property_type' => $booking->propertyType ? $booking->propertyType->name : null,
            'property_sub_type' => $booking->propertySubType ? $booking->propertySubType->name : null,
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
