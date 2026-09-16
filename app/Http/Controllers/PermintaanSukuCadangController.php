    public function exportPdf(Request $request)
    {
        $perPage   = (int) $request->query('per_page', 10);
        $page      = $request->query('page', 1);
        $nameQuery = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $query = Permintaan::with([
            'peminta',
            'status',
            'bidang',
            'bidang.user',
            'katim',
            'penyerah',
            'permintaanListSukuCadang.sukuCadang',
        ])->where('jenis', 'suku_cadang');

        if ($nameQuery) {
            $query->whereHas('permintaanListSukuCadang.sukuCadang', function ($q) use ($nameQuery) {
                $q->where('name', 'like', '%' . $nameQuery . '%');
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('tgl_permintaan', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        } elseif ($startDate) {
            $query->whereDate('tgl_permintaan', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('tgl_permintaan', '<=', $endDate);
        }

        $query->latest();

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $pdf = Pdf::loadView('pdf.permintaan-suku-cadang-export', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("permintaan-suku-cadang-hal{$page}.pdf");
    }
