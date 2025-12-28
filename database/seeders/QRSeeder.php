<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QR;
use Illuminate\Support\Str;

class QRSeeder extends Seeder
{
    public function run(): void
    {
        // Check if QR codes already exist
        if (QR::count() >= 50) {
            $this->command->info('QR codes already seeded. Skipping...');
            return;
        }

        $existingCount = QR::count();
        $needToCreate = 50 - $existingCount;

        for ($i = 0; $i < $needToCreate; $i++) {
            $code = $this->generateUniqueCode();
            QR::create([
                'name' => 'QR Code ' . ($existingCount + $i + 1),
                'code' => $code,
                'image' => null,
                'qr_link' => 'https://qr.proppik.com/' . $code,
                'booking_id' => null,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }

    private function generateUniqueCode()
    {
        do {
            $code = Str::random(8);
        } while (QR::where('code', $code)->exists());
        return $code;
    }
}
