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

    <div class="flashsale-container">
    <div class="flashsale-header">
        <span class="flashsale-title">FlashSale</span>
        <a href="#" class="see-all">See all</a>
    </div>

    <div class="flashsale-items">
        <div class="flashsale-card">
            <div class="position-relative">
                <img src="{{ asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                <span class="flashsale-stock">10/100 left</span>
                <span class="flashsale-timer" id="flashsale-timer">01:00:00</span>
                <div class="flashsale-rate">
                    4.7 <span style="color:#ffb400; font-size: 20px;;">★</span>
                </div>
            </div>
            <p class="price">Rp. 2.030.000</p>
            <a class="restricted-btn" href="#">See Detail</a>
        </div>
        <div class="flashsale-card">
            <div class="position-relative">
                <img src="{{ asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                <span class="flashsale-stock">10/100 left</span>
                <span class="flashsale-timer" id="flashsale-timer">01:00:00</span>
                <div class="flashsale-rate">
                    4.7 <span style="color:#ffb400; font-size: 20px;;">★</span>
                </div>
            </div>
            <p class="price">Rp. 250.000</p>
            <a class="restricted-btn" href="#">See Detail</a>
        </div>
        <div class="flashsale-card">
            <div class="position-relative">
                <img src="{{ asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                <span class="flashsale-stock">10/100 left</span>
                <span class="flashsale-timer" id="flashsale-timer">01:00:00</span>
                <div class="flashsale-rate">
                    4.7 <span style="color:#ffb400; font-size: 20px;;">★</span>
                </div>
            </div>
            <p class="price">Rp. 230.000</p>
            <a class="restricted-btn" href="#">See Detail</a>
        </div>
        <div class="flashsale-card">
            <div class="position-relative">
                <img src="{{ asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                <span class="flashsale-stock">10/100 left</span>
                <span class="flashsale-timer" id="flashsale-timer">01:00:00</span>
                <div class="flashsale-rate">
                    4.7 <span style="color:#ffb400; font-size: 20px;;">★</span>
                </div>
            </div>
            <p class="price">Rp. 2.030.000</p>
            <a class="restricted-btn" href="#">See Detail</a>
        </div>
        <div class="flashsale-card">
            <div class="position-relative">
                <img src="{{ asset('asset/images/sesudah_login/shirt.jpg') }}" alt="Product">
                <span class="flashsale-stock">10/100 left</span>
                <span class="flashsale-timer" id="flashsale-timer">01:00:00</span>
                <div class="flashsale-rate">
                    4.7 <span style="color:#ffb400; font-size: 20px;;">★</span>
                </div>
            </div>
            <p class="price fw-medium">Rp. 2.030.000</p>
            <a class="restricted-btn" href="#">See Detail</a>
        </div>
    </div>
    </div>

</div>
@endsection