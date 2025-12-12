<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SellerDetail;

class SellerController extends Controller
{
    public function startSelling()
    {
        $user = Auth::user();

        // Jika bukan seller → update role & buat seller_detail
        if ($user->role !== 'seller') {
            $user->role = 'seller';
            $user->save();

            // Cek apakah sellerDetail sudah ada
            if (!$user->sellerDetail) {
                SellerDetail::create([
                    'user_id' => $user->id,
                    'store_name' => $user->name . "'s Store",
                    // bisa tambahkan default lainnya jika perlu
                ]);
            }
        }

        // Redirect ke dashboard seller
        return redirect()->route('products.index');
    }
}