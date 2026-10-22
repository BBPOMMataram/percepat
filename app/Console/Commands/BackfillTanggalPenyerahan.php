<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permintaan;
use Illuminate\Support\Facades\DB;

class BackfillTanggalPenyerahan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permintaan:backfill-tgl-penyerahan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengisi tanggal penyerahan yang kosong pada data lama permintaan berstatus selesai dengan tanggal permintaannya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memeriksa data permintaan lama yang selesai tetapi tanggal penyerahan masih kosong...');

        $query = Permintaan::where('status_id', 4)
            ->whereNull('tgl_penyerahan');

        $count = $query->count();

        if ($count === 0) {
            $this->info('Tidak ditemukan data lama yang perlu diperbarui. Semua data sudah memiliki tanggal penyerahan.');
            return 0;
        }

        $this->warn("Ditemukan {$count} data permintaan selesai yang tanggal penyerahannya masih kosong.");

        $updated = Permintaan::where('status_id', 4)
            ->whereNull('tgl_penyerahan')
            ->update([
                'tgl_penyerahan' => DB::raw('tgl_permintaan')
            ]);

        $this->info("Berhasil memperbarui {$updated} data! Kolom tanggal penyerahan telah terisi sama dengan tanggal permintaan.");

        return 0;
    }
}
