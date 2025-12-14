<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SellerDetail;

class SellerController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('layout.seller.home', compact('user'));
    }
    public function startSelling()
    {
        $user = Auth::user();

        // Jika bukan seller → update role & buat seller_detail
        if ($user->role !== 'seller') {
            $user->role = 'seller';
            $user->save();

            // Cek apakah sellerDetail sudah ada
            SellerDetail::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'store_name'  => $user->name,
                    'store_logo'  => $user->profile_picture,
                    'joined_at'   => now(),
                ]
            );
        }

        // Redirect ke dashboard seller
        return redirect()->route('products.index');
    }
}