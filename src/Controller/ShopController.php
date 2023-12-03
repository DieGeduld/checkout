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
use App\Entity\ShoppingCartProduct;
use App\Entity\User;
use App\Entity\Country;
use App\Entity\Address;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;


class ShopController extends AbstractController
{
    private $entityManager;
    private $requestStack;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    #[Route('/shop', name: 'app_shop')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get all Products
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('shop/index.html.twig', [
            'controller_name' => 'Products',
            'products' => $products,
        ]);

    }

    //add to cart
    #[Route('/shop/addtocart/{id}', name: 'app_shop_addtocart')]
    public function addtocart(int $id, int $quantity = 1): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $shoppingCart = $this->getShoppingCart();
    
        if ($product && $shoppingCart) {
            // getting ShoppingCartProduct
            $shoppingCartProduct = $this->entityManager->getRepository(ShoppingCartProduct::class)->findOneBy([
                'shoppingcart' => $shoppingCart, 
                'product' => $product
            ]);

            if ($shoppingCartProduct == null) {
                if ($quantity <= 0) {
                    return $this->redirectToRoute('app_shop_shoppingcart');
                }
                $shoppingCartProduct = new ShoppingCartProduct();
                $shoppingCartProduct->setShoppingcart($shoppingCart);
                $shoppingCartProduct->setProduct($product);
                $shoppingCartProduct->setQuantity($quantity);
                $this->entityManager->persist($shoppingCartProduct);
            } else {

                if ($shoppingCartProduct->getQuantity() + $quantity <= 0) {
                    echo "start";
                    var_dump($shoppingCartProduct->getQuantity() );
                    var_dump($quantity);
                    var_dump($shoppingCartProduct->getQuantity() + $quantity);
                    $this->entityManager->remove($shoppingCartProduct);
                } else {
                    echo "start2";
                    var_dump($shoppingCartProduct->getQuantity());
                    var_dump($quantity);
                    var_dump($shoppingCartProduct->getQuantity() + $quantity);
                    $shoppingCartProduct->setQuantity($shoppingCartProduct->getQuantity() + $quantity);
                }

            }

            $this->entityManager->flush();
        }

        // return from where you came
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');
        if ($referer === null) {
            $referer = $this->generateUrl('app_shop');
        }
        return $this->redirect($referer);

    }

    // route for the shopping cart
    #[Route('/shop/shoppingcart', name: 'app_shop_shoppingcart')]
    public function shoppingcart(EntityManagerInterface $entityManager): Response
    {
        $shoppingCart = $this->getShoppingCart();

        if ($shoppingCart->getShoppingCartProducts()->count() == 0) {
            return $this->redirectToRoute('app_shop');
        }

        return $this->render('shop/shoppingcart.html.twig', [
            'shoppingCart' => $shoppingCart,
        ]);
    }

    // app_shop_shoppingcart_increase
    #[Route('/shop/shoppingcart/increase/{id}', name: 'app_shop_shoppingcart_increase')]
    public function shoppingcart_increase(EntityManagerInterface $entityManager, int $id): Response
    {
        
        $this->addtocart($id, 1);

        // return from where you came
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');
        if ($referer === null) {
            $referer = $this->generateUrl('app_shop');
        }
        return $this->redirect($referer);
    }

    // app_shop_shoppingcart_decrease
    #[Route('/shop/shoppingcart/decrease/{id}', name: 'app_shop_shoppingcart_decrease')]
    public function shoppingcart_decrease(int $id): Response
    {
        $this->addtocart($id, -1);

        // return from where you came
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');
        if ($referer === null) {
            $referer = $this->generateUrl('app_shop');
        }
        return $this->redirect($referer);
    }

    // app_shop_shoppingcart_remove
    #[Route('/shop/shoppingcart/remove/{id}', name: 'app_shop_shoppingcart_remove')]
    public function shoppingcart_remove(int $id): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $shoppingCart = $this->getShoppingCart();

        $shoppingCartProduct = $this->entityManager->getRepository(ShoppingCartProduct::class)->findOneBy([
            'shoppingcart' => $shoppingCart, 
            'product' => $product
        ]);
        $this->entityManager->remove($shoppingCartProduct);
        $this->entityManager->flush();
        
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');
        if ($referer === null) {
            $referer = $this->generateUrl('app_shop');
        }
        return $this->redirect($referer);
    }

    // route for the delivery address
    #[Route('/shop/deliveryaddress', name: 'app_shop_deliveryaddress')]
    public function deliveryaddress(EntityManagerInterface $entityManager): Response
    {
        $address = $this->entityManager->getRepository(Address::class)->findOneBy(['userId' => $this->security->getUser()->getId() ]);

        return $this->render('shop/deliveryaddress.html.twig', [
            'address' => $address,
        ]);
    }

    // route for the summary
    #[Route('/shop/summary', name: 'app_shop_summary')]
    public function summary(EntityManagerInterface $entityManager): Response
    {
        $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' =>$this->security->getUser()->getId(), 'state' => 'shopping']);
        $address = $this->entityManager->getRepository(Address::class)->findOneBy(['userId' =>$this->security->getUser()->getId()]);

        return $this->render('shop/summary.html.twig', [
            'shoppingCart' => $shoppingCart,
            'address' => $address,
        ]);
    }

    // route for the ordered
    #[Route('/shop/ordered', name: 'app_shop_ordered')]
    public function ordered(EntityManagerInterface $entityManager): Response
    {
        $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' =>$this->security->getUser()->getId(), 'state' => 'ordered']);
        $address = $this->entityManager->getRepository(Address::class)->findOneBy(['userId' =>$this->security->getUser()->getId()]);

        return $this->render('shop/ordered.html.twig', [
            'shoppingCart' => $shoppingCart,
            'address' => $address,
        ]);
    }



    // Route for listing all products
    #[Route('/shop/products', name: 'app_shop_products')]
    public function products(EntityManagerInterface $entityManager): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('shop/products.html.twig', [
            'products' => $products,
        ]);
    }


    // get shopping cart
    public function getShoppingCart(): ShoppingCart
    {
        if ($this->getUser() == null) {
            $request = $this->requestStack->getCurrentRequest();
            $sessionId = $request->getSession()->getId();

            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);

            if ($shoppingCart == null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setSessionId($sessionId);
                $shoppingCart->setState("shopping");
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }

        } else {
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' =>$this->security->getUser()->getId()]);
        }
        return $shoppingCart;

    }    
    // get shopping cart
    
    public function getShoppingCartProducts(): Array
    {
        if ($this->getUser() == null) {
            $request = $this->requestStack->getCurrentRequest();
            $sessionId = $request->getSession()->getId();

            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);

            if ($shoppingCart == null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setSessionId($sessionId);
                $shoppingCart->setState("shopping");
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }

            $shoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingcart' => $shoppingCart->getId()]);


        } else {
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['userId' =>$this->security->getUser()->getId()]);
        }
        return $shoppingCartProducts;

    }    



    #[Route('/shop/fill', name: 'fillshop')]
    public function fill(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {

        $faker = Factory::create();


        // truncate table
        $connection = $this->entityManager->getConnection();
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
            $this->entityManager->persist($product);
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
        $this->entityManager->persist($user);

        /*
         * Shopping Cart
         */

        $shoppingCart = new ShoppingCart();
        $shoppingCart->setUser($user->getId());
        $shoppingCart->setState("shopping");
        $this->entityManager->persist($shoppingCart);
        // Country

        $austria = new Country();
        $austria->setName("Österreich");
        $austria->setEu(true);
        $austria->setIso("AT");
        $this->entityManager->persist($austria);
        $germany = new Country();
        $germany->setName("Deutschland");
        $germany->setEu(true);
        $germany->setIso("DE");
        $this->entityManager->persist($germany);
        $france = new Country();
        $france->setName("Frankreich");
        $france->setEu(true);
        $france->setIso("FR");
        $this->entityManager->persist($france);
        $unitedKingdom = new Country();
        $unitedKingdom->setName("Vereinigtes Königreich");
        $unitedKingdom->setEu(false);
        $unitedKingdom->setIso("GB");
        $this->entityManager->persist($unitedKingdom);
        $usa = new Country();
        $usa->setName("Vereinigte Staaten");
        $usa->setEu(false);
        $usa->setIso("US");
        $this->entityManager->persist($usa);
        $china = new Country();
        $china->setName("China");
        $china->setEu(false);
        $china->setIso("CN");
        $this->entityManager->persist($china);
        $russia = new Country();
        $russia->setName("Russland");
        $russia->setEu(false);
        $russia->setIso("RU");
        $this->entityManager->persist($russia);

        /*
         * Address
         */

        $address = new Address();
        $address->setUser($user->getId());
        $address->setCountry($germany->getId());
        $address->setStreet("Teststraße 1");
        $address->setZip("1234");
        
        $this->entityManager->flush();

        return new Response(
            "<html><body><h1>" . $faker->text . " Shop</h1></body></html>"
        );

    }
}
