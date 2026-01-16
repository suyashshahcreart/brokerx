<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FtpConfiguration;
use Illuminate\Support\Facades\DB;

class FtpConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            [
                'category_name' => 'industry',
                'display_name' => 'Industry',
                'main_url' => 'industry.proppik.com',
                'driver' => 'ftp',
                'host' => '82.25.125.92',
                'username' => 'u646288003.industryproppik',
                'password' => 'Other@@42@@',
                'port' => 21,
                'root' => '/',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
                'remote_path_pattern' => '{customer_id}/{slug}/index.php',
                'url_pattern' => 'https://{main_url}/{remote_path}',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_name' => 'htl',
                'display_name' => 'HTL',
                'main_url' => 'htl.proppik.com',
                'driver' => 'ftp',
                'host' => '82.25.125.92',
                'username' => 'u646288003.htlproppik',
                'password' => 'Other@@42@@',
                'port' => 21,
                'root' => '/',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
                'remote_path_pattern' => '{customer_id}/{slug}/index.php',
                'url_pattern' => 'https://{main_url}/{remote_path}',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category_name' => 're',
                'display_name' => 'RE',
                'main_url' => 're.proppik.com',
                'driver' => 'ftp',
                'host' => '82.25.125.92',
                'username' => 'u646288003.reproppik',
                'password' => 'Other@@42@@',
                'port' => 21,
                'root' => '/',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
                'remote_path_pattern' => '{customer_id}/{slug}/index.php',
                'url_pattern' => 'https://{main_url}/{remote_path}',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category_name' => 'rs',
                'display_name' => 'RS',
                'main_url' => 'rs.proppik.com',
                'driver' => 'ftp',
                'host' => '82.25.125.92',
                'username' => 'u646288003.rsproppik',
                'password' => 'Other@@42@@',
                'port' => 21,
                'root' => '/',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
                'remote_path_pattern' => '{customer_id}/{slug}/index.php',
                'url_pattern' => 'https://{main_url}/{remote_path}',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category_name' => 'tours',
                'display_name' => 'Tours',
                'main_url' => 'tour.proppik.in',
                'driver' => 'sftp',
                'host' => '13.204.231.57',
                'username' => 'tourftp',
                'password' => 'Tour@@42##',
                'port' => 22,
                'root' => '/public_html',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
                'remote_path_pattern' => '{customer_id}/{slug}/index.php',
                'url_pattern' => 'https://{main_url}/{remote_path}',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'category_name' => 'creart_qr',
                'display_name' => 'Creart QR',
                'main_url' => 'creart.in',
                'driver' => 'ftp',
                'host' => '147.93.109.17',
                'username' => 'u678951868.qrcreart',
                'password' => 'Other@@42@@',
                'port' => 21,
                'root' => '/',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
                'remote_path_pattern' => 'qr/{customer_id}/{slug}/index.php',
                'url_pattern' => 'http://{main_url}/{remote_path}',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];
        
        foreach ($configs as $config) {
            FtpConfiguration::updateOrCreate(
                ['category_name' => $config['category_name']],
                $config
            );
        }
        
        $this->command->info('FTP configurations seeded successfully!');
    }
}
