<?php

namespace Webkul\Product\Helpers;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Contracts\Product;

class Review
{
    /**
     * Returns the product's avg rating
     *
     * @param  Product  $product
     * @return HasMany
     */
    public function getReviews($product)
    {
        return $product->reviews()->where('status', 'approved');
    }

    /**
     * Returns the product's avg rating
     *
     * @param  Product  $product
     * @return string
     */
    public function getAverageRating($product)
    {
        if (array_key_exists('approved_reviews_avg_rating', $product->getAttributes())) {
            return number_format(round((float) $product->getAttributes()['approved_reviews_avg_rating'], 2), 1);
        }

        return number_format(round($product->reviews->where('status', 'approved')->avg('rating'), 2), 1);
    }

    /**
     * Returns the total review of the product
     *
     * @param  Product  $product
     * @return int
     */
    public function getTotalReviews($product)
    {
        if (array_key_exists('approved_reviews_count', $product->getAttributes())) {
            return (int) $product->getAttributes()['approved_reviews_count'];
        }

        return $product->reviews->where('status', 'approved')->count();
    }

    /**
     * Returns the total rating of the product
     *
     * @param  Product  $product
     * @return int
     */
    public function getTotalRating($product)
    {
        if (array_key_exists('approved_reviews_sum_rating', $product->getAttributes())) {
            return (int) $product->getAttributes()['approved_reviews_sum_rating'];
        }

        return $product->reviews->where('status', 'approved')->sum('rating');
    }

    /**
     * Returns the total active feedback of the product
     *
     * @param  Product  $product
     * @return int
     */
    public function getTotalFeedback($product)
    {
        return core()->getConfigData('catalog.products.review.summary') == 'star_counts'
            ? $this->getTotalRating($product)
            : $this->getTotalReviews($product);
    }

    /**
     * Returns reviews with ratings.
     *
     * @param  Product  $product
     * @return Collection
     */
    public function getReviewsWithRatings($product)
    {
        return $product->reviews()
            ->where('status', 'approved')
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();
    }

    /**
     * Returns the Percentage rating of the product
     *
     * @param  Product  $product
     * @return array
     */
    public function getPercentageRating($product)
    {
        $reviews = $this->getReviewsWithRatings($product);

        $totalReviews = $this->getTotalReviews($product);

        for ($i = 5; $i >= 1; $i--) {
            if (! $reviews->isEmpty()) {
                foreach ($reviews as $review) {
                    if ($review->rating == $i) {
                        $percentage[$i] = round(($review->total / $totalReviews) * 100);

                        break;
                    } else {
                        $percentage[$i] = 0;
                    }
                }
            } else {
                $percentage[$i] = 0;
            }
        }

        return $percentage;
    }
}
