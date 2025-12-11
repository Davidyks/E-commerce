@extends('layout.sebelum_login.master')
@section('title', 'Home')
@section('css', 'css/home_before.css')
@section('content')
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
@endsection