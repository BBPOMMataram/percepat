<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Permintaan</title>

    <style>
        body {
            font-family: Arial;
            font-size: 12px;
        }

        #header table {
            width: 100%;
            border-collapse: collapse;
        }

        #header table tr td {
            border: 1px solid black;
            padding-left: 2px;
        }

        #logo {
            text-align: center;
            width: 80px;
        }

        #content {
            text-align: center;
        }

        #content table {
            width: 100%;
            border-collapse: collapse;
        }

        #content table tr td {
            border: 1px solid black;
            padding-left: 2px;
        }

        #nourut {
            margin-bottom: 5px;
        }

        #footer {
            margin-top: 5px;
        }

        #footer table {
            width: 100%;
        }

        #footer table tr td {
            /* border: 1px solid black; */
        }
    </style>
</head>

<body>
    <div id="header">
        <table>
            <tr>
                <td rowspan="4" id="logo">
                    <img src={{ $logobpom }} alt="Logo" width="60px">
                </td>
                <td>Nomor Formulir</td>
                {{-- NOMOR LAMA --}}
                {{-- <td>POM-12.SOP.01.IK.02B(108A)/F.02</td>  --}}
                <td>POM-14.01/CFM.01/SOP.01/IK18A/F.01</td>
            </tr>
            <tr>
                <td>Tanggal Pembuatan</td>
                <td>09 Maret 2020</td>
            </tr>
            <tr>
                <td>Nomor / Tanggal Revisi</td>
                <td>02/12 Mei 2023</td>
            </tr>
            <tr>
                <td>Nama Formulir</td>
                <td>Form SPB & SBBK</td>
            </tr>
        </table>
    </div>
    <div id="content">
        <h3 id="title">SURAT PERMINTAAN BARANG (SPB) &<br>SURAT BUKTI PENGELUARAN BARANG (SBBK)</h3>

        <div id="nourut">No. Urut Permintaan: {{ $datapermintaan->nourut }}</div>
        <table>
            <thead>
                <tr>
                    <td style="text-align: left;" colspan="3">Tanggal Permintaan :
                        {{ $datapermintaan->tgl_permintaan->isoFormat('D MMMM Y') }}</td>
                    <td style="text-align: left;" colspan="3">Bidang atau Seksi :
                        {{ $datapermintaan->bidang_name_auth_external }}
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;" colspan="6">Jenis Permintaan : {{ $datapermintaan->jenis }}</td>
                </tr>
                <tr style="text-align: center;">
                    <td rowspan="2">No</td>
                    <td rowspan="2">Nama Barang</td>
                    <td rowspan="2">Satuan</td>
                    <td colspan="2">Jumlah</td>
                    <td rowspan="2">Keterangan</td>
                </tr>
                <tr style="text-align: center;">
                    <td>Permintaan</td>
                    <td>Realisasi</td>
                </tr>
                @foreach ($datapermintaanlist as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->barang->name }}</td>
                        <td>{{ $item->barang->satuan }}</td>
                        <td>{{ $item->jumlahpermintaan }}</td>
                        <td>{{ $item->jumlahrealisasi }}</td>
                        <td>{{ $item->keterangan }}</td>
                    </tr>
                @endforeach
            </thead>
        </table>
    </div>
    @include('pdf.permintaan-footer')
</body>

</html>
