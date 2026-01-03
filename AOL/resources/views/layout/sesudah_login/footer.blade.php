<style>
    @media (max-width: 767px){
        footer {
            font-size: 0.9rem;
        }

        footer h6 {
            font-size: 0.85rem;
        }

        footer p,
        footer li,
        footer span {
            font-size: 0.8rem;
        }
    };
</style>

<footer class="bg-light border-top mt-5 pt-5 pb-3">
    <div class="container">
        
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="fw-bold mb-2">BuyBuy.com</h6>
                <p class="small text-muted">
                    BuyBuy.com is your trusted online shopping destination offering a wide range of
                    quality products — from fashion, electronics, beauty, automotive, to personal care.
                    We’re committed to providing a fast, easy, and secure shopping experience with the best deals every day.
                </p>
            </div>
        </div>

        <div class="row">

            <div class="col-12 col-md-6 col-lg-3 mb-4">
                <h6 class="fw-bold mb-3">Our Service</h6>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-1">• Fast & secure nationwide shipping</li>
                    <li class="mb-1">• 24/7 customer support</li>
                    <li class="mb-1">• 100% authentic products guaranteed</li>
                    <li class="mb-1">• Daily deals & exclusive promotions</li>
                </ul>
            </div>

            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <h6 class="fw-bold mb-3">Contact Us</h6>
                <ul class="list-unstyled small text-muted">
                    <li class="d-flex mb-2">
                        <span class="me-2">📞</span> 
                        <span>Contact Us</span>
                    </li>
                    <li class="d-flex mb-2">
                        <span class="me-2">📍</span> 
                        <span>Address: Jl. Merdeka No. 123, Jakarta, Indonesia</span>
                    </li>
                    <li class="d-flex mb-2">
                        <span class="me-2">📧</span> 
                        <span>Email: support@buybuy.com</span>
                    </li>
                    <li class="d-flex mb-2">
                        <span class="me-2">📞</span> 
                        <span>Phone: +62 21 555 8899</span>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-md-6 col-lg-3 mb-4">
                <h6 class="fw-bold mb-2">Payment</h6>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <img src="{{ asset('asset/images/sebelum_login/alfamart.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/bsi.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/bni.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/mastercard.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/cimb niaga.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/alfamide.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/mandiri.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/bca.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/bri.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/indomare.png') }}" height="20">
                </div>

                <h6 class="fw-bold mb-2">Delivery</h6>
                <div class="d-flex flex-wrap gap-2">
                    <img src="{{ asset('asset/images/sebelum_login/jne.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/gosend.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/ninja express.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/sicepat.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/grab.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/id express.png') }}" height="20">
                    <img src="{{ asset('asset/images/sebelum_login/jntcargo.png') }}" height="20">
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-2 mb-4">
                <h6 class="fw-bold mb-2">Follow Us</h6>
                <div class="d-flex flex-column gap-2 mb-4 small text-muted">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset('asset/images/sebelum_login/facebook.png') }}" width="20" height="20"> 
                        <span>Facebook</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset('asset/images/sebelum_login/instagram.png') }}" width="20" height="20"> 
                        <span>Instagram</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset('asset/images/sebelum_login/twitter.png') }}" width="20" height="20"> 
                        <span>X (Twitter)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ asset('asset/images/sebelum_login/linkedin.png') }}" width="20" height="20"> 
                        <span>LinkedIn</span>
                    </div>
                </div>
            </div>

        </div>

        <hr class="my-4">

        <div class="text-start small text-muted">
            © {{ date('Y') }} BuyBuy.com — All Rights Reserved.
        </div>

    </div>
</footer>