<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Faker\Factory;
use App\Factory\ProductFactory;
use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Entity\User;
use App\Entity\Country;
use App\Entity\Address;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(): Response
    {
        return $this->render('shop/index.html.twig', [
            'controller_name' => 'ShopController',
        ]);
    }

    // route for the shopping cart
    #[Route('/shop/shoppingcart', name: 'app_shop_shoppingcart')]
    public function shoppingcart(EntityManagerInterface $entityManager): Response
    {
        $shoppingCart = $entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' => $this->getUser()->getId(), 'state' => 'shopping']);

        return $this->render('shop/shoppingcart.html.twig', [
            'shoppingCart' => $shoppingCart,
        ]);
    }

    // route for the delivery address
    #[Route('/shop/deliveryaddress', name: 'app_shop_deliveryaddress')]
    public function deliveryaddress(EntityManagerInterface $entityManager): Response
    {
        $address = $entityManager->getRepository(Address::class)->findOneBy(['userId' => $this->getUser()->getId()]);

        return $this->render('shop/deliveryaddress.html.twig', [
            'address' => $address,
        ]);
    }

    // route for the summary
    #[Route('/shop/summary', name: 'app_shop_summary')]
    public function summary(EntityManagerInterface $entityManager): Response
    {
        $shoppingCart = $entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' => $this->getUser()->getId(), 'state' => 'shopping']);
        $address = $entityManager->getRepository(Address::class)->findOneBy(['userId' => $this->getUser()->getId()]);

        return $this->render('shop/summary.html.twig', [
            'shoppingCart' => $shoppingCart,
            'address' => $address,
        ]);
    }

    // route for the ordered
    #[Route('/shop/ordered', name: 'app_shop_ordered')]
    public function ordered(EntityManagerInterface $entityManager): Response
    {
        $shoppingCart = $entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' => $this->getUser()->getId(), 'state' => 'ordered']);
        $address = $entityManager->getRepository(Address::class)->findOneBy(['userId' => $this->getUser()->getId()]);

        return $this->render('shop/ordered.html.twig', [
            'shoppingCart' => $shoppingCart,
            'address' => $address,
        ]);
    }



    // Route for listing all products
    #[Route('/shop/products', name: 'app_shop_products')]
    public function products(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('shop/products.html.twig', [
            'products' => $products,
        ]);
    }


    



    #[Route('/shop/fill', name: 'fillshop')]
    public function fill(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {

        $faker = Factory::create();


        // truncate table
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement($platform->getTruncateTableSQL('product', true));
        $connection->executeStatement($platform->getTruncateTableSQL('shopping_cart', true));
        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
        $connection->executeStatement($platform->getTruncateTableSQL('country', true));
        $connection->executeStatement($platform->getTruncateTableSQL('address', true));


        // reset autoincrement

        $connection->executeStatement("DELETE FROM sqlite_sequence WHERE name='product';");
        $connection->executeStatement("DELETE FROM sqlite_sequence WHERE name='shopping_cart';");
        $connection->executeStatement("DELETE FROM sqlite_sequence WHERE name='user';");
        $connection->executeStatement("DELETE FROM sqlite_sequence WHERE name='country';");
        $connection->executeStatement("DELETE FROM sqlite_sequence WHERE name='address';");



        /*
        * Erstellen von Produkten
        */

        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setName($faker->name);
            $product->setStock($faker->numberBetween(50, 200));
            $product->setPrice($faker->randomFloat(2, 1, 100));
            $product->setDescription($faker->text);
            $entityManager->persist($product);
        }

        /*
         * User
         */

        $user = new User();
        $plaintextPassword = "test1234";

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $user->setEmail("fw@unkonventionell.at");
        $user->setIsVerified(true);
        $entityManager->persist($user);

        /*
         * Shopping Cart
         */

        $shoppingCart = new ShoppingCart();
        $shoppingCart->setUserId($user->getId());
        $shoppingCart->setState("shopping");
        $entityManager->persist($shoppingCart);
        // Country

        $austria = new Country();
        $austria->setName("Österreich");
        $austria->setEu(true);
        $austria->setIso("AT");
        $entityManager->persist($austria);
        $germany = new Country();
        $germany->setName("Deutschland");
        $germany->setEu(true);
        $germany->setIso("DE");
        $entityManager->persist($germany);
        $france = new Country();
        $france->setName("Frankreich");
        $france->setEu(true);
        $france->setIso("FR");
        $entityManager->persist($france);
        $unitedKingdom = new Country();
        $unitedKingdom->setName("Vereinigtes Königreich");
        $unitedKingdom->setEu(false);
        $unitedKingdom->setIso("GB");
        $entityManager->persist($unitedKingdom);
        $usa = new Country();
        $usa->setName("Vereinigte Staaten");
        $usa->setEu(false);
        $usa->setIso("US");
        $entityManager->persist($usa);
        $china = new Country();
        $china->setName("China");
        $china->setEu(false);
        $china->setIso("CN");
        $entityManager->persist($china);
        $russia = new Country();
        $russia->setName("Russland");
        $russia->setEu(false);
        $russia->setIso("RU");
        $entityManager->persist($russia);

        /*
         * Address
         */

        $address = new Address();
        $address->setUserId($user->getId());
        $address->setCountryId($germany->getId());
        $address->setStreet("Teststraße 1");
        $address->setZip("1234");
        
        $entityManager->flush();

        return new Response(
            "<html><body><h1>" . $faker->text . " Shop</h1></body></html>"
        );

    }
}
