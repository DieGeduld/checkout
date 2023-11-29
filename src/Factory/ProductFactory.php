<?php   
namespace App\Factory;

use App\Entity\Product;
use Faker\Factory;

class ProductFactory
{
    public function createProduct(): Product
    {
        $faker = Factory::create();

        $product = new Product();
        $product->setName($faker->word);
        $product->setStock($faker->numberBetween(1, 20));
        $product->setPrice($faker->randomFloat(2, 1, 100));
        $product->setDescription($faker->text);

        return $product;
    }
}
