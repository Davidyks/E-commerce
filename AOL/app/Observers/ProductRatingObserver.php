<?php

namespace App\Observers;

use App\Models\ProductRating;

class ProductRatingObserver
{
    public function saved(ProductRating $rating)
    {
        $this->updateProductRating($rating);
    }

    public function deleted(ProductRating $rating)
    {
        $this->updateProductRating($rating);
    }

    private function updateProductRating(ProductRating $rating)
    {
        $product = $rating->product;

        $avgRating = $product->ratings()->avg('rating');

        $product->update([
            'rating' => $avgRating ?? 0
        ]);
    }
}
