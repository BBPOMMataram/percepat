@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    <div class="table-responsive">
      
      <table class="table table-striped" id="dttable">
        <thead>
          <th>No</th>
          <th>Nama Barang</th>
          <th>Satuan</th>
          <th>Expired</th>
          <th>Jml Permintaan</th>
          <th>Jml Realisasi</th>
          <th>Peminta</th>
          <th>Bidang</th>
          <th>Status</th>
          <th>Tgl Permintaan</th>
          <th>Tgl Penyerahan</th>
          <th>Keterangan</th>
        </thead>
      </table>
    </div>
  </div>
  
  <a href="{{ route('print_laporan') }}" title="Export PDF" target="_blank" ><button class="btn btn-secondary mt-3 ml-3">Export PDF <i class="zmdi zmdi-print"></i></button></a>
  
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
            { data: 'permintaan.bidang.name'},
            { data: 'permintaan.status.name'},
            { data: 'permintaan.tgl_permintaan', render: function($data){ return $data ? $data : '-'; }},
            { data: 'permintaan.tgl_penyerahan', render: function($data){ return $data ? $data : '-'; }},
            { data: 'keterangan', render: function($data){ return $data ? $data : '-'; }},
          ]
        })

      });
</script>
@endpush