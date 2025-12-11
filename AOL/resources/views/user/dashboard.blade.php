@extends('layout.sesudah_login.master') 

@section('content')
<div class="container mt-5">
    <h1>Selamat Datang, {{ Auth::user()->name ?? Auth::user()->username ?? explode('@', Auth::user()->email)[0] }}!</h1>
    <p>Ini adalah halaman setelah login menggunakan template baru.</p>
</div>
@endsection