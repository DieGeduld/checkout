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
        $shoppingCart = $this->getShoppingCart();       

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select([
            'p.id AS id',
            'scp.quantity AS quantity',
            'p.name AS name',
            'p.price AS price',
            'p.price * scp.quantity AS sum'
        ])
        ->from(ShoppingCartProduct::class, 'scp')
        ->join('scp.product', 'p') // Annahme, dass ShoppingCartProduct eine 'product' Beziehung hat
        ->where('scp.shoppingcart = :shoppingCartId')
        ->setParameter('shoppingCartId', $shoppingCart->getId());
        
        $shoppingCartProducts = $queryBuilder->getQuery()->getResult();
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
                $shoppingCart->setState("shopping");
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }

        } else {
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['user_id' => $this->security->getUser()->getId()]);
        }
        return $shoppingCart;
    }

    public function getShoppingCartSum(): float
    {
        $shoppingCartProducts = $this->getShoppingCartProducts();
        $sum = 0;
        foreach ($shoppingCartProducts as $shoppingCartProduct) {
            $sum += $shoppingCartProduct['sum'];
        }
        return $sum;
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