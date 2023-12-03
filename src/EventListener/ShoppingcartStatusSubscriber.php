<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Product;
use App\Entity\ShoppingCart;
use App\Entity\ShoppingCartProduct;
use Twig\Environment as TwigEnvironment;


class ShoppingcartStatusSubscriber implements EventSubscriberInterface
{
    private $security;
    private $entityManager;
    private $twig;


    public function __construct(Security $security, EntityManagerInterface $entityManager, TwigEnvironment $twig)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 10]
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName === '_wdt') {
            return;
        }
        
        // get current user
        $user = $this->security->getUser();

        // used is not logged in
        if ($user === null) {
            $sessionId = $request->getSession()->getId();

            // get shopping cart by session id
            $shoppingCart = $this->entityManager->getRepository(ShoppingCart::class)->findOneBy(['sessionId' => $sessionId, 'state' => 'shopping']);

            // if shopping cart does not exist, create new one
            if ($shoppingCart === null) {
                $shoppingCart = new ShoppingCart();
                $shoppingCart->setSessionId($sessionId);
                $shoppingCart->setState('shopping');
                $this->entityManager->persist($shoppingCart);
                $this->entityManager->flush();
            }

            $queryBuilder = $this->entityManager->createQueryBuilder();

            $queryBuilder->select([
                'p.id AS id', 
                'scp.quantity AS quantity', 
                // 'p.id AS product_id', 
                'p.name AS name', 
                'p.price AS price', 
                'p.price AS product_price'
            ])
            ->from(ShoppingCartProduct::class, 'scp')
            ->join('scp.product', 'p');
        
            $products = $queryBuilder->getQuery()->getResult();

            $this->twig->addGlobal('shoppingcart', $products);

        } else {

        }  

        // var_dump($routeName);
    
        // Beispiellogik, um den Status basierend auf dem Routennamen zu ändern
        if ($routeName === 'shopping_route') {
            // Ändere den Status zu 'shopping'
        } elseif ($routeName === 'checkout_route') {
            // Ändere den Status zu 'checkout'
        } else {

        }
    }
}
