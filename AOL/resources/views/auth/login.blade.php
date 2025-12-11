<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <title>Login</title>
</head>
<body>
  <div class="container-fluid d-flex align-items-center justify-content-between p-0 overflow-hidden">
    <div class="left-section">
      <img id="logo" src="{{ asset('asset/images/Logo.png') }}" alt="Illustration" />
      <img id="illustration" src="{{ asset('asset/images/login/image1.png') }}" alt="Illustration" />
    </div>

    <div class="form-card">
      <h2>Login to BuyBuy</h2>
      <form action="{{ route('login.attempt') }}" method="POST">
        @csrf
        @if(session('danger'))
          <div class="alert alert-danger">
            {{ session('danger') }}
          </div>
        @endif
        <input type="text" class="@error('username') is-invalid @enderror" name="username" placeholder="No.handphone/Email/Username" value="{{ old('username') }}"/>
        @error('username')
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

        <button class="login-btn" type="submit">Login</button>
        
        <div class="small-text">
          Don't have an account? <a href="#">Sign Up</a>
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
