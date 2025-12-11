<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=CategorySeeder
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Automotive',
                'category_image' => 'asset/images/category/category-automotive.png'
            ],
            [
                'category_name' => 'Electronic',
                'category_image' => 'asset/images/category/category-electronic.png'
            ],
            [
                'category_name' => 'Health',
                'category_image' => 'asset/images/category/category-health.png'
            ],
            [
                'category_name' => 'Computer',
                'category_image' => 'asset/images/category/category-computer.png'
            ],
            [
                'category_name' => 'Beauty',
                'category_image' => 'asset/images/category/category-beauty.png'
            ],
            [
                'category_name' => 'Man Shoes',
                'category_image' => 'asset/images/category/category-man-shoes.png'
            ],
            [
                'category_name' => 'Fashion',
                'category_image' => 'asset/images/category/category-fashion.png'
            ],
            [
                'category_name' => 'Books',
                'category_image' => 'asset/images/category/category-books.png'
            ],
            [
                'category_name' => 'Food',
                'category_image' => 'asset/images/category/category-food.png'
            ],
            [
                'category_name' => 'Man Bag',
                'category_image' => 'asset/images/category/category-man-bag.png'
            ],
            [
                'category_name' => 'Watch',
                'category_image' => 'asset/images/category/category-watch.png'
            ],
            [
                'category_name' => 'Sport',
                'category_image' => 'asset/images/category/category-sport.png'
            ],
            [
                'category_name' => 'Woman Bag',
                'category_image' => 'asset/images/category/category-woman-bag.png'
            ],
            [
                'category_name' => 'Woman Shoes',
                'category_image' => 'asset/images/category/category-woman-shoes.png'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}