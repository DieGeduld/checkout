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
    private $shoppingCartProducts = null;
    private $shoppingCart = null;

    public function __construct(EntityManagerInterface $entityManager, Security $security, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getShoppingCartProducts(): array
    {
        // if ($this->shoppingCartProducts === null) {
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
            ->join('scp.product', 'p')
            ->where('scp.shoppingcart = :shoppingCartId')
            ->setParameter('shoppingCartId', $shoppingCart->getId());
            
            $shoppingCartProducts = $queryBuilder->getQuery()->getResult();

            $this->shoppingCartProducts = $shoppingCartProducts;
        // }
        return $this->shoppingCartProducts;;
    }


    public function getShoppingCart(): ?ShoppingCart
    { 
        // if ($this->shoppingCart === null) {
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
                if ($shoppingCart == null) {
                    $shoppingCart = new ShoppingCart();
                    $shoppingCart->setUserId($this->security->getUser());
                    $shoppingCart->setState("shopping");
                    $this->entityManager->persist($shoppingCart);
                    $this->entityManager->flush();
                }
            }
            $this->shoppingCart = $shoppingCart;
        // }
        return $this->shoppingCart;
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

    public function purgeShoppingCart(): void
    {
        $shoppingCartId = $this->getShoppingCart()->getId();
        $shoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingcart' => $shoppingCartId]);

        foreach ($shoppingCartProducts as $shoppingCartProduct) {
            $this->entityManager->remove($shoppingCartProduct);
            $this->entityManager->flush();
        }
    }

    public function mergeShoppingCarts(ShoppingCart $oldShoppingCart, ShoppingCart $newShoppingCart): void
    {
        $oldShoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingcart' => $oldShoppingCart->getId()]);
        $newShoppingCartProducts = $this->entityManager->getRepository(ShoppingCartProduct::class)->findBy(['shoppingcart' => $newShoppingCart->getId()]);

        // Durchlaufen aller Produkte im alten Warenkorb
        foreach ($oldShoppingCartProducts as $oldProduct) {

            $existsInNewCart = false;

            foreach ($newShoppingCartProducts as $newProduct) {
                if ($oldProduct->getProduct()->getId() === $newProduct->getProduct()->getId()) {
                    $newProduct->setQuantity( $newProduct->getQuantity() + $oldProduct->getQuantity() );
                    $this->entityManager->persist($newProduct);
                    $existsInNewCart = true;
                    break;
                }
            }

            // Hinzufügen des Produkts zum neuen Warenkorb, falls es noch nicht vorhanden ist
            if (!$existsInNewCart) {
                $newProduct = new ShoppingCartProduct();
                $newProduct->setShoppingcart($newShoppingCart);
                $newProduct->setProduct($oldProduct->getProduct());
                $newProduct->setQuantity($oldProduct->getQuantity());
                $this->entityManager->persist($newProduct);
            }
        }

        $this->entityManager->remove($oldShoppingCart);
        $this->entityManager->flush();
    }
    
}