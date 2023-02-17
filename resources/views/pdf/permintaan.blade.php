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
                    <img src="storage/bpomri.png" alt="Logo" width="60px">
                </td>
                <td>Nomor Formulir</td>
                <td>POM-12.SOP.01.IK.02B(108A)/F.02</td>
            </tr>
            <tr>
                <td>Tanggal Pembuatan</td>
                <td>10 Oktober 2011</td>
            </tr>
            <tr>
                <td>Nomor / Tanggal Revisi</td>
                <td>-</td>
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
                    <td style="text-align: left;" colspan="3">Bidang atau Seksi : {{ $datapermintaan->bidang->name }}</td>
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
    <div id="footer">
        <table>
            <tr>
                <td style="padding-left: 5px;">Mengetahui Atasan langsung <br />
                    <span style="padding-left:30px;">Kabid / Penyelia</span>
                </td>
                <td style="text-align: right; padding-right: 5px;">Pemohon / Penerima <br />
                </td>
            </tr>
            <tr>
                <td>
                    @if ($datapermintaan->status_id >= 2)
                    @if ($kabid)
                    <span style="padding-left: -20px;">@if ($kabid->signature) <img src="{{ Storage::url($kabid->signature) }}" alt="ttd kabid" width="150px"> @endif</span><br />
                    <span style="margin-left: 25px;">{{ $kabid->name }}</span>
                    @endif
                    @endif
                </td>
                <td style="text-align: right; margin-right: 20px">
                    @if ($datapermintaan->status_id >= 1)
                    @if ($pemohon)
                    <span>@if($pemohon->signature) <img src="{{ Storage::url($pemohon->signature) }}" alt="ttd pemohon" width="150px">@endif</span><br />
                    <span>{{ $pemohon->name }}</span>
                    @endif
                    @endif
                </td>
            </tr>
            {{-- <tr><td><br/></td></tr> --}}
            <tr>
                <td colspan="2" style="text-align: center; border: 1px solid black;">Penyerahan Barang</td>
            </tr>
            <tr>
                <td>Tanggal Penyerahan : {{ $datapermintaan->tgl_penyerahan }}</td>
            </tr>
            <tr>
                <td style="padding-left:50px;">Mengetahui<br />
                    <span style="margin-left:-20px;">Ka. Sub. Bag. Umum</span>
                </td>
                <td style="text-align: right; padding-right: 5px;">Yang menyerahkan <br />
                </td>
            </tr>
            <tr>
                <td>
                    @if ($datapermintaan->status_id >= 4)
                    @if ($kasub)
                    <span style="padding-left: -20px;">@if ($kasub->signature) <img src="{{ Storage::url($kasub->signature) }}" alt="ttd kasub" width="150px"> @endif</span><br />
                    <span style="margin-left: 25px;">{{ $kasub->name }}</span>
                    @endif
                    @endif
                </td>
                <td style="text-align: right; margin-right: 20px">
                    @if ($datapermintaan->status_id >= 3)
                    @if ($penyerah)
                    <span>@if($penyerah->signature) <img src="{{ Storage::url($penyerah->signature) }}" alt="ttd penyerah" width="150px">@endif</span><br />
                    <span>{{ $penyerah->name }}</span>
                    @endif
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>

</html>