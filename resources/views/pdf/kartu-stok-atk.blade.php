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
            padding: 15px 20px;
        }
        #header {
            border-bottom: 2px solid black;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        #header table {
            width: 100%;
            border-collapse: collapse;
        }
        #header table td {
            vertical-align: top;
        }
        #logo {
            width: 60px;
            text-align: center;
        }
        #logo img {
            width: 45px;
        }
        #title {
            text-align: center;
        }
        #title h2 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
        }
        #title h3 {
            margin: 2px 0;
            font-size: 12px;
            font-weight: bold;
        }
        #meta {
            text-align: right;
            font-size: 10px;
            width: 180px;
        }
        #meta table {
            width: 100%;
        }
        #meta table td {
            padding: 1px 0;
        }
        #meta table td:first-child {
            width: 70px;
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
            width: 100px;
        }
        #content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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
    </style>
</head>
<body>
    <div id="header">
        <table>
            <tr>
                <td id="logo">
                    <img src="storage/bpomri.png" alt="Logo BBPOM">
                </td>
                <td id="title">
                    <h2>BADAN POM RI</h2>
                    <h3>KARTU STOK PERSIAAN</h3>
                </td>
                <td id="meta">
                    <table>
                        <tr>
                            <td>Form</td>
                            <td>: POM-14.01CFM.01SOP.01IK.14A.02</td>
                        </tr>
                        <tr>
                            <td>Dibuat tanggal</td>
                            <td>: 27 Juni 2019</td>
                        </tr>
                        <tr>
                            <td>Revisi</td>
                            <td>: Oktober 2024</td>
                        </tr>
                        <tr>
                            <td>Halaman</td>
                            <td>: 1 dari 1</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
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
            <tr>
                <td>Kode</td>
                <td>: {{ $barang->code ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div id="content">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Tanggal</th>
                    <th rowspan="2">Uraian</th>
                    <th colspan="3">Jumlah</th>
                </tr>
                <tr>
                    <th>Terima</th>
                    <th>Keluar</th>
                    <th>Jumlah</th>
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
</body>
</html>
