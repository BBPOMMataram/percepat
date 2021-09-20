@extends('layout.app')
@push('styles')
<style>
  .select2 {
    width: 100% !important;
  }

  .select2-container--default .select2-selection--single {
    background-color: rgba(255, 255, 255, 0.2) !important;
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
@section('content')
@parent
<div class="row mt-3">
  <div class="col">
    <div class="card">
      <div class="card-body">
        <div class="card-title">
          @isset($editeddata)
          <i class="zmdi zmdi-account"></i> Edit data
          @else
          <i class="zmdi zmdi-account-add"></i> Tambah data barang
          @endisset
        </div>
        <hr>
        @isset($editeddata)
        <form id="form">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="barang_id">Name</label>
            <select name="barang_id" id="barang_id" class="form-control  select2" disabled>
              <option value="">==Select an item==</option>
              @foreach ($barang as $item)
              <option @if ($item->id === $editeddata->barang_id) selected @endif
                value="{{ $item->id }}">{{ $item->name }} || ex: @isset($item->expired)
        {{ $item->expired->isoFormat('D MMM Y') }} @else - @endisset || stock: {{ $item->stock ?? '-' }}</option>
        @endforeach
        </select>
      </div>
      <div class="form-group">
        <label for="jumlahpermintaan">Jumlah Permintaan</label>
        <input required type="number" class="form-control " id="jumlahpermintaan" name="jumlahpermintaan"
          placeholder="Jumlah" value="{{ $editeddata->jumlahpermintaan ?? 0 }}" min="0" readonly>
      </div>
      <div class="form-group">
        <label for="jumlahrealisasi">Jumlah Realisasi</label>
        <input required type="number" class="form-control " id="jumlahrealisasi" name="jumlahrealisasi"
          placeholder="Jumlah" value="{{ $editeddata->jumlahpermintaan ?? 0 }}" min="0">
      </div>
      <div class="form-group">
        <label for="keterangan">Keterangan</label>
        <input type="text" class="form-control " id="keterangan" name="keterangan" placeholder="Keterangan"
          value="{{ $editeddata->keterangan ?? '' }}" readonly>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
        <a href="{{ route('permintaanlist.done', $data->id) }}"><button type="button"
            class="btn btn-secondary btn-round px-5">Done</button></a>
      </div>
      </form>
      @else
      <div class="table-responsive">
        <table class="table table-striped" id="dttable">
          <thead>
            <th>No</th>
            <th>Name</th>
            <th>Satuan</th>
            <th>Expired</th>
            <th>Stock</th>
            <th>Jumlah Permintaan</th>
            <th>-</th>
          </thead>
        </table>
      </div>
      <a href="{{ route('permintaanlist.index', $data->id) }}"><button type="button"
          class="btn btn-secondary px-5 mt-2">Done</button></a>
      @endisset
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
            url: "{{ route('dt_barang') }}"
          },
          columns: [
            { data: 'DT_RowIndex' },
            { data: 'name', className: 'text-wrap' },
            { data: 'satuan' },
            { data: 'expired', render: function($data){ return $data ? $data : '-' ;}  },
            { data: 'stock' },
            { data: 'jumlahpermintaan', className: 'text-center'},
            { data: 'addBtn', className: 'text-center'},
            { data: 'id', visible: false }
          ]
        })

    // $('.select2').select2();

    //   function ResetForm(){
    //     $('#barang_id').val('').trigger('change')
    //     $('#jumlahpermintaan').val(0);
    //     $('#keterangan').val('')
    //   }

    $('#form').on('submit', function(e){
        let fd = new FormData($('#form')[0]);
        
        const url = "@isset($editeddata) {{ route('permintaanlist.update', [$editeddata->permintaan_id, $editeddata->barang_id]) }} @endisset"
        const method = "@isset($editeddata) PUT @else POST @endisset"

        $.ajax({
          type: 'post',
          url: url,
          data: fd,
          cache: false,
          processData: false,
          contentType: false,
          success: function (response) {
            if(response.status){
              Swal.fire({
                title: 'Success',
                text: response.msg,
                icon: 'success'
              })
            }
          },
          error: function(err){
            if(err.status == 422){
              console.log(err.responseJSON);
              let errMsg = '';
              $.each(err.responseJSON.errors, function (indexInArray, valueOfElement) {
                $.each(valueOfElement, function (indexInArray, valueOfElement) { 
                  errMsg += '<li class="text-left">' + valueOfElement + '</li>';
                });
              });
              Swal.fire({
                title: err.responseJSON.message,
                html: '<ul>' + errMsg + '</ul>',
              })
            }
          }
        });
        e.preventDefault();
      });


      $('#dttable').on('click','.add', function(e){
          e.preventDefault();
          let jumlahpermintaan = 0;
          const index = dttable.row($(this).parents('tr')).index();

          const rowData = dttable.row($(this).parents('tr')).data();
          const id = rowData['id'];

          dttable.row().every(function(){
            jumlahpermintaan = $(dttable.cell(index, 5).node()).find('input').val();
          });

        const url = "{{ route('permintaanlist.store', $data->id) }}"

        $.ajax({
          type: 'post',
          url: url,
          data: {
            'barang_id' : id,
            'jumlahpermintaan' : jumlahpermintaan,
            '_token': '{{ csrf_token() }}',
          },
          cache: false,
          dataType: 'json',
          // processData: false,
          // contentType: false,
          success: function (response) {
            if(response.status){
              Swal.fire({
                title: 'Success',
                text: response.msg,
                icon: 'success'
              })
            }
          },
          error: function(err){
            if(err.status == 422){
              console.log(err.responseJSON);
              let errMsg = '';
              $.each(err.responseJSON.errors, function (indexInArray, valueOfElement) {
                $.each(valueOfElement, function (indexInArray, valueOfElement) { 
                  errMsg += '<li class="text-left">' + valueOfElement + '</li>';
                });
              });
              Swal.fire({
                title: err.responseJSON.message,
                html: '<ul>' + errMsg + '</ul>',
              })
            }
          }
        });
        e.preventDefault();
      });

    })
</script>
@endpush