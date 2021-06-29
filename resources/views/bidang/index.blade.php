@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-12">
    @if (auth()->user()->level === 'admin')
    <a href="{{ route('bidang.create') }}" class="btn btn-light mb-3">ADD</a>
    @endif
    <div class="table-responsive">
      <table class="table table-striped" id="dttable">
        <thead>
          <th>Actions</th>
          <th>No</th>
          <th>Name</th>
          <th>Kabid</th>
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
            url: "{{ route('dt_bidang') }}"
          },
          columns: [
            { data: 'actions', className: 'text-center' },
            { data: 'DT_RowIndex' },
            { data: 'name', className: 'text-wrap' },
            { data: 'user.name' },
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
                url: "bidang/" + id,
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