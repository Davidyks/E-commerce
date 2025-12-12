@extends('layout.sesudah_login.master') 
@section('title', 'Home')
@section('css', 'css/home_after.css')
@section('content')
<div class="container-fluid">
    <div class="mx-auto py-4 rounded shadow-sm" style="background: #fff; width: 95%;">
        <h4 class="fw-bold mb-0 ms-4">
            Welcome, 
            <span style="color: #e63939">
                {{ Auth::user()->name ?? Auth::user()->username ?? explode('@', Auth::user()->email)[0] }}
            </span>
        </h4>
    </div>

    <div class="category-container">
        <div class="category-title">Category</div>
        <div class="category-list">
            @foreach ($categories as $c)
                <div class="category-item">
                <img src={{ $c->category_image }} />
                <div>{{ $c->category_name }}</div>  
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection