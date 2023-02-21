@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <div class="card-title">
          @isset($user)
          <i class="zmdi zmdi-account"></i> Edit user
          @else
          <i class="zmdi zmdi-account-add"></i> Add a new data
          @endisset
        </div>
        <hr>
        <form id="form" enctype="multipart/form-data">
          @csrf
          @isset($editeddata)
          @method('PUT')
          @endisset
          <div class="form-group">
            <label for="name">Name</label>
            <input required type="text" class="form-control form-control-rounded" id="name" name="name"
              placeholder="Name" value="{{ $editeddata->name ?? '' }}" autofocus>
          </div>
          <div class="form-group">
            <label for="satuan">Satuan</label>
            <input required type="text" class="form-control form-control-rounded" id="satuan" name="satuan"
              placeholder="Satuan" value="{{ $editeddata->satuan ?? '' }}">
          </div>
          <div class="form-group">
            <label for="stock">Stock</label>
            <input required type="number" class="form-control form-control-rounded" id="stock" name="stock"
              placeholder="Stock" value="{{ $editeddata->stock ?? 0 }}" min="0">
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
            <a href="{{ route('atk.index') }}"><button type="button"
                class="btn btn-secondary btn-round px-5">Exit</button></a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script> --}}
<script>
  $(function(){

      function ResetForm(){
        $('#name').val('')
        $('#satuan').val('')
        $('#expired').val('')
        $('#msds').val('')
        $('#stock').val(0);
      }

      $('#form').on('submit', function(e){
        let fd = new FormData($('#form')[0]);
        
        const url = "@isset($editeddata) {{ route('atk.update', $editeddata->id) }} @else {{ route('atk.store') }} @endisset"
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
              if(method === ' POST '){
                ResetForm();
              }
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