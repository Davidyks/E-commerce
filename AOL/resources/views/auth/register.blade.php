<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="{{ asset('css/register.css') }}">
  @include('layout.logo')
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <title>Register</title>
</head>
<body>
  <div class="container-fluid d-flex align-items-center justify-content-between p-0 overflow-hidden">
    <div class="left-section">
      <img id="logo" src="{{ asset('asset/images/Logo.png') }}" alt="Illustration" />
      <img id="illustration" src="{{ asset('asset/images/login-register/image2.png') }}" alt="Illustration" />
      <div class="slogan">
          <p>BuyBuy.com</p>
          <p>Semua kebutuhanmu, satu klik saja</p>
        </div>
    </div>

    <div class="form-card">
      <h2>Register</h2>
      <form action="{{ route('register.attempt') }}" method="POST">
        @csrf
        @if(session('danger'))
          <div class="alert alert-danger">
            {{ session('danger') }}
          </div>
        @endif
        <input type="text" class="@error('identifier') is-invalid @enderror" name="identifier" placeholder="No.handphone/Email/Username" value="{{ old('identifier') }}"/>
        @error('identifier')
          <div class="invalid-feedback" role="alert">
              {{ $message }}
          </div>
        @enderror
        <input type="password" class="@error('password') is-invalid @enderror" name="password" style="margin-top:7px" placeholder="Password" value="{{ old('password') }}"/>
        @error('password')
          <div class="invalid-feedback" role="alert">
              {{ $message }}
          </div>
        @enderror
        <input type="password" class="@error('password_confirmation') is-invalid @enderror" name="password_confirmation" style="margin-top:7px" placeholder="Confirm Password" value="{{ old('password_confirmation') }}"/>
        @error('password_confirmation')
          <div class="invalid-feedback" role="alert">
              {{ $message }}
          </div>
        @enderror

        <button class="login-btn" type="submit">Create Account</button>
        
        <div class="small-text">
          Have an account? <a href="{{ route('login') }}" class="fw-bold">Login</a>
        </div>
        <div class="smaller-text">
            By registering, I agree to <span class="highlight fw-semibold">BuyBuy Terms & Conditions</span> and <span class="highlight fw-semibold">Privacy Policy</span>
        </div>
      </form>
        
      <hr style="margin: 25px 0;" />

      <div class="social-btns">
        <a href="{{ route('google.redirect') }}"><img src="https://www.svgrepo.com/show/355037/google.svg" width="18" /> Google</a>
      </div>
    </div>
  </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
