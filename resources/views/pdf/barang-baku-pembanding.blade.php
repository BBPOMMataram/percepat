<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Baku Pembanding</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        #header table {
            width: 100%;
            border-collapse: collapse;
        }

        #title {
            margin-top: 0;
            text-align: center;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            border: 1px solid black;
            padding: 5px 6px;
        }

        table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        #footer {
            margin-top: 15px;
            text-align: right;
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div id="content">
        <h3 id="title">DATA BAKU PEMBANDING<br>BALAI BESAR POM DI MATARAM</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 14%;">Kode Baru</th>
                    <th>Nama Baku Pembanding</th>
                    <th style="width: 14%;">Letak</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 8%;">Stok</th>
                    <th style="width: 12%;">Expired</th>
                    <th style="width: 10%;">MSDS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td>{{ $item->kode_baru ?? '-' }}</td>
                        <td>{{ $item->name ?? '-' }}</td>
                        <td>{{ $item->letak ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->satuan ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->stock ?? '-' }}</td>
                        <td style="text-align: center;">
                            @if(isset($item->expired))
                                {{ \Carbon\Carbon::parse($item->expired)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->msds ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #888; padding: 15px;">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div id="footer">
        <div>Mataram, {{ now()->isoFormat('D MMMM Y') }}</div>
    </div>
</body>

</html>
