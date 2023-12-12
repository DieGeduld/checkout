<?php 
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\ShoppingCart;
use App\Entity\ShoppingCartProduct;
use App\Entity\Address;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;


class ProductService
{
    private $entityManager;
    private $security;
    private $request;
    public function __construct(EntityManagerInterface $entityManager, Security $security, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getShoppingCartProducts(): array
    {
        if ($this->security->getUser() == null) {

            $session = $this->request->getSession();
            $sessionId = $session->getId();

            $sessionId = $this->request->getSession()->getId();

            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);

            if ($shoppingCart == null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setSessionId($sessionId);
                $shoppingCart->setState("shopping6");
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }

        } else {
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId()]);
        }

        $shoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingcart' => $shoppingCart->getId()]);    
        return $shoppingCartProducts;
    }


    public function getShoppingCart(): ?ShoppingCart
    { 
        if ($this->security->getUser() == null) {

            $session = $this->request->getSession();
            $sessionId = $session->getId();

            $sessionId = $this->request->getSession()->getId();

            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId]);

            if ($shoppingCart == null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setSessionId($sessionId);
                $shoppingCart->setState("shopping7");
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }

        } else {
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId()]);
        }
        return $shoppingCart;
    }

    public function getAddresses(): array
    {
        if ($this->security->getUser() != null) {
            $addresses = $this->entityManager->getRepository(Address::class)->findBy(['user_id' => $this->security->getUser()->getId() ]);
        } else {
            $addresses = [];
        }
        return $addresses;
    }

}