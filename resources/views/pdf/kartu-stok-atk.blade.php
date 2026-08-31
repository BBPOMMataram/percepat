<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Stok Persediaan - {{ $barang->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px 20px;
            background-color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td, table th {
            border: 1px solid black;
            padding: 4px 8px;
        }

        /* Header */
        #header-table {
            border-collapse: collapse;
            border: 1px solid black;
        }
        #header-table td {
            vertical-align: middle;
        }
        #logo-cell {
            width: 70px;
            height: 55px;
            text-align: center;
            vertical-align: middle;
            padding: 4px;
            position: relative;
            border-right: 1px solid black;
            border-top: none;
            border-bottom: none;
            border-left: none;
        }
        #logo-cell img {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 45px;
            height: 45px;
            margin-top: -22px;
            margin-left: -22px;
        }
        #meta-cell {
            border-left: 1px solid black;
        }
        #meta-cell table td {
            padding: 2px 8px;
            font-size: 10px;
            border: none;
            border-bottom: 1px solid black;
        }
        #meta-cell table tr:last-child td {
            border-bottom: none;
        }
        #meta-cell table td:first-child {
            width: 130px;
            font-weight: bold;
        }

        /* Title */
        #title-row td {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 4px 0;
            border: none;
        }

        /* Info Barang */
        #info-table td {
            border: none;
            padding: 2px 0;
            font-size: 11px;
        }
        #info-table .label {
            width: 60px;
        }
        #info-table .separator {
            width: 10px;
            text-align: center;
        }
        #info-table .lokasi-cell {
            text-align: right;
        }
        #lokasi-box {
            border: 1px solid black;
            padding: 4px 10px;
            text-align: center;
            font-size: 10px;
            display: inline-block;
            min-width: 80px;
        }

        /* Content Table */
        #content-table th {
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
        }
        #content-table td {
            padding: 3px 6px;
            text-align: center;
        }
        #content-table td.text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <!-- Header: Logo + Meta -->
    <table id="header-table">
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

    <!-- Title -->
    <table>
        <tr id="title-row">
            <td>KARTU STOK PERSEDIAAN</td>
        </tr>
    </table>

    <!-- Info Barang -->
    <table id="info-table">
        <tr>
            <td class="label">NAMA</td>
            <td class="separator">:</td>
            <td>{{ $barang->name }}</td>
            <td class="lokasi-cell" rowspan="2">
                <div id="lokasi-box">LOKASI<br>(no.)</div>
            </td>
        </tr>
        <tr>
            <td class="label">SATUAN</td>
            <td class="separator">:</td>
            <td>{{ $barang->satuan }} &lt; {{ $barang->code ?? '-' }} &gt;</td>
        </tr>
    </table>

    <!-- Content Table -->
    <table id="content-table">
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