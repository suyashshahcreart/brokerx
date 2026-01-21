<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Booking::with([
            'user',
            'propertyType',
            'propertySubType',
            'bhk',
            'city',
            'state',
            'assignees.user',
            'tours',
        ]);

        // Apply filters
        if (!empty($this->filters['state_id'])) {
            $query->where('state_id', $this->filters['state_id']);
        }

        if (!empty($this->filters['city_id'])) {
            $query->where('city_id', $this->filters['city_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            $query->whereBetween('booking_date', [
                $this->filters['date_from'],
                $this->filters['date_to']
            ]);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Booking ID',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Property Type',
            'Property Subtype',
            'BHK',
            'Area (sq ft)',
            'Price (₹)',
            'Payment Amount (₹)',
            'Payment Status',
            'City',
            'State',
            'Pincode',
            'Address',
            'Booking Date',
            'Status',
            'Assigned Photographers',
            'Tour URL',
            'Tour Status',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($booking): array
    {
        // Get assigned photographers
        $photographers = $booking->assignees->map(function ($assignee) {
            return $assignee->user ? $assignee->user->firstname . ' ' . $assignee->user->lastname : 'N/A';
        })->implode(', ');

        // Tour URL
        $tourUrl = $booking->tour ? url('/tours/' . $booking->tour->tour_id) : 'N/A';
        $tourStatus = $booking->tour ? $booking->tour->status : 'N/A';

        return [
            $booking->id,
            $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : 'N/A',
            $booking->user?->email ?? 'N/A',
            $booking->user?->mobile ?? 'N/A',
            $booking->propertyType?->name ?? 'N/A',
            $booking->propertySubType?->name ?? 'N/A',
            $booking->bhk?->name ?? 'N/A',
            number_format($booking->area, 2),
            number_format($booking->price, 2),
            number_format($booking->cashfree_payment_amount ?? $booking->price ?? 0, 2),
            ucfirst($booking->payment_status ?? 'N/A'),
            $booking->city?->name ?? 'N/A',
            $booking->state?->name ?? 'N/A',
            $booking->pin_code ?? 'N/A',
            $booking->full_address ?? 'N/A',
            $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'N/A',
            ucfirst($booking->status ?? 'N/A'),
            $photographers ?: 'Not Assigned',
            $tourUrl,
            ucfirst($tourStatus),
            $booking->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $booking->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold header
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
