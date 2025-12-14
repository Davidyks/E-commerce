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

        if (empty($user->name)) {
            return redirect()
                ->route('profile')
                ->with('warning', 'Lengkapi nama profil terlebih dahulu sebelum mulai berjualan.');
        }

        if ($user->role !== 'seller') {
            $user->update(['role' => 'seller']);
        }

        if (!$user->sellerDetail) {
            SellerDetail::create([
                'user_id'    => $user->id,
                'store_name' => $user->name,
                'joined_at'  => now(),
            ]);
        }

        // Redirect ke dashboard seller
        return redirect()->route('products.index');
    }
}