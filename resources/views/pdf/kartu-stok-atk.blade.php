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
            background-color: #fffde7;
        }
        #header {
            border: 1px solid black;
            border-bottom: none;
        }
        #header table {
            width: 100%;
            border-collapse: collapse;
        }
        #header table td {
            vertical-align: top;
        }
        #logo-cell {
            width: 90px;
            text-align: center;
            padding: 8px;
            border-right: 1px solid black;
            vertical-align: middle;
        }
        #logo-cell img {
            width: 55px;
        }
        #meta-cell {
            padding: 0;
        }
        #meta-cell table {
            width: 100%;
            border-collapse: collapse;
        }
        #meta-cell table td {
            padding: 4px 8px;
            font-size: 10px;
            border: 1px solid black;
            border-top: none;
            border-right: none;
            white-space: nowrap;
        }
        #meta-cell table td:first-child {
            width: 120px;
            font-weight: bold;
            border-left: none;
        }
        #meta-cell table tr:first-child td {
            border-top: none;
        }
        #meta-cell table tr:last-child td {
            border-bottom: none;
        }
        #title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 2px 0;
            margin: 0;
            line-height: 1.2;
        }
        #info-barang {
            margin: 2px 0;
        }
        #info-barang table {
            width: 100%;
        }
        #info-barang table td {
            padding: 1px 0;
        }
        #info-barang table td:first-child {
            width: 60px;
        }
        #lokasi {
            border: 1px solid black;
            padding: 4px 8px;
            text-align: center;
            font-size: 10px;
            width: 80px;
            margin-left: auto;
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
                <td id="logo-cell" rowspan="4">
                    <img src="storage/bpomri.png" alt="Logo BBPOM">
                </td>
                <td id="meta-cell">
                    <table>
                        <tr>
                            <td>Nomor Formulir</td>
                            <td>POM-14.01CFM.01SOP.01IK.14A.02</td>
                        </tr>
                        <tr>
                            <td>Tanggal Pembuatan</td>
                            <td>27 Juni 2019</td>
                        </tr>
                        <tr>
                            <td>Nomor / Tanggal Revisi</td>
                            <td>04 / 21 Oktober 2024</td>
                        </tr>
                        <tr>
                            <td>Nama Formulir</td>
                            <td>Form Kartu Stok Persediaan</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div id="title">KARTU STOK PERSEDIAAN</div>

    <div id="info-barang">
        <table>
            <tr>
                <td>NAMA</td>
                <td>: {{ $barang->name }}</td>
                <td style="width: 80px;"></td>
                <td>
                    <div id="lokasi">LOKASI<br>(no.)</div>
                </td>
            </tr>
            <tr>
                <td>SATUAN</td>
                <td>: {{ $barang->satuan }} &lt; {{ $barang->code ?? '-' }} &gt;</td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    <div id="content">
        <table>
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>KETERANGAN</th>
                    <th>TERIMA</th>
                    <th>KELUAR</th>
                    <th>JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse($transaksi as $item)
                    @php
                        $masuk = $item['tipe'] == 'masuk' ? $item['jumlah'] : 0;
                        $keluar = $item['tipe'] == 'keluar' ? $item['jumlah'] : 0;
                        $total = $total + $masuk - $keluar;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['tanggal'])->isoFormat('D MMM Y') }}</td>
                        <td class="text-left">{{ $item['keterangan'] }}</td>
                        <td>{{ $masuk > 0 ? $masuk : '-' }}</td>
                        <td>{{ $keluar > 0 ? $keluar : '-' }}</td>
                        <td>{{ $total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada transaksi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
