<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerlengkapanKebersihanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('perlengkapan_kebersihans')->insert([
            [
                'code' => 'PKB-001',
                'name' => 'Sapu Lantai',
                'satuan' => 'buah',
                'stock' => 20,
                'description' => 'Sapu lantai berbahan ijuk',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'PKB-002',
                'name' => 'Pel Lantai',
                'satuan' => 'buah',
                'stock' => 15,
                'description' => 'Pel lantai microfiber',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'PKB-003',
                'name' => 'Ember Plastik',
                'satuan' => 'buah',
                'stock' => 10,
                'description' => 'Ember plastik ukuran sedang',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'PKB-004',
                'name' => 'Cairan Pembersih Lantai',
                'satuan' => 'botol',
                'stock' => 25,
                'description' => 'Cairan pembersih lantai aroma lemon',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'PKB-005',
                'name' => 'Lap Kain',
                'satuan' => 'buah',
                'stock' => 30,
                'description' => 'Lap kain serbaguna',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'PKB-006',
                'name' => 'Sarung Tangan Karet',
                'satuan' => 'pasang',
                'stock' => 12,
                'description' => 'Sarung tangan karet untuk kebersihan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
