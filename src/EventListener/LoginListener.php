<?php 

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\ShoppingCart;
use App\Entity\ShoppingCartProduct;
use Doctrine\Common\Lexer\Token;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Services\ProductService;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\Security;

class LoginListener
{    private $requestStack;
    private $entityManager;
    private $productService;
    private $security;
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager, ProductService $productService, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->productService = $productService;
        $this->security = $security;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

        $session = $this->requestStack->getSession();
        $user = $event->getAuthenticationToken()->getUser();

        $sessionId = $session->getId();
        
        if ($session != null) {
            $oldShoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);
            $newShoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $user->getId()]);

            if (!$newShoppingCart && !$oldShoppingCart->getUserId()) {
                $oldShoppingCart->setUserId($user);
                $this->entityManager->persist($oldShoppingCart);
                $this->entityManager->flush();
            } else if ($newShoppingCart && $oldShoppingCart) {
                $this->productService->mergeShoppingCarts($oldShoppingCart, $newShoppingCart);
            } else if ($oldShoppingCart && !$newShoppingCart) {
                $newShoppingCart = new ShoppingCart();
                $newShoppingCart->setSessionId($sessionId);
                $newShoppingCart->setUserId($user);
                $this->entityManager->persist($newShoppingCart);
                $this->entityManager->flush();
                $this->productService->mergeShoppingCarts($oldShoppingCart, $newShoppingCart);
            }
        }
    }
    public function onSecurityLogout(LogoutEvent $event)
    {

        $user = $this->security->getUser();

        if ($user) {

            //TODO

        }
        // $session = $this->requestStack->getSession();
        // $sessionId = $session->getId();
        // $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);
        // $shoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingCart' => $shoppingCart->getId()]);
        // foreach ($shoppingCartProducts as $shoppingCartProduct) {
        //     $this->entityManager->remove($shoppingCartProduct);
        // }
        // $this->entityManager->remove($shoppingCart);
        // $this->entityManager->flush();
    }
}
