@extends('layout.app')
@section('content')
@push('styles')
<style>
  .select2-container--default .select2-selection--single {
    background-color: rgba(255, 255, 255, 0.2) !important;
    margin-bottom: 10px;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #fff !important;
  }

  .select2-search {
    background-color: #447EB3;
  }

  .select2-search input {
    background-color: #447EB3;
  }

  .select2-results {
    background-color: #447EB3;
  }
</style>
@endpush
@parent
<div class="row mt-3">
  <div class="col-12">
    <select name="barang_id" id="barang_id" class="select2 mb-2">
      <option value="0">All</option>
      @foreach ($barang as $item)
      <option value="{{ $item->id }}">{{ $item->name }}</option>
      @endforeach
    </select>
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

  <a href="{{ route('print_laporan') }}" title="Export PDF" target="_blank" id="exportPdf"><button
      class="btn btn-secondary mt-3 ml-3">Export PDF <i class="zmdi zmdi-print"></i></button></a>

</div>
@endsection
@push('scripts')
<script>
  $(function(){
    $('.select2').select2();

    $('#barang_id').change(function (e) { 
      e.preventDefault();
      $barangId = $(this).val();
      
      if($barangId != 0){
      dttable.column(12).search($barangId).draw();
      }else{
        dttable.search('').columns('').search('').draw();
      }

      $('#exportPdf').attr('href', '/print-laporan/' + $barangId);

    });

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
          {data: 'barang_id', visible: false}
        ]
      });

    });
</script>
@endpush