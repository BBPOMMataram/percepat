<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Stok Persediaan - {{ $barang->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
        }
        #header {
            text-align: center;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        #header img {
            width: 50px;
            display: block;
            margin: 0 auto 5px;
        }
        #header h2 {
            margin: 0;
            font-size: 14px;
        }
        #header h3 {
            margin: 2px 0;
            font-size: 12px;
        }
        #header p {
            margin: 0;
            font-size: 10px;
        }
        #info-barang {
            margin-bottom: 10px;
        }
        #info-barang table {
            width: 100%;
        }
        #info-barang table td {
            padding: 2px 0;
        }
        #info-barang table td:first-child {
            width: 120px;
        }
        #content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        #content table th, #content table td {
            border: 1px solid black;
            padding: 4px 6px;
            text-align: center;
        }
        #content table th {
            font-weight: bold;
        }
        #content table td.text-left {
            text-align: left;
        }
        #footer table {
            width: 100%;
        }
        #footer table td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }
        #footer .ttd {
            height: 50px;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div id="header">
        <img src="storage/bpomri.png" alt="Logo BBPOM">
        <h2>BADAN POM RI</h2>
        <h3>KARTU STOK PERSIAAN</h3>
        <p>BALAI BESAR POM DI MATARAM</p>
    </div>

    <div id="info-barang">
        <table>
            <tr>
                <td>Nama Barang</td>
                <td>: {{ $barang->name }}</td>
            </tr>
            <tr>
                <td>Satuan</td>
                <td>: {{ $barang->satuan }}</td>
            </tr>
        </table>
    </div>

    <div id="content">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Tanggal</th>
                    <th rowspan="2">Keterangan</th>
                    <th colspan="2">Jumlah</th>
                    <th rowspan="2">Jumlah</th>
                </tr>
                <tr>
                    <th>Masuk</th>
                    <th>Keluar</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse($transaksi as $item)
                    @php
                        $masuk = $item->tipe == 'masuk' ? $item->jumlah : 0;
                        $keluar = $item->tipe == 'keluar' ? $item->jumlah : 0;
                        $total = $total + $masuk - $keluar;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMM Y') }}</td>
                        <td class="text-left">{{ $item->keterangan }}</td>
                        <td>{{ $masuk > 0 ? $masuk : '-' }}</td>
                        <td>{{ $keluar > 0 ? $keluar : '-' }}</td>
                        <td>{{ $total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada transaksi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="footer">
        <table>
            <tr>
                <td>
                    Dibuat tanggal,<br>
                    Mataram, {{ now()->isoFormat('D MMMM Y') }}<br>
                    <div class="ttd"></div>
                    <strong>Diisi,</strong><br>
                    ...............................
                </td>
                <td>
                    Diketahui,<br>
                    <div class="ttd"></div>
                    <strong>Penanggung Jawab,</strong><br>
                    ...............................
                </td>
                <td>
                    Mengetahui,<br>
                    <div class="ttd"></div>
                    <strong>Administrator,</strong><br>
                    ...............................
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
