<?php 
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\ShoppingCart;
use App\Entity\ShoppingCartProduct;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;


class ProductService
{
    private $entityManager;
    private $security;
    private $requestStack;
    public function __construct(EntityManagerInterface $entityManager, Security $security, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->requestStack = new RequestStack();
    }

    public function getProducts()
    {
        if ($this->security->getUser() == null) {
            $request = $this->requestStack->getCurrentRequest();


            if ($request && $request->hasSession()) {
                $session = $request->getSession();
                $sessionId = $session->getId();
                // Ihr Code hier...
            } else {

               return [];
            }
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
}