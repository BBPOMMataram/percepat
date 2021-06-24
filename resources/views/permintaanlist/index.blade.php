@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    <h6>Tanggal Permintaan : {{ $data->tgl_permintaan->isoFormat('D MMM Y') }}</h6>
    @if (auth()->user()->level === 'admin' || auth()->user()->position === 'pemohon')
    <a href="{{ route('permintaanlist.create', $data->id) }}" class="btn btn-light mb-3">ADD</a>
    @endif
    <div class="table-responsive">
      <table class="table table-striped" id="dttable">
        <thead>
          <th>Actions</th>
          <th>No</th>
          <th>Nama Barang</th>
          <th>Satuan</th>
          <th>Jumlah Permintaan</th>
          <th>Jumlah Realisasi</th>
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
            url: "{{ route('dt_permintaanlist', $data->id) }}"
          },
          columns: [
            { data: 'actions', className: 'text-center' },
            { data: 'DT_RowIndex' },
            { data: 'barang.name', className: 'text-wrap' },
            { data: 'barang.satuan', className: 'text-center' },
            { data: 'jumlahpermintaan', className: 'text-center' },
            { data: 'jumlahrealisasi', className: 'text-center', render: function($data){ return $data ? $data : '-'; }},
            { data: 'keterangan', className: 'text-center', render: function($data){ return $data ? $data : '-'; }},
          ]
        })

        $('#dttable').on('click', '.delete', function(e){
          e.preventDefault();
          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['barang_id'];
          Swal.fire({
            title: 'Deletion Confirmation',
            text: 'Really to delete this item ?',
            icon: 'question',
            showCancelButton: true,
          }).then(function(val){
            if(val.isConfirmed){
              $.ajax({
                type: "delete",
                url: "{{ $data->id }}/" + id,
                data: {
                  _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                  if(response.status){
                    Swal.fire({
                      title: 'Success',
                      text: response.msg,
                      icon: 'success',
                    })
                  }
                  dttable.ajax.reload(null, false);
                }
              });
            }
          })
        })
      });
</script>
@endpush