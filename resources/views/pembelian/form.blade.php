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
        <form id="form" enctype="multipart/form-data">
          @csrf
          @isset($editeddata)
          @method('PUT')
          @endisset
          
          <div class="form-group">
            <label for="created_at">Tanggal Pembelian</label>
            <input type="date" class="form-control " id="created_at" name="created_at" placeholder="Satuan"
              @isset($editeddata) 
              value="{{ $editeddata->created_at ? $editeddata->created_at->format('Y-m-d') : '' }}"
              @else
              value="{{ now()->format('Y-m-d') }}"
              @endisset>
          </div>
          <div class="form-group">
            <label for="barang_id">Name</label>
            <select name="barang_id" id="barang_id" class="form-control  select2" @isset ($editeddata) disabled
              @endisset>
              <option value="">==Select an item==</option>
              @foreach ($barang as $item)
              <option @isset($editeddata) @if ($item->id === $editeddata->barangs_id) selected @endif @endisset
                value="{{ $item->id }}">{{ $item->name }} (ex: @isset($item->expired)
                {{ $item->expired->isoFormat('D MMM Y') }} @endisset)</option>
              @endforeach
            </select>

            @isset($editeddata)
            <select name="barang_id" id="barang_id" class="form-control  d-none">
              <option value="">==Select an item==</option>
              @foreach ($barang as $item)
              <option @isset($editeddata) @if ($item->id === $editeddata->barangs_id) selected @endif @endisset
                value="{{ $item->id }}">{{ $item->name }} (ex: @isset($item->expired)
                {{ $item->expired->isoFormat('D MMM Y') }} @endisset)</option>
              @endforeach
            </select>
            @endisset
          </div>
          <div class="form-group">
            <label for="expired">Expired</label>
            <input type="date" class="form-control " id="expired" name="expired" placeholder="Satuan"
              @isset($editeddata) value="{{ $editeddata->expired ? $editeddata->expired->format('Y-m-d') : '' }}"
              @endisset>
          </div>
          <div class="form-group">
            <label for="jumlah">Jumlah</label>
            <input required type="number" class="form-control " id="jumlah" name="jumlah" placeholder="Jumlah"
              value="{{ $editeddata->jumlah ?? 0 }}" min="0">
          </div>
          <div class="form-group">
            <label for="vendor">Vendor</label>
            <input type="text" class="form-control " id="vendor" name="vendor" placeholder="Vendor"
              value="{{ $editeddata->vendor ?? '' }}">
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
            <a href="{{ route('pembelian.index') }}"><button type="button"
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
        $('.select2').val('').trigger('change')
        $('#vendor').val('')
        $('#expired').val('')
        $('#jumlah').val(0);
      }

      $('#form').on('submit', function(e){
        let fd = new FormData($('#form')[0]);
        
        const url = "@isset($editeddata) {{ route('pembelian.update', $editeddata->id) }} @else {{ route('pembelian.store') }} @endisset"
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