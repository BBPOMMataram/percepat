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
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <div class="card-title">
          @isset($editeddata)
          <i class="zmdi zmdi-account"></i> Edit data
          @else
          <i class="zmdi zmdi-account-add"></i> Add a new data
          @endisset
        </div>
        <hr>
        <form id="form">
          @csrf
          @isset($editeddata)
          @method('PUT')
          @endisset
          <div class="form-group">
            <label for="bidang">Bidang atau Seksi</label>
            <input required type="text" class="form-control " id="bidang" name="bidang" placeholder="Bidang"
              value="{{ $editeddata->bidang ?? ''}}">
          </div>
          <div class="form-group">
            <label for="kabid_id">Kabid / Kasie / Penyelia</label>
            <select required name="kabid_id" id="kabid_id" class="form-control  select2">
              <option value="">==Select an item==</option>
              @foreach ($kabid as $item)
              <option @isset($editeddata) @if ($item->id === $editeddata->kabid_id) selected @endif @endisset
                value="{{ $item->id }}">{{ $item->name }} </option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
            <a href="{{ route('permintaan.index') }}"><button type="button"
                class="btn btn-secondary btn-round px-5">Exit</button></a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
  $(function(){

    $('.select2').select2();

      function ResetForm(){
        $('#bidang').val('')
        $('#kabid_id').val('').trigger('change')
      }

      $('#form').on('submit', function(e){
        let fd = new FormData($('#form')[0]);
        
        const url = "@isset($editeddata) {{ route('permintaan.update', $editeddata->id) }} @else {{ route('permintaan.store') }} @endisset"
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