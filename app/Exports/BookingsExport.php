<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\DB;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $filters;
    protected $exportedBy;
    protected $exportedAt;
    protected $totals = [
        'price' => 0,
        'area' => 0,
        'bookings' => 0
    ];

    public function __construct($filters = [], $exportedBy = null)
    {
        $this->filters = $filters;
        $this->exportedBy = $exportedBy;
        $this->exportedAt = now();
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
            'tours',
        ]);

        // Apply filters
        if (!empty($this->filters['state_id'])) {
            $query->where('state_id', $this->filters['state_id']);
        }

        if (!empty($this->filters['city_id'])) {
            $query->where('city_id', $this->filters['city_id']);
        }

        if (!empty($this->filters['owner_type'])) {
            $query->where('owner_type', $this->filters['owner_type']);
        }

        if (!empty($this->filters['property_type_id'])) {
            $query->where('property_type_id', $this->filters['property_type_id']);
        }

        if (!empty($this->filters['property_sub_type_id'])) {
            $query->where('property_sub_type_id', $this->filters['property_sub_type_id']);
        }

        if (!empty($this->filters['pin_code'])) {
            $query->where('pin_code', $this->filters['pin_code']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['from']) && !empty($this->filters['to'])) {
            $query->whereBetween('created_at', [
                $this->filters['from'] . ' 00:00:00',
                $this->filters['to'] . ' 23:59:59'
            ]);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $this->totals['bookings'] = $bookings->count();
        $this->totals['price'] = $bookings->sum('price');
        $this->totals['area'] = $bookings->sum('area');

        return $bookings;
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Serial No.',
            'Full Name',
            'Mobile',
            'Email',
            'Owner Type',
            'Property Type',
            'Property Sub Type',
            'Area (Sq. Ft)',
            'Price (₹)',
            'State',
            'City',
            'Pin Code',
            'Tour Title',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * Map data to columns
     */
    public function map($booking): array
    {
        static $serial = 0;
        $serial++;

        // Get tour title
        $tourTitle = $booking->tours && $booking->tours->count() > 0 
            ? $booking->tours->first()->title ?? 'N/A' 
            : 'N/A';

        return [
            $serial,
            $booking->user ? $booking->user->firstname . ' ' . $booking->user->lastname : 'N/A',
            $booking->user?->mobile ?? 'N/A',
            $booking->user?->email ?? 'N/A',
            $booking->owner_type ? ucfirst($booking->owner_type) : 'N/A',
            $booking->propertyType?->name ?? 'N/A',
            $booking->propertySubType?->name ?? 'N/A',
            number_format($booking->area, 2),
            number_format($booking->price, 2),
            $booking->state?->name ?? 'N/A',
            $booking->city?->name ?? 'N/A',
            $booking->pin_code ?? 'N/A',
            $tourTitle,
            $booking->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $booking->updated_at?->format('Y-m-d H:i:s') ?? 'N/A',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Note: This runs before registerEvents, so row numbers are not yet shifted
        // The actual styling of header row is done in registerEvents after insertion
        return [];
    }

    /**
     * Register events to add metadata at the top
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get the last data row before insertion
                $lastDataRow = $sheet->getHighestRow();
                
                // Insert 4 rows at the top for metadata
                $sheet->insertNewRowBefore(1, 4);
                
                // Add export metadata
                $exportedByName = $this->exportedBy ? $this->exportedBy->firstname . ' ' . $this->exportedBy->lastname : 'System';
                $exportDate = $this->exportedAt->format('d M Y');
                $exportTime = $this->exportedAt->format('h:i A');
                
                // Row 1: Report Title
                $sheet->setCellValue('A1', 'BOOKINGS EXPORT REPORT');
                $sheet->mergeCells('A1:O1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
                
                // Row 2: Export Date and Time
                $sheet->setCellValue('A2', 'Export Date:');
                $sheet->setCellValue('B2', $exportDate);
                $sheet->setCellValue('D2', 'Export Time:');
                $sheet->setCellValue('E2', $exportTime);
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('D2')->getFont()->setBold(true);
                
                // Row 3: Exported By
                $sheet->setCellValue('A3', 'Exported By:');
                $sheet->setCellValue('B3', $exportedByName);
                $sheet->getStyle('A3')->getFont()->setBold(true);
                
                // Row 4: Empty row (separator) - clear any styling
                $sheet->getStyle('A4:O4')->getFont()->setBold(false);
                $sheet->getStyle('A4:O4')->getFill()->setFillType(Fill::FILL_NONE);
                
                // Add light background to rows 2 and 3
                $sheet->getStyle('A2:O2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                $sheet->getStyle('A3:O3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                
                // Add border around metadata section (rows 1-3)
                $sheet->getStyle('A1:O3')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '4472C4']
                        ],
                    ],
                ]);
                
                // Now style the header row (row 5 after insertion)
                $sheet->getStyle('A5:O5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Add totals at the bottom (after the 4 inserted rows)
                $totalRow = $lastDataRow + 4 + 1; // +4 for inserted rows, +1 for new row
                $sheet->setCellValue('A' . $totalRow, 'TOTALS:');
                $sheet->setCellValue('H' . $totalRow, number_format($this->totals['area'], 2));
                $sheet->setCellValue('I' . $totalRow, number_format($this->totals['price'], 2));
                
                $sheet->setCellValue('A' . ($totalRow + 1), 'Total Bookings:');
                $sheet->setCellValue('B' . ($totalRow + 1), $this->totals['bookings']);
                
                // Style totals rows
                $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC']
                    ]
                ]);
                
                $sheet->getStyle('A' . ($totalRow + 1) . ':O' . ($totalRow + 1))->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC']
                    ]
                ]);
                
                // Set row heights for better visibility
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(5)->setRowHeight(20);
                
                // Auto-size columns
                foreach(range('A','O') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}

