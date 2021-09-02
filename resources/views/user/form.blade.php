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
          <i class="zmdi zmdi-account-add"></i> Add a new user
          @endisset
        </div>
        @empty($user)
        <small class="text-info">* ) Default password is "password"</small>
        @endempty
        <hr>
        <form id="form-user" enctype="multipart/form-data">
          @csrf
          @isset($user)
          @method('PUT')
          @endisset
          <div class="form-group">
            <label for="name">Name</label>
            <input required type="text" class="form-control form-control-rounded" id="name" name="name"
              placeholder="Enter Your Name" value="{{ $user->name ?? '' }}" autofocus>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input required type="email" class="form-control form-control-rounded" id="email" name="email"
              placeholder="Enter Your Email Address" value="{{ $user->email ?? '' }}">
          </div>
          <div class="form-group">
            <label for="photo">Photo</label>
            <input type="file" class="form-control form-control-rounded" id="photo" name="photo">
            @isset($user->photo)
            <small class="text-warning"> * ) Choose a new file for changing your current photo</small>
            <img src="{{ Storage::url($user->photo) }}" alt="profile photo" class="w-100">
            @endisset
          </div>
          <div class="mb-3">
            <label for="signature">Signature</label> <br />
            <canvas name="signature" id="signature" class="bg-light"></canvas>
            <button type="button" id="clear-signature" class="w-25 d-block bg-light">clear</button>
            @isset($user->signature)
            <br />
            <small class="text-warning"> * ) Draw a new signature for changing your current signature</small>
            <img src="{{ Storage::url($user->signature) }}" alt="profile photo">
            @endisset
          </div>
          <div class="form-group">
            <label for="position">Position</label>
            <select name="position" id="position" class="form-control form-control-rounded">
              <option value="pemohon" @isset($user) @if($user->position === 'pemohon') selected @endif @endisset>Pemohon
              </option>
              <option value="penyelia" @isset($user) @if($user->position === 'penyelia') selected @endif @endisset>Kabid
                / Penyelia</option>
              <option value="penyerah" @isset($user) @if($user->position === 'penyerah') selected @endif
                @endisset>Petugas Gudang</option>
              <option value="kasubbagumum" @isset($user) @if($user->position === 'kasubbagumum') selected @endif
                @endisset>Ka. Sub. Bag. Umum</option>
            </select>
          </div>
          <div class="form-group">
            <label for="bidang">Bidang</label>
            <select name="bidang" id="bidang" class="form-control form-control-rounded">
              @foreach ($bidangs as $item)
              <option value="{{ $item->id }}" @if ($item->id === $user->bidang_id) selected @endif>{{ $item->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
            <a href="{{ route('users.index') }}"><button type="button"
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
      var canvas = document.querySelector("canvas");
      
      var signaturePad = new SignaturePad(canvas);

      function ResetForm(){
        $('#name').val('')
        $('#email').val('')
        $('#photo').val('')
        signaturePad.clear();
        $('#position').prop('selectedIndex', 0);
      }

      $('#form-user').on('submit', function(e){
        let fd = new FormData($('#form-user')[0]);
        if (!signaturePad.isEmpty()) {
          fd.append('signed', signaturePad.toDataURL());
        }
        
        const url = "@isset($user) {{ route('users.update', $user->id) }} @else {{ route('users.store') }} @endisset"
        const method = "@isset($user) PUT @else POST @endisset"
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

      $('#clear-signature').click(function(){
        signaturePad.clear();
      })

      updateBidang();

      function updateBidang(){
        if($('#position').val() === 'pemohon'){
          $('#bidang').attr('disabled', false);
        }else{
          $('#bidang').attr('disabled', true);
        }
      }

      $('#position').change(function (e) { 
        e.preventDefault();
        updateBidang();
      });

    })
</script>
@endpush