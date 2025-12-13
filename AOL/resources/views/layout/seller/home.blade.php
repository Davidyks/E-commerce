@extends('layout.seller.master')

@section('title', 'Test Layout Seller')

@section('content')
<div class="container py-5">
    <div class="alert alert-info">
        <h3 class="fw-bold"><i class="bi bi-check-circle-fill"></i> Layout Check</h3>
        <p>
            Jika Anda melihat <strong>Header Merah Seller</strong> di atas dan 
            <strong>Footer</strong> di bawah, berarti <code>master.blade.php</code> Anda sudah berfungsi dengan benar!
        </p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Konten Dummy</h5>
            <p>Ini adalah area konten yang akan berubah-ubah di setiap halaman.</p>
        </div>
    </div>
</div>
@endsection