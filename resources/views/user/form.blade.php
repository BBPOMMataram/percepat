@extends('layout.app')
@section('content')
@parent
<div class="row mt-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <div class="card-title"><i class="zmdi zmdi-account-add"></i> Add a new user</div>
        <small class="text-info">Default password is "password"</small>
        <hr>
        <form id="form-user" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label for="name">Name</label>
            <input required type="text" class="form-control form-control-rounded" id="name" name="name"
              placeholder="Enter Your Name" autofocus>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input required type="email" class="form-control form-control-rounded" id="email" name="email"
              placeholder="Enter Your Email Address">
          </div>
          <div class="form-group">
            <label for="photo">Photo</label>
            <input type="file" class="form-control form-control-rounded" id="photo" name="photo">
          </div>
          <div>
            <label for="signature">Signature</label> <br />
            <canvas name="signature" id="signature" class="bg-light"></canvas>
            <button type="button" id="clear-signature" class="w-25 d-block bg-light">clear</button>
          </div>
          <div class="form-group">
            <label for="position">Position</label>
            <select name="position" id="position" class="form-control form-control-rounded">
              <option value="penerima">Penerima</option>
              <option value="penyelia">Penyelia</option>
              <option value="penyerah">Penyerah</option>
              <option value="kasubbagumum">Ka. Sub. Bag. Umum</option>
            </select>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-light btn-round px-5">Submit</button>
            <a href="{{ route('users.index') }}"><button type="button" class="btn btn-secondary btn-round px-5">Cancel</button></a>
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
        fd.append('signed', signaturePad.toDataURL());

        $.ajax({
          type: "post",
          url: "{{ route('users.store') }}",
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
              ResetForm();
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