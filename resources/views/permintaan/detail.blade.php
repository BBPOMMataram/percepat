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
      <div class="card-body data">
        <div class="card-title">
          <i class="zmdi zmdi-account"></i> Detail Data
        </div>
        <hr>
          <div class="form-group">
            <label for="tgl_permintaan">Tanggal permintaan</label>
            <input required type="text" class="form-control " id="tgl_permintaan" name="tgl_permintaan" placeholder="tgl_permintaan"
              value="{{ $data->tgl_permintaan->isoFormat('D MMMM Y')}}" readonly>
          </div>
          <div class="form-group">
            <label for="status">Status</label>
            <input required type="text" class="form-control " id="status" name="status" placeholder="status"
              value="{{ $data->status->name}}" readonly>
          </div>
          <div class="form-group">
            <label for="pemohon">Pemohon</label>
            <input required type="text" class="form-control " id="pemohon" name="pemohon" placeholder="pemohon"
              value="{{ $data->peminta->name}}" readonly>
          </div>
          <div class="form-group">
            <label for="bidang">Bidang atau Seksi</label>
            <input required type="text" class="form-control " id="bidang" name="bidang" placeholder="Bidang"
              value="{{ $data->bidang->name ?? ''}}" readonly>
          </div>
          <div class="form-group">
            <label for="kabid_id">Kabid / Kasie / Penyelia</label>
            <input required type="text" class="form-control " id="kabid" name="kabid" placeholder="kabid"
              value="{{ $data->bidang->user->name ?? ''}}" readonly>
          </div>
          <div class="form-group">
            <a href="{{ route('permintaanlist.index', $data->id) }}" target="_blank"><button type="button"
              class="btn btn-success btn-round px-5">List Barang</button></a>
              @if (auth()->user()->position != 'pemohon')
              <button type="button" class="@if (auth()->user()->position === 'penyelia') kabidacc @elseif(auth()->user()->position === 'penyerah') penyerahacc @elseif (auth()->user()->position === 'kasubbagumum') kasubbagumumacc @endif btn btn-light btn-round px-5">ACC</button>
              @endif
            <a href="{{ route('permintaan.index') }}"><button type="button"
                class="btn btn-secondary btn-round px-5">Exit</button></a>
          </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>
  $(function(){

    $('.data').on('click', '.kabidacc', function(e){
          e.preventDefault();
          const id = '{{ $data->id }}';
          
            $.ajax({
              type: "PATCH",
              url: "/kabidaccpermintaan/" + id,
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
                location.reload();
              }
            });
        });

        $('.data').on('click', '.penyerahacc', function(e){
          e.preventDefault();
          const id = '{{ $data->id }}';          
          
            $.ajax({
              type: "PATCH",
              url: "/penyerahaccpermintaan/" + id,
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
                  location.reload();
              }
            });
        });

        $('.data').on('click', '.kasubbagumumacc', function(e){
          e.preventDefault();
          const id = '{{ $data->id }}';
          
            $.ajax({
              type: "PATCH",
              url: "/kasubbagumumaccpermintaan/" + id,
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
                location.reload();
              }
            });
        });

    })
</script>
@endpush