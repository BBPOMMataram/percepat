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
        <h3 id="title">DATA REAGEN<br>BALAI BESAR POM DI MATARAM</h3>
        <table>
            <thead>
                <tr style="text-align: center;">
                    <td>No</td>
                    <td>Nama</td>
                    <td>Satuan</td>
                    <td>Stok</td>
                    <td>Expired</td>
                    <td>MSDS</td>
                </tr>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name ?? '-' }}</td>
                        <td>{{ $item->satuan ?? '-' }}</td>
                        <td>{{ $item->stock ?? '-' }}</td>

                        <td style="text-align: center;">
                            @isset($item->expired)
                                {{ $item->expired->isoFormat('D/MM/YY') }}
                            @else
                                -
                            @endisset
                        </td>
                        <td>{{ $item->msds ?? '-' }}</td>
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
