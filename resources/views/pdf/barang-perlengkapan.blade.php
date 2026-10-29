<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Perlengkapan Kebersihan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
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
        <h3 id="title">DATA PERLENGKAPAN KEBERSIHAN<br>BALAI BESAR POM DI MATARAM</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th>Nama Perlengkapan Kebersihan</th>
                    <th style="width: 14%;">Satuan</th>
                    <th style="width: 12%;">Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td>{{ $item->name ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->satuan ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->stock ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #888; padding: 15px;">Tidak ada data</td>
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

