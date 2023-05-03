<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan</title>

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
            /* text-align: center; */
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

        #title {
            margin-top: 0;
            text-align: center;
        }

        #tgl {
            /* text-align: left; */
            margin: 10px;
        }
    </style>
</head>

<body>
    <div id="header">
        <img src="storage/bpomri.png" alt="Logo" width="60px">

    </div>
    <div id="content">
        <h3 id="title">LAPORAN PERMINTAAN BARANG <br>BALAI BESAR POM DI MATARAM</h3>
        <table>
            <thead>
                <tr style="text-align: center;">
                    <td rowspan="2">No</td>
                    <td rowspan="2">Nama Barang</td>
                    <td rowspan="2">Satuan</td>
                    <td rowspan="2">Expired</td>
                    <td colspan="2">Jumlah</td>
                    <td rowspan="2">Peminta</td>
                    <td rowspan="2">Bidang</td>
                    <td rowspan="2">Status</td>
                    <td rowspan="2">Tgl Permintaan</td>
                    <td rowspan="2">Tgl Penyerahan</td>
                    <td rowspan="2">Ket</td>
                </tr>
                <tr style="text-align: center;">
                    <td>Permintaan</td>
                    <td>Realisasi</td>
                </tr>
                @foreach ($datapermintaanlist as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->barang->name ?? '-'}}</td>
                    <td>{{ $item->barang->satuan ?? '-'}}</td>

                    <td style="text-align: center;">
                        @isset ($item->barang->expired)
                        {{ $item->barang->expired->isoFormat('D/MM/YY') }}
                        @else
                        -
                        @endisset
                    </td>
                    <td style="text-align: center;">{{ $item->jumlahpermintaan }}</td>
                    <td style="text-align: center;">{{ $item->jumlahrealisasi }}</td>
                    <td>{{ $item->permintaan->peminta->name ?? '-' }}</td>
                    <td>{{ $item->permintaan->bidang->name ?? '-' }}</td>
                    <td>{{ $item->permintaan->status->name ?? '-' }}</td>
                    <td style="text-align: center;">
                        @isset($item->permintaan->tgl_permintaan)
                        {{ $item->permintaan->tgl_permintaan->isoFormat('D/MM/YY')}}
                        @else
                        -
                        @endisset
                    </td>
                    <td style="text-align: center;">
                        @isset($item->permintaan->tgl_penyerahan)
                        {{ $item->permintaan->tgl_penyerahan }}
                        @else
                        -
                        @endisset
                    </td>
                    <td style="text-align: center;">{{ $item->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </thead>
        </table>
    </div>
    <div id="footer">
        <div id="tgl">Mataram, {{ now()->isoFormat('D MMMM Y') }}</div>
    </div>
</body>

</html>