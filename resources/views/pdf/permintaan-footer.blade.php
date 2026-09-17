<div id="footer">
    <table>
        <tr>
            <td style="width: 25%;">
                <div style="width:100%; text-align:center;">Mengetahui Atasan langsung</div>
                <div style="width:100%; text-align:center;">Ketua Tim / Penyelia</div>
            </td>
            <td style="width: 25%;">
                <div style="width:100%; text-align:center;">Pemohon / Penerima</div>
            </td>
            <td style="width: 25%;">
                <div style="width:100%; text-align:center;">Mengetahui</div>
                <div style="width:100%; text-align:center;">Kepala Bagian Tata Usaha</div>
            </td>
            <td style="width: 25%;">
                <div style="width:100%; text-align:center;">Yang menyerahkan /</div>
                <div style="width:100%; text-align:center;">Petugas Gudang</div>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; height: 60px;">
                @if ($datapermintaan->status_id >= 2)
                    @if ($kabid)
                        @if ($kabidSignature ?? null)
                            <img src="{{ $kabidSignature }}" alt="ttd kabid" width="80px" style="padding-left: 15px;">
                        @endif
                    @endif
                @endif
            </td>
            <td style="text-align: center; height: 60px;">
                @if ($datapermintaan->status_id >= 1)
                    @if ($pemohon)
                        @if ($pemohonSignature ?? null)
                            <img src="{{ $pemohonSignature }}" alt="ttd pemohon" width="80px">
                        @endif
                    @endif
                @endif
            </td>
            <td style="text-align: center; height: 60px;">
                @if ($datapermintaan->status_id >= 4)
                    @if ($kasub)
                        @if ($kasubSignature ?? null)
                            <img src="{{ $kasubSignature }}" alt="ttd kasub" width="80px" style="padding-left: 15px;">
                        @endif
                    @endif
                @endif
            </td>
            <td style="text-align: center; height: 60px;">
                @if ($datapermintaan->status_id >= 3)
                    @if ($penyerah)
                        @if ($penyerahSignature ?? null)
                            <img src="{{ $penyerahSignature }}" alt="ttd penyerah" width="80px">
                        @endif
                    @endif
                @endif
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">
                {{ $kabid?->name ?? '' }}
            </td>
            <td style="text-align: center;">
                {{ $pemohon?->name ?? '' }}
            </td>
            <td style="text-align: center;">
                {{ $kasub?->name ?? '' }}
            </td>
            <td style="text-align: center;">
                {{ $penyerah?->name ?? '' }}
            </td>
        </tr>
    </table>
    <br />
    <table style="width: 100%;">
        <tr>
            <td colspan="4" style="text-align: center; border: 1px solid black; padding: 5px;">Penyerahan Barang</td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: left;">Tanggal Penyerahan :
                {{ $datapermintaan->tgl_penyerahan ? $datapermintaan->tgl_penyerahan->isoFormat('D MMM YYYY') : '' }}
            </td>
        </tr>
    </table>
</div>
