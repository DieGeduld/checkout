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

class LoginListener
{    private $requestStack;
    private $entityManager;
    private $productService;
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager, ProductService $productService)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->productService = $productService;

    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

        $session = $this->requestStack->getSession();
        $user = $event->getAuthenticationToken()->getUser();

        $sessionId = $session->getId();

        if ($session != null) {
            $oldShoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);
            $newShoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $user->getId()]);


            if ($oldShoppingCart && !$newShoppingCart) {
                $queryBuilder = $this->entityManager->createQueryBuilder();
                $queryBuilder->update(ShoppingCartProduct::class, 'scp')
                    ->set('scp.shoppingcart_id', $newShoppingCart->getId())
                    ->where('scp.shoppingcart_id = :oldShoppingCart')
                    ->setParameter('oldShoppingCart', $oldShoppingCart->getId())
                    ->getQuery()
                    ->execute();
            } else if ( $oldShoppingCart && $newShoppingCart) {
                $this->productService->mergeShoppingCarts($oldShoppingCart, $newShoppingCart);

            }
        }
    }
}
