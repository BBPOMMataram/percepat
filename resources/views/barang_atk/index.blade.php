@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    @if (auth()->user()->level === 'admin')
    <a href="{{ route('atk.create') }}" class="btn btn-light mb-3">ADD</a>
    @endif
    <div class="table-responsive">
      <table class="table table-striped" id="dttable">
        <thead>
          <th>Actions</th>
          <th>No</th>
          <th>Name</th>
          <th>Satuan</th>
          <th>Stock</th>
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
          order: [[2, 'asc']],
          ajax: {
            url: "{{ route('dt_barang_atk') }}"
          },
          columns: [
            { data: 'actions', className: 'text-center' },
            { data: 'DT_RowIndex' },
            { data: 'name', className: 'text-wrap' },
            { data: 'satuan' },
            { data: 'stock' },
            { data: 'id', visible: false}
          ]
        })

        $('#dttable').on('click', '.delete', function(e){
          e.preventDefault();
          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['id'];
          Swal.fire({
            title: 'Deletion Confirmation',
            text: 'Really to delete this item ?',
            icon: 'question',
            showCancelButton: true,
          }).then(function(val){
            if(val.isConfirmed){
              $.ajax({
                type: "delete",
                url: "atk/" + id,
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