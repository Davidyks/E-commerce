<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  @include('layout.logo')
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <title>Login</title>
</head>
<body>
  <div class="container-fluid d-flex align-items-center justify-content-between p-0 overflow-hidden">
    <div class="left-section">
      <img id="logo" src="{{ asset('asset/images/Logo.png') }}" alt="Illustration" />
      <img id="illustration" src="{{ asset('asset/images/login-register/image1.png') }}" alt="Illustration" />
    </div>
    <div class="d-flex flex-column justify-content-center">
      <a href="{{ route('show.beforelogin') }}" class="back fw-semibold">
        @lang('messages.back')
      </a>
      <div class="form-card">
        <h2>@lang('messages.login') to BuyBuy</h2>
        <form action="{{ route('login.attempt') }}" method="POST">
          @csrf
          @if(session('danger'))
          <div class="alert alert-danger">
            {{ session('danger') }}
          </div>
          @endif
          <input type="text" class="@error('username') is-invalid @enderror" name="username" placeholder="@lang('messages.handphone')/@lang('messages.email')/@lang('messages.username')" value="{{ old('username') }}"/>
          @error('username')
          <div class="invalid-feedback" role="alert">
            {{ $message }}
          </div>
          @enderror
          <input type="password" class="@error('password') is-invalid @enderror" name="password" style="margin-top:7px" placeholder="@lang('messages.password')" value="{{ old('password') }}"/>
          @error('password')
          <div class="invalid-feedback" role="alert">
            {{ $message }}
          </div>
          @enderror
          
        <button class="login-btn" type="submit">@lang('messages.login')</button>
        
        <div class="small-text">
          @lang('messages.dont_have_acc')<a href="{{ route('register') }}" class="fw-bold">@lang('messages.sign_up')</a>
        </div>
      </form>
      
      <hr style="margin: 25px 0;" />
      
      <div class="social-btns">
        <a href="{{ route('google.redirect') }}"><img src="https://www.svgrepo.com/show/355037/google.svg" width="18" /> Google</a>
      </div>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
