<div>
    Kepada, {{ $kepada }}
</div>
<div>
    <br />
    Mohon untuk
    @if ($datapermintaan->status_id === 1)
    diverifikasi
    @elseif($datapermintaan->status_id === 2)
    diterima
    @elseif($datapermintaan->status_id === 3)
    disetujui
    @endif
    permintaan reagen dengan data sbb : <br />
    Pemohon : {{ $datapermintaan->peminta->name }} <br />

</div>
<div>
    Data Barang Reagen :
    <ol>
        @foreach ($databarang as $item)
        <li>{{ $item->barang->name . ': ' . $item->jumlahpermintaan . '. Ket : ' .$item->keterangan}}</li>
        @endforeach
    </ol>
</div>

<div>
    Silahkan klik url berikut untuk melihat detail data, terima kasih.
    {{ url('permintaan/'. $datapermintaan->id) }}
</div>