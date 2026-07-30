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

        .footer {
            margin-top: 16px;
            font-size: 10px;
            color: #888;
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Data Penerimaan Perlengkapan Kebersihan</h2>
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
                <th style="width:18%">Nama Barang</th>
                <th style="width:10%">Satuan</th>
                <th style="width:8%">Jumlah</th>
                <th style="width:15%">Vendor</th>
                <th style="width:15%">Tanggal Terima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
                <tr>
                    <td>{{ ($page - 1) * $perPage + $i + 1 }}</td>
                    <td>{{ $item->barang?->name ?? '-' }}</td>
                    <td>{{ $item->barang?->satuan ?? '-' }}</td>
                    <td>{{ $item->jumlah ?? '-' }}</td>
                    <td>{{ $item->vendor ?? '-' }}</td>
                    <td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:20px;color:#888">
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
