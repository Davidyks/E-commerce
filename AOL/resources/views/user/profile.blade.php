@extends('layout.sesudah_login.master')

@section('content')
<style>
    /* Custom Style untuk Input */
    .form-control-custom {
        background-color: #E0E0E0;
        border: none;
        border-radius: 10px;
        padding: 12px 20px;
        font-size: 16px;
        color: #555;
    }
    .form-control-custom:focus {
        background-color: #d6d6d6;
        box-shadow: none;
        outline: none;
    }
    /* Style tombol edit foto */
    .edit-icon {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 8px;
        cursor: pointer;
        border: 3px solid white;
    }
</style>

<div class="container d-flex justify-content-center align-items-center my-5">
    <div class="card shadow-lg p-4" style="width: 500px; border-radius: 20px; border: none;">
        
        <h4 class="text-center text-danger fw-bold mb-4">Personal Details</h4>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="d-flex justify-content-center mb-4 position-relative">
                <div style="position: relative; width: 120px; height: 120px;">
                    <img src="{{ Auth::user()->profile_picture ? asset('storage/'.Auth::user()->profile_picture) : asset('asset/images/default-avatar.png') }}" 
                         class="rounded-circle w-100 h-100" 
                         style="object-fit: cover; border: 4px solid #f0f0f0;"
                         id="preview-image">
                    
                    <label for="upload-photo" class="edit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z"/>
                        </svg>
                    </label>
                    <input type="file" name="profile_picture" id="upload-photo" class="d-none" onchange="previewImage()">
                </div>
            </div>

            <div class="mb-3">
                <input type="text" class="form-control form-control-custom" name="name" 
                       placeholder="Display Name" value="{{ old('name', Auth::user()->name) }}">
            </div>

            <div class="mb-3">
                <input type="text" class="form-control form-control-custom" name="address" 
                       placeholder="Address" value="{{ old('address', Auth::user()->address) }}">
            </div>

            <hr class="my-4 text-muted" style="width: 50%; margin: auto;">

            <div class="mb-3">
                <input type="email" class="form-control form-control-custom" name="email" 
                       placeholder="Email" value="{{ old('email', Auth::user()->email) }}">
            </div>

            <div class="mb-3">
                <input type="password" class="form-control form-control-custom" name="password" 
                       placeholder="Password (Isi jika ingin mengganti)">
            </div>

            <div class="mb-4">
                <input type="text" class="form-control form-control-custom" name="phone_number" 
                       placeholder="Phone Number" value="{{ old('phone_number', Auth::user()->phone_number) }}">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-danger py-2 fw-bold" style="border-radius: 10px; background-color: #e63939;">
                    Save
                </button>
            </div>
            
        </form> <div class="d-grid mt-3">
            <button type="button" class="btn btn-outline-secondary py-2 fw-bold" 
                    style="border-radius: 10px;" 
                    data-bs-toggle="modal" 
                    data-bs-target="#logoutModal"> Logout
            </button>
        </div>

    </div>
</div>

<script>
    function previewImage() {
        const image = document.querySelector('#upload-photo');
        const imgPreview = document.querySelector('#preview-image');

        if(image.files && image.files[0]){
            const oFReader = new FileReader();
            oFReader.readAsDataURL(image.files[0]);

            oFReader.onload = function(oFREvent) {
                imgPreview.src = oFREvent.target.result;
            }
        }
    }
</script>

@if(session('success'))
    <script>alert("{{ session('success') }}");</script>
@endif

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="logoutModalLabel">Konfirmasi Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body text-center py-4">
                <p class="mb-0 text-muted">Apakah Anda yakin ingin keluar dari akun ini?</p>
            </div>
            
            <div class="modal-footer border-0 justify-content-center gap-2 mb-2">
                <button type="button" class="btn btn-light px-4 fw-semibold" 
                        style="border-radius: 10px;" 
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger px-4 fw-bold" 
                            style="border-radius: 10px; background-color: #e63939;">
                        Ya, Logout
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>
@endsection
