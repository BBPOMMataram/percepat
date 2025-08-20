<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DATA PER 13 AGUSTUS 2025
        DB::table('sites')->insert([
            [
                'id' => 1,
                'name' => 'PERCEPAT',
                'link' => 'https://percepat.bbpommataram.id',
                'desc' => 'PERSEDIAAN CEPAT DAN TEPAT',
                'logo_path' => 'images/66bd6053d6045.webp',
                'pic' => 'KBKC',
                'clicks' => 622,
                'created_at' => Carbon::parse('2024-08-15 01:56:06'),
                'updated_at' => Carbon::parse('2025-08-13 00:08:15'),
            ],
            [
                'id' => 2,
                'name' => 'E - Tamu',
                'link' => 'https://e-tamu.bbpommataram.id',
                'desc' => 'ELEKTRONIK PENERIMAAN TAMU',
                'logo_path' => 'images/66cbf0a55f822.webp',
                'pic' => 'TATA USAHA',
                'clicks' => 517,
                'created_at' => Carbon::parse('2024-08-15 01:56:06'),
                'updated_at' => Carbon::parse('2025-08-13 07:51:09'),
            ],
            [
                'id' => 3,
                'name' => 'SIPROVAL',
                'link' => 'https://siproval.bbpommataram.id',
                'desc' => 'SISTEM PROGRES DAN EVALUASI',
                'logo_path' => 'images/66cbf0d374149.webp',
                'pic' => 'HILMI',
                'clicks' => 871,
                'created_at' => Carbon::parse('2024-08-15 01:56:06'),
                'updated_at' => Carbon::parse('2025-08-13 03:16:17'),
            ],
            [
                'id' => 4,
                'name' => 'SIMPEL BMN',
                'link' => 'https://simpel.bbpommataram.id',
                'desc' => 'SISTEM PEMELIHARAAN BMN',
                'logo_path' => 'images/66cbf12fe3d47.webp',
                'pic' => 'Santoso',
                'clicks' => 532,
                'created_at' => Carbon::parse('2024-08-15 01:56:06'),
                'updated_at' => Carbon::parse('2025-08-13 00:08:23'),
            ],
            [
                'id' => 7,
                'name' => 'E_SEMU',
                'link' => 'https://e-semu.bbpommataram.id/',
                'desc' => 'ELEKTRONIK SISTEM MUTU',
                'logo_path' => 'images/66d6cb617c1d3.webp',
                'pic' => 'WULAN',
                'clicks' => 524,
                'created_at' => Carbon::parse('2024-09-03 16:37:55'),
                'updated_at' => Carbon::parse('2025-08-13 01:34:25'),
            ],
            [
                'id' => 8,
                'name' => 'PAULA',
                'link' => 'https://paula.bbpommataram.id',
                'desc' => 'Peminjaman Aula Balai Besar POM di Mataram',
                'logo_path' => 'images/680597ef11c0c.webp',
                'pic' => 'Oso',
                'clicks' => 269,
                'created_at' => Carbon::parse('2025-04-21 08:52:33'),
                'updated_at' => Carbon::parse('2025-08-13 00:08:29'),
            ],
            [
                'id' => 9,
                'name' => 'PORTAL SIPT PIHAK KE-3',
                'link' => 'https://sipt.pom.go.id/pihak-3/login',
                'desc' => null,
                'logo_path' => 'images/684a1f8c766aa.webp',
                'pic' => 'WANTI',
                'clicks' => 311,
                'created_at' => Carbon::parse('2025-05-05 14:12:12'),
                'updated_at' => Carbon::parse('2025-08-13 00:55:54'),
            ],
        ]);
    }
}
