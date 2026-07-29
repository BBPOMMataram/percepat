<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        body {
            margin: 20px;
        }

        h2 {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .meta {
            margin-bottom: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th {
            background: #2563eb;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
        }

        td {
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) td {
            background: #f8fafc;
        }

        .barang-list {
            margin: 0;
            padding-left: 14px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .footer {
            margin-top: 16px;
            font-size: 10px;
            color: #888;
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Daftar Permintaan Perlengkapan Kebersihan</h2>
    <div class="meta">
        Halaman {{ $page }} &bull; Menampilkan {{ count($items) }} dari {{ $total }} data
        @if ($nameQuery)
            &bull; Filter barang: <strong>{{ $nameQuery }}</strong>
        @endif
        @if ($startDate || $endDate)
            &bull; Periode:
            <strong>
                {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '...' }}
                s/d
                {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '...' }}
            </strong>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:15%">Tgl Permintaan</th>
                <th style="width:14%">Peminta</th>
                <th style="width:13%">Bidang</th>
                <th style="width:9%">Status</th>
                <th style="width:10%">Katim</th>
                <th style="width:10%">Penyerah</th>
                <th style="width:25%">Daftar Barang</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
                <tr>
                    <td>{{ ($page - 1) * $perPage + $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tgl_permintaan)->format('d/m/Y') }}</td>
                    <td>{{ $item->peminta?->name ?? '-' }}</td>
                    <td>{{ $item->bidang?->name ?? ($item->bidang_name_auth_external ?? '-') }}</td>
                    <td>
                        <span class="badge" style="background:#dbeafe;color:#1d4ed8">
                            {{ $item->status?->name ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $item->katim?->name ?? '-' }}</td>
                    <td>{{ $item->penyerah?->name ?? '-' }}</td>
                    <td>
                        @if ($item->permintaanListPerlengkapanKebersihan && $item->permintaanListPerlengkapanKebersihan->count())
                            <ul class="barang-list">
                                @foreach ($item->permintaanListPerlengkapanKebersihan as $list)
                                    <li>
                                        {{ $list->barang?->name ?? '-' }}
                                        <span style="color:#6b7280">
                                            (Minta: {{ $list->jumlahpermintaan }} / Realisasi:
                                            {{ $list->jumlahrealisasi ?? '-' }})
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:20px;color:#888">
                        Tidak ada data
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>
