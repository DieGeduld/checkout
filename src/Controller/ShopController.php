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
use App\Services\ProductService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\Exception\TransitionException;
use Symfony\Component\Workflow\Registry;
use App\Form\Type\AddressType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Order;


class ShopController extends AbstractController
{
    private $entityManager;
    private $requestStack;
    private $security;
    private $workflow;
    private $workflowRegistry;
    private $productService;
    private $shoppingCartProducts;
    private $shoppingCart;
    private $addresses;


    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, Security $security, Workflow $workflow, Registry $workflowRegistry, ProductService $productService)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->workflow = $workflow;
        $this->workflowRegistry = $workflowRegistry;
        $this->productService = $productService;
        $this->shoppingCartProducts = $productService->getShoppingCartProducts();
        $this->shoppingCart = $productService->getShoppingCart();
        $this->addresses = $productService->getAddresses();
    }

    #[Route('/shop', name: 'app_shop')]
    public function index(): Response
    { 
        $this->changeState("shopping");
        // Get all Products
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('shop/index.html.twig', [
            'controller_name' => 'Products',
            'products' => $products,
        ]);

    }

    #[Route('/shop/updatecart/{id}', name:'app_shop_shoppingcart_update')]
    public function update(Product $product, Request $request): Response
    {
        $quantity = $request->request->get('quantity', 1);

        if ($quantity <= 0) {
            return $this->redirectToRoute('app_shop_shoppingcart');
        }

        if ($product && $this->shoppingCart) {

            // getting ShoppingCartProduct
            $shoppingCartProduct = $this->entityManager->getRepository(ShoppingCartProduct::class)->findOneBy([
                'shoppingcart' => $this->shoppingCart, 
                'product' => $product
            ]);

            if ($shoppingCartProduct == null) {
                if ($quantity <= 0) {
                    return $this->redirectToRoute('app_shop_shoppingcart');
                }
                $shoppingCartProduct = new ShoppingCartProduct();
                $shoppingCartProduct->setShoppingcart($this->shoppingCart);
                $shoppingCartProduct->setProduct($product);
                $shoppingCartProduct->setQuantity($quantity);
                $this->entityManager->persist($shoppingCartProduct);
                $this->addFlash('success', "Added \"" . $product->getName() . "\" to shopping cart");
            } else {

                if ($shoppingCartProduct->getQuantity() + $quantity <= 0) {
                    $this->addFlash('success', "Removed \"" . $product->getName() . "\" from shopping cart");
                    $this->entityManager->remove($shoppingCartProduct);
                } else {
                    if ($shoppingCartProduct->getQuantity() > $quantity) {
                        $this->addFlash('success', "Removed " . abs($quantity - $shoppingCartProduct->getQuantity())  . "x \"" . $product->getName() . "\" from shopping cart");
                    } else {
                        $this->addFlash('success', "Added " . abs($shoppingCartProduct->getQuantity() - $quantity)  . "x \"" . $product->getName() . "\" from shopping cart");
                    }

                    $shoppingCartProduct->setQuantity($quantity);
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


    //add to cart
    #[Route('/shop/addtocart/{id}', name: 'app_shop_addtocart')]
    public function addtocart(Product $product, Request $request): Response
    {
        $quantity = $request->query->get('quantity', 1);

        if ($quantity <= 0) {
            return $this->redirectToRoute('app_shop_shoppingcart');
        }

        $shoppingCartProduct = $this->entityManager->getRepository(ShoppingCartProduct::class)->findOneBy([
            'shoppingcart' => $this->shoppingCart, 
            'product' => $product
        ]);

        if ($shoppingCartProduct == null) {
            $shoppingCartProduct = new ShoppingCartProduct();
            $shoppingCartProduct->setShoppingcart($this->shoppingCart);
            $shoppingCartProduct->setProduct($product);
            $shoppingCartProduct->setQuantity($quantity);
            $this->entityManager->persist($shoppingCartProduct);
            $this->addFlash('success', "Added \"" . $product->getName() . "\" to shopping cart");
        } else {
            $this->addFlash('success', "Added " . abs($quantity) . "x \"" . $product->getName() . "\" to shopping cart");
            $shoppingCartProduct->setQuantity($shoppingCartProduct->getQuantity() + $quantity);
        }
        $this->entityManager->flush();



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
        $this->changeState("shopping_cart");

        $shoppingCart = $this->getShoppingCart();
        if (count($this->getShoppingCartProducts()) == 0) {
            $this->addFlash('error', "Your shopping cart is empty");
            return $this->redirectToRoute('app_shop');
        }

        return $this->render('shop/shoppingcart.html.twig', [
            'shoppingCart' => $shoppingCart,
        ]);
    }

    // app_shop_shoppingcart_increase
    #[Route('/shop/shoppingcart/increase/{id}', name: 'app_shop_shoppingcart_increase')]
    public function shoppingcart_increase(EntityManagerInterface $entityManager, Request $request, Product $product): Response
    {

        $shoppingCartProduct = $this->entityManager->getRepository(ShoppingCartProduct::class)->findOneBy(['shoppingcart' => $this->shoppingCart->getId(), 'product' => $product]);    
        if ($shoppingCartProduct) {
            $currentQuantity = $shoppingCartProduct->getQuantity();
            $request->request->set('quantity', $currentQuantity + 1);
            $this->update($product, $request);
        } else {
            $request->request->set('quantity', 1);
            $this->update($product, $request);
        }

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
    public function shoppingcart_decrease(EntityManagerInterface $entityManager, Request $request, Product $product): Response
    {
        $shoppingCartProduct = $this->entityManager->getRepository(ShoppingCartProduct::class)->findOneBy(['shoppingcart' => $this->shoppingCart->getId(), 'product' => $product]);    
        if ($shoppingCartProduct) {
            $currentQuantity = $shoppingCartProduct->getQuantity();
            
            if ($currentQuantity > 1) {
                $request->request->set('quantity', $currentQuantity - 1);
                $this->update($product, $request);
            } else {
                $this->entityManager->remove($shoppingCartProduct);
                $this->entityManager->flush();
            }
        } 

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
        if ($shoppingCartProduct) {
            $this->entityManager->remove($shoppingCartProduct);
            $this->entityManager->flush();
            $this->addFlash('success','Removed "' . $product->getName() . '" from shopping cart');
        } else {
            $this->addFlash('success', $product->getName() . 'was allready removed from shopping cart');
        }
        
        $request = $this->requestStack->getCurrentRequest();
        $referer = $request->headers->get('referer');
        if ($referer === null) {
            $referer = $this->generateUrl('app_shop');
        }
        return $this->redirect($referer);
    }

    // route for the delivery address
    #[Route('/shop/deliveryaddress', name: 'app_shop_deliveryaddress')]
    public function deliveryaddress(EntityManagerInterface $entityManager, Request $request): Response
    {

        
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            if ($this->security->getUser()) {
                

            } else {

                // save address to session
                $request = $this->requestStack->getCurrentRequest();
                $session = $this->requestStack->getSession();

                $userData = [];

                $fields = ['firstName', 'lastName', 'street', 'number', 'city', 'zip', 'country', 'taxNumber', 'telephone', 'email'];

                foreach ($fields as $field) {
                    $userData[$field] = $form->get($field)->getData();
                }

                $session->set('userData', $userData);

                return $this->redirectToRoute('app_shop_summary');

            }
        }

    
        $this->changeState("delivery_address");


        $session = $this->requestStack->getSession();
        $userData = $session->get('userData', []);

        $address = new Address();

        if ($userData) {
            
            // Setzen Sie die Eigenschaften des Address-Objekts basierend auf den Session-Daten
            $address->setFirstName($userData['firstName'] ?? "");
            $address->setLastName($userData['lastName'] ?? "");
            $address->setStreet($userData['street'] ?? "");
            $address->setNumber($userData['number'] ?? "");
            $address->setCity($userData['city'] ?? "");
            $address->setZip($userData['zip'] ?? "");
            
            if ($userData['country'] instanceof Country) {
                $userData['country'] = $this->entityManager->merge($userData['country']);
            }

            $address->setCountry($userData['country'] ?? "");
            $address->setTaxNumber($userData['taxNumber'] ?? "");
            $address->setTelephone($userData['telephone'] ?? "");
            $address->setEmail($userData['email'] ?? "");
        }

        $form = $this->createForm(AddressType::class, $address);

        if ($this->security->getUser()) {

            $currentAddress = $this->security->getUser()->getCurrentAddress();
        
            return $this->render('shop/deliveryaddress.html.twig', [
                'currentAddress' => $currentAddress,
                'addresses' => $this->addresses,
                'form' => $form,
            ]);

        } else {

            return $this->render('shop/deliveryaddress.html.twig', [
                'currentAddress' => null,
                'form' => $form,
            ]);
        
        }
    }

    // route for the summary
    #[Route('/shop/summary', name: 'app_shop_summary')]
    public function summary(EntityManagerInterface $entityManager): Response
    {

        $this->changeState("summary");

        $shoppingCart = $this->getShoppingCart();

        if ($this->security->getUser()) {
            dd("currently not supported");
            // $address = $this->entityManager->getRepository(Address::class)->findOneBy(['user_id' =>$this->security->getUser()->getId()]);

            // return $this->render('shop/summary.html.twig', [
            //     'shoppingCart' => $shoppingCart,
            //     'address' => $address,
            // ]);

        } else {

            $session = $this->requestStack->getSession();
            $userData = $session->get('userData', []);

            return $this->render('shop/summary.html.twig', [
                'shoppingCart' => $shoppingCart,
                'userData' => $userData
            ]);

        }
    }

    // route for the ordered
    #[Route('/shop/ordered', name: 'app_shop_ordered')]
    public function ordered(EntityManagerInterface $entityManager): Response
    {
        $this->changeState("ordered");


        if ($this->security->getUser()) {
                
            dd("currently not supported");
        } else {

            $this->getShoppingCartProducts();

            // save address to session
            $request = $this->requestStack->getCurrentRequest();
            $session = $this->requestStack->getSession();

            $userData = $session->get('userData', []);

            
            $order = new Order();
            $order->setUser(null);
            
            
            // TODO: Write address to order, or separate 'address_order' table?
            // $order->setAddressId($address->getId());
            // $order->setTotal($this->getShoppingCart()->getTotal());
            $order->setDate(new \DateTime());
            $order->setStatus("ordered");
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            
            
            $session->set('order', $order);
                        
            return $this->render('shop/ordered.html.twig', [
                'order' => $order,
                'userData' => $userData,
                'items' => $this->getShoppingCartProducts()
            ]);
        }



        // $session = $this->requestStack->getSession();
        // $userData = $session->get('userData', []);

        // $orderedItems = [];

        // return $this->render('shop/summary.html.twig', [
        //     'orderedItems' => $orderedItems,
        //     'userData' => $userData
        // ]);
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
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId()]);
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

        } else {
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId()]);
        }
        $shoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingcart' => $shoppingCart->getId()]);    
        return $shoppingCartProducts;

    }    


    function changeState($transition)
    {
        $workflow = $this->workflowRegistry->get($this->getShoppingCart(), 'checkout_process');
        $marking = $workflow->getMarking($this->getShoppingCart());
        $currentState = array_keys($marking->getPlaces())[0];
    
        if ($currentState != $transition) {
            try {          
                $workflow->apply($this->getShoppingCart(), 'to_' . $transition);
                $this->entityManager->persist($this->getShoppingCart());
                $this->entityManager->flush();
            } catch (TransitionException $e) {
                $this->addFlash('error', "??" . $e->getMessage()); 
                return $this->redirectToRoute('app_shop');
            }
        }
    
        // Recheck the state and add a success message if needed
        $newMarking = $workflow->getMarking($this->getShoppingCart());
        $newState = array_keys($newMarking->getPlaces())[0];
        if ($newState !== $currentState) {
            $request = $this->requestStack->getCurrentRequest();
            $referer = $request->headers->get('referer');
            if ($referer === null) {
                $referer = $this->generateUrl('app_shop');
            }
            $this->addFlash('success', $currentState . ' -> ' . $transition); 
            return $this->redirect($referer);
        }
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
        $connection->executeStatement($platform->getTruncateTableSQL('shopping_cart_product', true));
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
        $shoppingCart->setUserId($user->getId());
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
        $address->setUserId($user->getId());
        $address->setCountryId($germany->getId());
        $address->setStreet("Teststraße 1");
        $address->setZip("1234");
        
        $this->entityManager->flush();

        return new Response(
            "<html><body><h1>" . $faker->text . " Shop</h1></body></html>"
        );

    }
}
