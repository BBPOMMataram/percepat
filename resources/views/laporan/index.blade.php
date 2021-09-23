@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    <h6>Laporan</h6>
    <div class="table-responsive">
      <table class="table table-striped" id="dttable">
        <thead>
          <th>No</th>
          <th>Nama Barang</th>
          <th>Satuan</th>
          <th>Expired</th>
          <th>Jumlah Permintaan</th>
          <th>Jumlah Realisasi</th>
          <th>Peminta</th>
          <th>Bidang</th>
          <th>Keterangan</th>
        </thead>
      </table>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
  $(function(){
      const dttable = $('#dttable').DataTable({
          responsive: true,
          serverSide: true,
          ajax: {
            url: "{{ route('dt_laporan') }}"
          },
          order: [[1, 'asc']],
          columns: [
            { data: 'DT_RowIndex' },
            { data: 'barang.name', className: 'text-wrap' },
            { data: 'barang.satuan', className: 'text-center' },
            { data: 'barang.expired', className: 'text-center' },
            { data: 'jumlahpermintaan', className: 'text-center' },
            { data: 'jumlahrealisasi', className: 'text-center', render: function($data){ return $data ? $data : '-'; }},
            { data: 'permintaan.peminta.name', className: 'text-center'},
            { data: 'permintaan.bidang_id', className: 'text-center'},
            { data: 'keterangan', className: 'text-center', render: function($data){ return $data ? $data : '-'; }},
          ]
        })

      });
</script>
@endpush