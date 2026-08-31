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
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-cell {
            width: 90px;
            text-align: center;
            padding: 8px;
            border: 1px solid black;
            border-bottom: none;
            vertical-align: middle;
        }
        .logo-cell img {
            width: 55px;
        }
        .meta-row {
            display: flex;
            border: 1px solid black;
            border-left: none;
            border-bottom: none;
        }
        .meta-row:last-child {
            border-bottom: 1px solid black;
        }
        .meta-label {
            width: 120px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-right: 1px solid black;
            white-space: nowrap;
        }
        .meta-value {
            padding: 4px 8px;
            font-size: 10px;
            white-space: nowrap;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 4px 0;
        }
        .info-barang {
            margin: 4px 0;
        }
        .info-barang table {
            width: 100%;
        }
        .info-barang table td {
            padding: 2px 0;
        }
        .info-barang table td:first-child {
            width: 60px;
        }
        .lokasi {
            border: 1px solid black;
            padding: 4px 8px;
            text-align: center;
            font-size: 10px;
            width: 80px;
            margin-left: auto;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .content-table th, .content-table td {
            border: 1px solid black;
            padding: 4px 6px;
            text-align: center;
        }
        .content-table th {
            font-weight: bold;
        }
        .content-table td.text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell" rowspan="4">
                <img src="storage/bpomri.png" alt="Logo BBPOM">
            </td>
        </tr>
    </table>
    <div class="meta-row">
        <span class="meta-label">Nomor Formulir</span>
        <span class="meta-value">POM-14.01CFM.01SOP.01IK.14A.02</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Tanggal Pembuatan</span>
        <span class="meta-value">27 Juni 2019</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Nomor / Tanggal Revisi</span>
        <span class="meta-value">04 / 21 Oktober 2024</span>
    </div>
    <div class="meta-row">
        <span class="meta-label">Nama Formulir</span>
        <span class="meta-value">Form Kartu Stok Persediaan</span>
    </div>

    <div class="title">KARTU STOK PERSEDIAAN</div>

    <div class="info-barang">
        <table>
            <tr>
                <td>NAMA</td>
                <td>: {{ $barang->name }}</td>
                <td style="width: 80px;"></td>
                <td><div class="lokasi">LOKASI<br>(no.)</div></td>
            </tr>
            <tr>
                <td>SATUAN</td>
                <td>: {{ $barang->satuan }} &lt; {{ $barang->code ?? '-' }} &gt;</td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    <table class="content-table">
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
</body>
</html>
