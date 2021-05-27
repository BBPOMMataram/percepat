@extends('layout.app')
@section('content')
@parent
@if ($errors->any())
    <div class="alert alert-danger p-3">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@elseif (session('msg'))
<div class="alert alert-success p-3">
  {{ session('msg') }}
</div>
@endif
<div class="row mt-3">
  <div class="col-lg-4">
    <div class="card profile-card-2">
      <div class="card-img-block">
        <img class="img-fluid" src="{{ Storage::url('cover-profile.jpg') }}" alt="Card image cap">
      </div>
      <div class="card-body pt-5">
        <img
          src="{{ auth()->user()->photo ? Storage::url(auth()->user()->photo) : 'https://via.placeholder.com/110x110' }}"
          alt="profile-image" class="profile">
        <h5 class="card-title">{{ auth()->user()->name }}</h5>
        {{-- <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
          <div class="icon-block">
            <a href="javascript:void();"><i class="fa fa-facebook bg-facebook text-white"></i></a>
    <a href="javascript:void();"> <i class="fa fa-twitter bg-twitter text-white"></i></a>
    <a href="javascript:void();"> <i class="fa fa-google-plus bg-google-plus text-white"></i></a>
          </div> --}}
      </div>

      <div class="card-body border-top border-light">
        <div class="media align-items-center text-capitalize">
          {{ auth()->user()->position ?? '-' }}
        </div>

      </div>
    </div>

  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-primary top-icon nav-justified">
          <li class="nav-item">
            <a href="javascript:void();" data-target="#profile" data-toggle="pill" class="nav-link active"><i
                class="icon-user"></i> <span class="hidden-xs">Profile</span></a>
          </li>
          <li class="nav-item">
            <a href="javascript:void();" data-target="#edit" data-toggle="pill" class="nav-link"><i
                class="icon-note"></i> <span class="hidden-xs">Edit</span></a>
          </li>
        </ul>
        <div class="tab-content p-3">
          <div class="tab-pane active" id="profile">
            <h5 class="mb-3">User Profile</h5>
            <div class="row">
              <div class="col-md-6">
                <h6>Name</h6>
                <p>
                  {{ auth()->user()->name ?? '-' }}
                </p>
                <h6>Email</h6>
                <p>
                  {{ auth()->user()->email ?? '-' }}
                </p>
                <h6>Position</h6>
                <p>
                  {{ auth()->user()->position ?? '-' }}
                </p>
                <h6>Signature</h6>
                @if (auth()->user()->signature)
                  <img src="{{ Storage::url(auth()->user()->signature) }}" alt="signature">
                @else
                <p class="text-danger">Not available</p>
                @endif
              </div>
            </div>
            <!--/row-->
          </div>
          <div class="tab-pane" id="edit">
            <form action="{{ route('profile.update', auth()->user()->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="form-group row">
                <label class="col-lg-3 col-form-label form-control-label">Name</label>
                <div class="col-lg-9">
                  <input class="form-control" name="name" type="text" value="{{ old('name', auth()->user()->name) }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-3 col-form-label form-control-label">Email</label>
                <div class="col-lg-9">
                  <input class="form-control" name="email" type="email" value="{{ old('email', auth()->user()->email) }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-3 col-form-label form-control-label">Change photo</label>
                <div class="col-lg-9">
                  <input class="form-control" type="file" name="photo">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-3 col-form-label form-control-label">Change Password</label>
                <div class="col-lg-9">
                  <input class="form-control" type="password" name="password">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-3 col-form-label form-control-label">Confirm password</label>
                <div class="col-lg-9">
                  <input class="form-control" type="password" name="confirmpassword">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-lg-3 col-form-label form-control-label"></label>
                <div class="col-lg-9">
                  <input type="reset" class="btn btn-secondary" value="Cancel">
                  <input type="submit" class="btn btn-primary" value="Save Changes">
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection