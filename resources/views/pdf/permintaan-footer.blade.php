
<div id="footer">
    <table>
        <tr>
            <td>
                <div style="width:180px; text-align:center;">Mengetahui Atasan langsung</div>
                <div style="width:180px; text-align:center;">Ketua Tim / Penyelia</div>
            </td>
            <td style="text-align: right; padding-right: 35px;">Pemohon / Penerima <br />
            </td>
        </tr>
        <tr>
            <td>
                @if ($datapermintaan->status_id >= 2)
                @if ($kabid)
                <span>@if ($kabidSignature) <img src="{{ $kabidSignature }}"
                        alt="ttd kabid" width="150px" style="padding-left: 15px;">
                    @endif</span><br />
                <div style="width:180px; text-align:center;">{{ $kabid->name }}</div>
                @endif
                @endif
            </td>
            <td style="text-align: right; margin-right: 20px">
                @if ($datapermintaan->status_id >= 1)
                @if ($pemohon)
                <span>@if($pemohonSignature) <img src={{ $pemohonSignature }} alt="ttd pemohon"
                        width="150px">@endif</span><br />
                <div style="width:180px; margin-left:auto; text-align:center;">{{ $pemohon->name }}</div>
                @endif
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; border: 1px solid black;">Penyerahan Barang</td>
        </tr>
        <tr>
            <td>Tanggal Penyerahan : {{ $datapermintaan->tgl_penyerahan ?
                $datapermintaan->tgl_penyerahan->isoFormat('D MMM YYYY') : '' }}</td>
        </tr>
        <tr>
            <td>
                <div style="width:180px; text-align:center;">Mengetahui</div>
                <div style="width:180px; text-align:center;">Kepala Bagian Tata Usaha</div>
            </td>
            <td style="text-align: center; padding-left: 305px;">Yang menyerahkan /<br /> Petugas Gudang</td>
        </tr>
        <tr>
            <td>
                @if ($datapermintaan->status_id >= 4)
                @if ($kasub)
                <span>@if ($kasubSignature) <img src="{{ $kasubSignature }}"
                        alt="ttd kasub" width="150px" style="padding-left:15px">
                    @endif</span><br />
                <div style="width:180px; text-align:center;">{{ $kasub->name }}</div>
                @endif
                @endif
            </td>
            <td style="text-align: right; margin-right: 20px">
                {{-- MENAMPILKAN TTD PENYERAH SAAT SUDAH DISETUJUI OLEH PENYERAH, JADI TIDAK APA2 DEFAULT VALUE PENYERAH_ID PADA TABLE PERMINTAAN DIISI DULUAN BEGITU JUGA DENGAN YG LAINNYA --}}
                @if ($datapermintaan->status_id >= 3) 
                @if ($penyerah)
                <span>@if($penyerahSignature) <img src="{{ $penyerahSignature }}" alt="ttd penyerah"
                        width="150px">@endif</span><br />
                <div style="width:180px; margin-left:auto; text-align:center;">{{ $penyerah->name }}</div>
                @endif
                @endif
            </td>
        </tr>
    </table>
</div>