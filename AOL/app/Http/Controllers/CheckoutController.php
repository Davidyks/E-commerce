<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAddress;
use App\Models\Shipping;
use App\Models\Voucher;
use stdClass; 

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $directBuy = session('direct_buy'); 

        if ($directBuy) {
            $product = Product::with('seller')->find($directBuy['product_id']);

            if (!$product) {
                return redirect()->back()->with('error', 'Produk tidak ditemukan.');
            }

            $tempItem = new stdClass();
            $tempItem->id = null;
            $tempItem->quantity = $directBuy['quantity'];
            $tempItem->price = $product->price;
            $tempItem->product = $product; 

            $cartItems = collect([$tempItem]);

        } else {
            $cartItems = $user->cartItems()->with('product.seller')->get();

            if($cartItems->isEmpty()) {
               return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda kosong.');
            }
        }

        $mainAddress = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();
        if (!$mainAddress) {
            $mainAddress = UserAddress::where('user_id', $user->id)->first();
        }

        $shippings = Shipping::all();

        foreach($shippings as $ship) {
            $courierName = strtolower($ship->courier);
            if(str_contains($courierName, 'jne')) $ship->logo = asset('asset/images/sebelum_login/jne.png');
            elseif(str_contains($courierName, 'sicepat')) $ship->logo = asset('asset/images/sebelum_login/sicepat.png');
            elseif(str_contains($courierName, 'ninja')) $ship->logo = asset('asset/images/sebelum_login/ninja express.png');
            elseif(str_contains($courierName, 'gosend')) $ship->logo = asset('asset/images/sebelum_login/gosend.png');
            else $ship->logo = asset('asset/images/sebelum_login/delivery.png');
        }

        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

        $availableVouchers = Voucher::where('start_at', '<=', now())
                            ->where('end_at', '>=', now())
                            ->get();
        
        $discountAmount = 0;
        $sessionVoucher = session('applied_voucher');

        if ($sessionVoucher) {
            $voucher = Voucher::where('code', $sessionVoucher['code'])->first();
            if ($voucher) {
                $discountAmount = $voucher->calculateDiscount($subtotal);
            }
        }

        $paymentMethods = [
            ['code' => 'bca_va', 'name' => 'BCA Virtual Account', 'logo' => asset('asset/images/sebelum_login/bca.png'), 'fee' => 1000],
            ['code' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'logo' => asset('asset/images/sebelum_login/mandiri.png'), 'fee' => 1000],
            ['code' => 'bni_va', 'name' => 'BNI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bni.png'), 'fee' => 1000],
            ['code' => 'bri_va', 'name' => 'BRI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bri.png'), 'fee' => 1000],
            ['code' => 'bsi_va', 'name' => 'BSI Virtual Account', 'logo' => asset('asset/images/sebelum_login/bsi.png'), 'fee' => 1000],
            ['code' => 'cimb_va', 'name' => 'CIMB Niaga Virtual Account', 'logo' => asset('asset/images/sebelum_login/cimb niaga.png'), 'fee' => 1000],
            ['code' => 'alfamart', 'name' => 'Alfamart / Alfamidi', 'logo' => asset('asset/images/sebelum_login/alfamart.png'), 'fee' => 2500],
            ['code' => 'indomaret', 'name' => 'Indomaret', 'logo' => asset('asset/images/sebelum_login/indomare.png'), 'fee' => 2500],
            ['code' => 'gosend_pay', 'name' => 'GoPay', 'logo' => asset('asset/images/sebelum_login/gopay.jpg'), 'fee' => 0],
        ];

        $insuranceFee = 6000;
        $applicationFee = 1000;
        $defaultShippingCost = $shippings->first()->base_cost ?? 0;

        $totalPay = ($subtotal + $defaultShippingCost + $insuranceFee + $applicationFee) - $discountAmount;
        if($totalPay < 0) $totalPay = 0;

        return view('user.checkout', compact(
            'cartItems', 'subtotal', 'discountAmount', 'shippings', 'mainAddress', 
            'insuranceFee', 'applicationFee', 'totalPay', 'defaultShippingCost',
            'paymentMethods', 'availableVouchers'
        ));
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'label' => 'required', 'recipient_name' => 'required', 'phone' => 'required',
            'address' => 'required', 'city' => 'required', 'province' => 'required', 'postal_code' => 'required',
        ]);
        
        $user = Auth::user();
        $isFirst = UserAddress::where('user_id', $user->id)->doesntExist();

        UserAddress::create([
            'user_id' => $user->id,
            'label' => $request->label,
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'is_default' => $isFirst,
        ]);

        return redirect()->back()->with('success', 'Alamat berhasil disimpan!');
    }

    public function store(Request $request)
    {
        return redirect()->route('home')->with('success', 'Transaksi Berhasil! Pesanan sedang diproses.');
    }

    public function applyVoucher(Request $request)
    {
        $request->validate(['code' => 'required']);
        $user = Auth::user();
        
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return redirect()->back()->with('error', 'Kode voucher tidak valid!');
        }

        $cartItems = $user->cartItems;
        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

        if (!$voucher->isValid($user, $subtotal)) {
            return redirect()->back()->with('error', 'Voucher tidak valid (Min. belanja kurang / Kuota habis).');
        }
        
        session()->put('applied_voucher', [
            'code' => $voucher->code
        ]);

        return redirect()->back()->with('success', 'Voucher berhasil dipasang!');
    }

    public function removeVoucher()
    {
        session()->forget('applied_voucher');
        return redirect()->back()->with('success', 'Voucher dilepas.');
    }
}