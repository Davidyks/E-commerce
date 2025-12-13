<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    //
    public function apply(Request $request, VoucherService $service)
    {
        try {
            $result = $service->apply(
                $request->code, 
                auth()->user(), 
                $this->getCartTotal()
            );

            return response()->json([
                'message' => 'Sukses!',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
