@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    <a href="{{ route('permintaan.create') }}" class="btn btn-light mb-3">ADD</a>
    <div class="table-responsive">
      <table class="table table-striped" id="dttable">
        <thead>
          <th>Actions</th>
          <th>No</th>
          <th>Tanggal Permintaan</th>
          <th>Peminta</th>
          <th>Bidang</th>
          <th>Kabid / Penyelia</th>
          <th>Status</th>
          <th>Tanggal Penyerahan</th>
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
          ordering: false,
          ajax: {
            url: "{{ route('dt_permintaan') }}"
          },
          columns: [
            { data: 'actions', className: 'text-center' },
            { data: 'nourut' },
            { data: 'tgl_permintaan' },
            { data: 'namapeminta' },
            { data: 'bidang.name' },
            { data: 'bidang.user.name' },
            { data: 'status.name' },
            { data: 'tgl_penyerahan', render: function($data){ return $data ? $data : '-' ;}   },
            { data: 'id', visible: false },
          ]
        });

        $('#dttable').on('click', '.kabidacc', function(e){
          e.preventDefault();
          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['id'];
          
            $.ajax({
              type: "PATCH",
              url: "kabidaccpermintaan/" + id,
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
        });

        $('#dttable').on('click', '.penyerahacc', function(e){
          e.preventDefault();
          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['id'];
          
            $.ajax({
              type: "PATCH",
              url: "penyerahaccpermintaan/" + id,
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
        });

        $('#dttable').on('click', '.kasubbagumumacc', function(e){
          e.preventDefault();
          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['id'];
          
            $.ajax({
              type: "PATCH",
              url: "kasubbagumumaccpermintaan/" + id,
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
        });

      });
</script>
@endpush