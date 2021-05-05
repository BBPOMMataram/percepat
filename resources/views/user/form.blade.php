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
          @method('PUT')
          <div class="form-group">
            <label for="name">Name</label>
            <input required type="text" class="form-control form-control-rounded" id="name" name="name"
              placeholder="Enter Your Name" value="{{ $user->name ?? '' }}" autofocus>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input required type="email" class="form-control form-control-rounded" id="email" name="email"
              placeholder="Enter Your Email Address"  value="{{ $user->email ?? '' }}">
          </div>
          <div class="form-group">
            <label for="photo">Photo</label>
            <input type="file" class="form-control form-control-rounded" id="photo" name="photo">
            @isset($user->photo)
            <small class="text-warning"> * ) Choose a new file for changing your current photo</small>
            <img src="{{ Storage::url($user->photo) }}" alt="profile photo" class="w-100">
            @endisset
          </div>
          <div>
            <label for="signature">Signature</label> <br />
            <canvas name="signature" id="signature" class="bg-light"></canvas>
            @isset($user->signature)
            <br />
            <small class="text-warning"> * ) Draw a new signature for changing your current signature</small>
            <img src="{{ Storage::url($user->signature) }}" alt="profile photo">
            @endisset
            <button type="button" id="clear-signature" class="w-25 d-block bg-light">clear</button>
          </div>
          <div class="form-group">
            <label for="position">Position</label>
            <select name="position" id="position" class="form-control form-control-rounded">
              <option value="penerima" @isset($user) @if($user->position === 'penerima') selected @endif @endisset>Penerima</option>
              <option value="penyelia" @isset($user) @if($user->position === 'penyelia') selected @endif @endisset>Penyelia</option>
              <option value="penyerah" @isset($user) @if($user->position === 'penyerah') selected @endif @endisset>Penyerah</option>
              <option value="kasubbagumum" @isset($user) @if($user->position === 'kasubbagumum') selected @endif @endisset>Ka. Sub. Bag. Umum</option>
            </select>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
            <a href="{{ route('users.index') }}"><button type="button"
                class="btn btn-secondary btn-round px-5">Cancel</button></a>
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

        const url = "{{ $user ? route('users.update', $user->id) : route('users.store') }}"
        const method = "{{ $user ? 'PUT' : 'POST' }}"

        if(method === 'PUT'){
          fd.append('_token', "{{ csrf_token() }}");
        }
        
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
              if(method === 'POST'){
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

    })
</script>
@endpush